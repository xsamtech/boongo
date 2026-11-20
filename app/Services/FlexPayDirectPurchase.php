<?php

namespace App\Services;

use App\Exceptions\DirectPurchaseException;
use App\Models\Cart;
use App\Models\Group;
use App\Models\Payment;
use App\Models\Status;
use App\Models\Subscription;
use App\Models\Type;
use App\Models\User;
use App\Models\Work;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class FlexPayDirectPurchase
{
    public function __construct(private UsdPriceConverter $converter) {}

    /**
     * @return array{payment_id: int, cart_id: int, entity: string, entity_id: int, amount: string, currency: 'USD', reference: string, result_response: array{message: string, order_number: string, url: ?string}}
     */
    public function purchase(User $user, Work|Subscription $item, array $inputs): array
    {
        $entity = $item instanceof Work ? 'consultation' : 'subscription';
        $amount = $this->converter->convert($item instanceof Work ? $item->consultation_price : $item->price, $item->currency);
        $type = Type::with('group')->find($inputs['transaction_type_id']);
        if ($type?->group?->group_name !== 'Type de paiement' || ! in_array($type->getTranslation('type_name', 'fr'), ['Mobile money', 'Carte bancaire'], true)) {
            throw ValidationException::withMessages(['transaction_type_id' => __('notifications.find_type_404')]);
        }
        $mobile = $type->getTranslation('type_name', 'fr') === 'Mobile money';
        if ($mobile && empty($inputs['other_phone'])) {
            throw ValidationException::withMessages(['other_phone' => 'Le numéro de téléphone est obligatoire pour Mobile money.']);
        }
        if (! $mobile && empty($inputs['app_url'])) {
            throw ValidationException::withMessages(['app_url' => 'L’URL de retour est obligatoire pour la carte bancaire.']);
        }

        // Resolve local configuration before any request to the payment provider.
        $inProgress = $this->status('Etat du paiement', 'En cours');
        $paid = $this->status('Etat du panier', 'Payé');
        $pending = $this->status('Etat de l\'abonnement', 'En attente');
        $gateway = config('services.flexpay.gateway_mobile'); // Existing Cart::purchase uses type 1/2 on this gateway.
        if (! $gateway || ! config('services.flexpay.api_token') || ! config('services.flexpay.merchant')) {
            throw new DirectPurchaseException('La configuration FlexPay est incomplète.', 503);
        }

        // Keep the user ID in the third segment for PaymentController::store callbacks.
        $reference = 'REF-'.bin2hex(random_bytes(8)).'-'.$user->id;
        $payload = [
            'merchant' => config('services.flexpay.merchant'),
            'type' => $mobile ? '1' : '2',
            'reference' => $reference,
            'amount' => $amount,
            'currency' => 'USD',
            'callback_url' => route('payment.api.store'),
        ];
        if ($mobile) {
            $payload['phone'] = ltrim($inputs['other_phone'], '+');
        } else {
            $base = rtrim($inputs['app_url'], '/');
            $payload += [
                'description' => __('miscellaneous.bank_transaction_description'),
                'approve_url' => $base.'/subscribed/'.$amount.'/USD/0/'.$user->id.'?app_id=',
                'cancel_url' => $base.'/subscribed/'.$amount.'/USD/1/'.$user->id.'?app_id=',
                'decline_url' => $base.'/subscribed/'.$amount.'/USD/2/'.$user->id.'?app_id=',
                'home_url' => $base.'/subscribe',
            ];
        }

        try {
            $response = Http::timeout(300)->withToken(config('services.flexpay.api_token'))->post($gateway, $payload);
        } catch (ConnectionException $exception) {
            throw new DirectPurchaseException('FlexPay est injoignable. Vérifiez le résultat de la transaction avant de réessayer.', 502);
        }
        $result = $response->json();
        if (! $response->successful() || ! is_array($result) || ! isset($result['code']) || (string) $result['code'] !== '0' || ! empty($result['error'])) {
            throw new DirectPurchaseException(__('notifications.transaction_push_failed'), 502);
        }
        if (empty($result['orderNumber']) || (! is_string($result['orderNumber']) && ! is_int($result['orderNumber'])) || (! $mobile && empty($result['url'])) || (isset($result['url']) && ! is_string($result['url']))) {
            throw new DirectPurchaseException('La réponse FlexPay ne contient pas les informations du paiement.', 502);
        }

        [$payment, $cart] = DB::transaction(function () use ($result, $reference, $amount, $inputs, $type, $user, $inProgress, $paid, $pending, $entity, $item) {
            // A fast callback may already have recorded the completed payment: never reset it.
            $payment = Payment::firstOrCreate(['order_number' => (string) $result['orderNumber']], [
                'reference' => $reference, 'amount' => $amount, 'currency' => 'USD',
                'phone' => isset($inputs['other_phone']) ? ltrim($inputs['other_phone'], '+') : null,
                'channel' => $inputs['channel'] ?? null, 'type_id' => $type->id,
                'status_id' => $inProgress->id, 'user_id' => $user->id,
            ]);
            if ($payment->reference !== $reference || (string) $payment->user_id !== (string) $user->id) {
                throw new DirectPurchaseException('Le numéro de commande FlexPay appartient à une autre demande de paiement.', 502);
            }
            $cart = Cart::create([
                'payment_code' => 'BNG-'.bin2hex(random_bytes(8)).'-'.date('Y.m.d'),
                'entity' => $entity, 'status_id' => $paid->id, 'payment_id' => $payment->id, 'user_id' => $user->id,
            ]);
            $relation = $entity === 'consultation' ? 'works' : 'subscriptions';
            $cart->{$relation}()->attach($item->id, ['status_id' => $pending->id]);
            if ($user->promo_code !== null) {
                $user->update(['is_promoted' => 0]);
            }

            return [$payment, $cart];
        });

        return [
            'payment_id' => $payment->id, 'cart_id' => $cart->id,
            'entity' => $entity, 'entity_id' => $item->id, 'amount' => $amount, 'currency' => 'USD', 'reference' => $reference,
            'result_response' => [
                'message' => is_string($result['message'] ?? null) ? $result['message'] : __('notifications.ready_for_payment'),
                'order_number' => (string) $result['orderNumber'], 'url' => $result['url'] ?? null,
            ],
        ];
    }

    private function status(string $groupName, string $name): Status
    {
        $groupId = Group::where('group_name', $groupName)->value('id');
        $status = $groupId === null ? null : Status::where('group_id', $groupId)->where('status_name->fr', $name)->first();
        if ($status === null) {
            throw new DirectPurchaseException('Le statut de paiement requis est absent : '.$groupName.' / '.$name.'.', 503);
        }

        return $status;
    }
}
