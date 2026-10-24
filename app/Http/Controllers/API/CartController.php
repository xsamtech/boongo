<?php

namespace App\Http\Controllers\API;

use stdClass;
use App\Models\Cart;
use App\Models\Group;
use App\Models\Payment;
use App\Models\Status;
use App\Models\Subscription;
use App\Models\Type;
use App\Models\User;
use App\Models\Work;
use Illuminate\Http\Request;
use Dedoc\Scramble\Attributes\BodyParameter;
use App\Http\Resources\Cart as ResourcesCart;
use App\Http\Resources\User as ResourcesUser;
use Illuminate\Support\Facades\Http;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class CartController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        $carts = Cart::all();

        return $this->handleResponse(ResourcesCart::collection($carts), __('notifications.find_all_carts_success'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    #[BodyParameter('payment_code', description: 'Colonne carts.payment_code : TEXT; NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('entity', description: 'Colonne carts.entity : ENUM(\'favorite\', \'subscription\', \'consultation\'); NOT NULL. Défaut SQL : \'favorite\'.', required: false, type: 'string')]
    #[BodyParameter('status_id', description: 'Colonne carts.status_id : BIGINT; NULL autorisé.', required: false, type: 'integer')]
    #[BodyParameter('user_id', description: 'Colonne carts.user_id : BIGINT; NULL autorisé.', required: false, type: 'integer')]
    #[BodyParameter('payment_id', description: 'Colonne carts.payment_id : BIGINT; NULL autorisé.', required: false, type: 'integer')]
    public function store(Request $request)
    {
        // Get inputs
        $inputs = [
            'payment_code' => $request->payment_code,
            'entity' => isset($request->entity) ? $request->entity : 'favorite',
            'status_id' => $request->status_id,
            'user_id' => $request->user_id,
            'payment_id' => $request->payment_id
        ];
        // Select all carts to check unique constraint
        $carts = Cart::all();

        // Validate required fields
        if (trim($inputs['user_id']) == null) {
            return $this->handleError($inputs['user_id'], __('validation.required'), 400);
        }

        // Check if cart payment code already exists
        foreach ($carts as $another_book):
            if ($another_book->payment_code == $inputs['payment_code']) {
                return $this->handleError($inputs['payment_code'], __('validation.custom.code.exists'), 400);
            }
        endforeach;

        $cart = Cart::create($inputs);

        return $this->handleResponse(new ResourcesCart($cart), __('notifications.create_cart_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     */
    public function show($id)
    {
        $cart = Cart::find($id);

        if (is_null($cart)) {
            return $this->handleError(__('notifications.find_cart_404'));
        }

        return $this->handleResponse(new ResourcesCart($cart), __('notifications.find_cart_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Cart  $cart
     */
    #[BodyParameter('id', description: 'Colonne carts.id : BIGINT; NOT NULL. Identifiant du corps utilisé par les contrôles existants, distinct du paramètre de route.', required: false, type: 'integer')]
    #[BodyParameter('payment_code', description: 'Colonne carts.payment_code : TEXT; NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('entity', description: 'Colonne carts.entity : ENUM(\'favorite\', \'subscription\', \'consultation\'); NOT NULL. Défaut SQL : \'favorite\'.', required: false, type: 'string')]
    #[BodyParameter('status_id', description: 'Colonne carts.status_id : BIGINT; NULL autorisé.', required: false, type: 'integer')]
    #[BodyParameter('user_id', description: 'Colonne carts.user_id : BIGINT; NULL autorisé.', required: false, type: 'integer')]
    #[BodyParameter('payment_id', description: 'Colonne carts.payment_id : BIGINT; NULL autorisé.', required: false, type: 'integer')]
    public function update(Request $request, Cart $cart)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'payment_code' => $request->payment_code,
            'entity' => $request->entity,
            'status_id' => $request->status_id,
            'user_id' => $request->user_id,
            'payment_id' => $request->payment_id
        ];

        if ($inputs['payment_code'] != null) {
            // Select all carts to check unique constraint
            $carts = Cart::all();
            $current_cart = Cart::find($inputs['id']);

            foreach ($carts as $another_cart):
                if (($current_cart->payment_code != $inputs['payment_code']) && ($another_cart->payment_code == $inputs['payment_code'])) {
                    return $this->handleError($inputs['payment_code'], __('validation.custom.code.exists'), 400);
                }
            endforeach;

            $cart->update([
                'payment_code' => $inputs['payment_code'],
                'updated_at' => now(),
            ]);
        }

        foreach (['entity', 'status_id', 'user_id', 'payment_id'] as $field) {
            if ($inputs[$field] != null) {
                $cart->update([
                    $field => $inputs[$field],
                    'updated_at' => now()
                ]);
            }
        }

        return $this->handleResponse(new ResourcesCart($cart), __('notifications.update_cart_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Cart  $cart
     */
    public function destroy(Cart $cart)
    {
        $cart->delete();

        $carts = Cart::all();

        return $this->handleResponse(ResourcesCart::collection($carts), __('notifications.delete_cart_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Add work to cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $entity
     */
    #[BodyParameter('user_id', description: 'Colonne carts.user_id : BIGINT; NULL autorisé.', required: false, type: 'integer')]
    #[BodyParameter('work_id', description: 'Colonne work_id : BIGINT; NOT NULL.', required: false, type: 'integer')]
    #[BodyParameter('subscription_id', description: 'Colonne subscription_id : BIGINT; NOT NULL.', required: false, type: 'integer')]
    public function addToCart(Request $request, $entity)
    {
        $cart_status_group = Group::where('group_name', 'Etat du panier')->first();
        $status = Status::where([['status_name->fr', 'En cours'], ['group_id', $cart_status_group->id]])->first();

        if (is_null($status)) {
            return $this->handleError(__('notifications.find_status_404'));
        }

        $user = User::find($request->user_id);

        if (is_null($user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        if (is_null($entity) || !in_array($entity, ['favorite', 'subscription', 'consultation'])) {
            return $this->handleError(__('validation.custom.owner.required'));
        }

        if ($entity == 'favorite' || $entity == 'consultation') {
            $work = Work::find($request->work_id);

            if (is_null($work)) {
                return $this->handleError(__('notifications.find_work_404'));
            }

            $cart = $entity == 'favorite' ?
                        Cart::where([['entity', $entity], ['user_id', $user->id]])->first() :
                        Cart::where([['entity', $entity], ['status_id', $status->id], ['user_id', $user->id]])->first();

            if ($cart != null) {
                if (count($cart->works) > 0) {
                    if (!$cart->works->contains($work->id)) {
                        $cart->works()->syncWithoutDetaching([$work->id]);
                    }

                } else {
                    $cart->works()->attach([$work->id]);
                }

            } else {
                $cart = Cart::create([
                    'entity' => $entity,
                    'user_id' => $user->id,
                    'status_id' => ($entity == 'favorite' ? null : $status->id)
                ]);

                $cart->works()->attach([$work->id]);
            }

            return $this->handleResponse(new ResourcesUser($user), __('notifications.add_work_success'));

        } else {
            $subscription = Subscription::find($request->subscription_id);

            if (is_null($subscription)) {
                return $this->handleError(__('notifications.find_subscription_404'));
            }

            $cart = Cart::where([['entity', $entity], ['status_id', $status->id], ['user_id', $user->id]])->first();

            if ($cart != null) {
                if (count($cart->subscriptions) > 0) {
                    if (!$cart->subscriptions->contains($subscription->id)) {
                        $cart->subscriptions()->syncWithoutDetaching([$subscription->id]);
                    }

                } else {
                    $cart->subscriptions()->attach([$subscription->id]);
                }

            } else {
                $cart = Cart::create([
                    'entity' => $entity,
                    'user_id' => $user->id,
                    'status_id' => $status->id
                ]);

                $cart->subscriptions()->attach([$subscription->id]);
            }

            return $this->handleResponse(new ResourcesUser($user), __('notifications.add_subscription_success'));
        }
    }

    /**
     * Remove work from cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $cart_id
     */
    #[BodyParameter('work_id', description: 'Colonne work_id : BIGINT; NOT NULL.', required: false, type: 'integer')]
    #[BodyParameter('subscription_id', description: 'Colonne subscription_id : BIGINT; NOT NULL.', required: false, type: 'integer')]
    public function removeFromCart(Request $request, int $cart_id)
    {
        $cart = Cart::find($cart_id);

        if (is_null($cart)) {
            return $this->handleError(__('notifications.find_cart_404'));
        }

        $user = User::find($cart->user_id);

        if (isset($request->work_id)) {
            $work = Work::find($request->work_id);

            if (is_null($work)) {
                return $this->handleError(__('notifications.find_work_404'));
            }

            $cart->works()->detach($work->id);

            return $this->handleResponse(new ResourcesUser($user), __('notifications.delete_work_success'));
        }

        if (isset($request->subscription_id)) {
            $subscription = Subscription::find($request->subscription_id);

            if (is_null($subscription)) {
                return $this->handleError(__('notifications.find_subscription_404'));
            }

            $cart->subscriptions()->detach($subscription->id);

            return $this->handleResponse(new ResourcesUser($user), __('notifications.delete_subscription_success'));
        }
    }

    /**
     * Purchase ordered product/service.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $user_id
     */
    #[BodyParameter('transaction_type_id', description: 'Identifiant utilisé par cette opération.', required: false, type: 'integer')]
    #[BodyParameter('other_phone', description: 'Champ other_phone de la requête.', required: false, type: 'string')]
    #[BodyParameter('channel', description: 'Colonne channel : VARCHAR(45); NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('app_url', description: 'Champ app_url de la requête.', required: false, type: 'string')]
    public function purchase(Request $request, int $user_id)
    {
        // FlexPay accessing data
        $gateway_mobile = config('services.flexpay.gateway_mobile');
        $gateway_card = config('services.flexpay.gateway_card_v2');
        // Groups
        $cart_status_group = Group::where('group_name', 'Etat du panier')->first();
        $payment_status_group = Group::where('group_name', 'Etat du paiement')->first();
        $payment_type_group = Group::where('group_name', 'Type de paiement')->first();
        $subscription_status_group = Group::where('group_name', 'Etat de l\'abonnement')->first();
        // Status
        $ongoing_status = Status::where([['status_name->fr', 'En cours'], ['group_id', $cart_status_group->id]])->first();
        $paid_status = Status::where([['status_name->fr', 'Payé'], ['group_id', $cart_status_group->id]])->first();
        $in_progress_status = Status::where([['status_name->fr', 'En cours'], ['group_id', $payment_status_group->id]])->first();
        $pending_status = Status::where([['status_name->fr', 'En attente'], ['group_id', $subscription_status_group->id]])->first();
        // Types
        $mobile_money_type = Type::where([['type_name->fr', 'Mobile money'], ['group_id', $payment_type_group->id]])->first();
        $bank_card_type = Type::where([['type_name->fr', 'Carte bancaire'], ['group_id', $payment_type_group->id]])->first();

        $object = new stdClass();
        $cart_consultation = null;
        $cart_subscription = null;

        if (is_null($mobile_money_type)) {
            return $this->handleError(__('miscellaneous.public.about.subscribe.send_money.mobile_money'), __('notifications.find_type_404'), 404);
        }

        if (is_null($bank_card_type)) {
            return $this->handleError(__('miscellaneous.public.about.subscribe.send_money.bank_card'), __('notifications.find_type_404'), 404);
        }

        $current_user = User::find($user_id);

        if (is_null($current_user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        $user_currency = $current_user->currency->currency_acronym;

        $cart_totals = $current_user->totalsUnpaid();
        $total_to_pay = $cart_totals['total_unpaid_consultations'] + $cart_totals['total_unpaid_subscriptions'];

        if ($request->transaction_type_id == null || !is_numeric($request->transaction_type_id)) {
            return $this->handleError($request->transaction_type_id, __('validation.required'), 400);
        }

        /*
         * Fonctions communes
         */
        $createPayment = function ($orderNumber, $reference) use ($current_user, $request, $total_to_pay, $user_currency, $in_progress_status) {
            return Payment::firstOrCreate(
                ['order_number' => $orderNumber],
                [
                    'reference' => $reference,
                    'amount' => round($total_to_pay),
                    'phone' => str_replace('+', '', $request->other_phone),
                    'currency' => $user_currency,
                    'channel' => $request->channel,
                    'type_id' => $request->transaction_type_id,
                    'status_id' => $in_progress_status->id,
                    'user_id' => $current_user->id,
                ]
            );
        };

        $updateCart = function ($cart, $relation, $payment, $paymentCode) use ($paid_status, $pending_status) {
            if (!$cart) {
                return;
            }

            $cart->update([
                'payment_code' => $paymentCode,
                'status_id' => $paid_status->id,
                'payment_id' => $payment->id,
                'updated_at' => now(),
            ]);

            foreach ($cart->{$relation} as $item) {
                $cart->{$relation}()->updateExistingPivot($item->id, ['status_id' => $pending_status->id]);
            }
        };

        $prepareUser = function () use ($current_user) {
            if ($current_user->promo_code != null) {
                $current_user->update(['is_promoted' => 0]);
            }
        };

        $generatePaymentCode = fn() => 'BNG-' . random_int(1000000, 9999999) . '-' . date('Y.m.d');

        /*
         * Mobile Money
         */
        if ($request->transaction_type_id == $mobile_money_type->id) {
            $reference_code = 'REF-' . random_int(10000000, 99999999) . '-' . $current_user->id;
            $response = Http::timeout(300)
                            ->withToken(config('services.flexpay.api_token'))
                            ->post($gateway_mobile, [
                                'merchant' => config('services.flexpay.merchant'),
                                'type' => '1',
                                'phone' => str_replace('+', '', $request->other_phone),
                                'reference' => $reference_code,
                                'amount' => round($total_to_pay),
                                'currency' => $user_currency,
                                'callback_url' => getApiURL() . '/payment/store',
                            ]);

            $jsonRes = $response->json();

            if ($jsonRes['code'] != '0') {
                return $this->handleError(__('miscellaneous.error_label'), __('notifications.transaction_push_failed'), 400);
            }

            $payment = $createPayment($jsonRes['orderNumber'], $reference_code);

            $prepareUser();

            $paymentCode = $generatePaymentCode();

            if ($current_user->unpaidConsultations()->isNotEmpty()) {
                $cart_consultation = Cart::where([
                    ['entity', 'consultation'],
                    ['status_id', $ongoing_status->id],
                    ['user_id', $current_user->id],
                ])->latest()->first();

                $updateCart($cart_consultation, 'works', $payment, $paymentCode);
            }

            if ($current_user->unpaidSubscriptions()->isNotEmpty()) {
                $cart_subscription = Cart::where([
                    ['entity', 'subscription'],
                    ['status_id', $ongoing_status->id],
                    ['user_id', $current_user->id],
                ])->latest()->first();

                $updateCart($cart_subscription, 'subscriptions', $payment, $paymentCode);
            }

            $object->user = new ResourcesUser($current_user);
            $object->result_response = [
                'message' => $jsonRes['message'],
                'order_number' => $jsonRes['orderNumber'],
            ];
        }

        /*
         * Carte bancaire
         */
        if ($request->transaction_type_id == $bank_card_type->id) {
            $cart_consultation = $current_user->unpaidConsultations()->isNotEmpty()
                ? Cart::where([['entity', 'consultation'], ['status_id', $ongoing_status->id], ['user_id', $current_user->id],])->latest()->first()
                : null;

            $cart_subscription = $current_user->unpaidSubscriptions()->isNotEmpty()
                ? Cart::where([['entity', 'subscription'], ['status_id', $ongoing_status->id], ['user_id', $current_user->id],])->latest()->first()
                : null;

            $reference_code = 'REF-' . random_int(10000000, 99999999) . '-' . $current_user->id;

            $response = Http::timeout(300)
                            ->withToken(config('services.flexpay.api_token'))
                            ->post($gateway_mobile, [
                                'merchant' => config('services.flexpay.merchant'),
                                'type' => '2',
                                'reference' => $reference_code,
                                'amount' => round($total_to_pay),
                                'currency' => $user_currency,
                                'description' => __('miscellaneous.bank_transaction_description'),
                                'callback_url' => getApiURL() . '/payment/store',
                                'approve_url' => $request->app_url . '/subscribed/' . round($total_to_pay) . '/USD/0/' . $current_user->_id . '?app_id=',
                                'cancel_url' => $request->app_url . '/subscribed/' . round($total_to_pay) . '/USD/1/' . $current_user->id . '?app_id=',
                                'decline_url' => $request->app_url . '/subscribed/' . round($total_to_pay) . '/USD/2/' . $current_user->id . '?app_id=',
                                'home_url' => $request->app_url . '/subscribe?app_id=' . '&cart_consultation_id=' . ($cart_consultation?->id ?? 0) . '&cart_subscription_id=' . ($cart_subscription?->id ?? 0) . '&user_id=' . $current_user->id . '&api_token=' . $current_user->api_token,]);

            $jsonRes = $response->json();

            $code = $jsonRes['code'];
            $message = $jsonRes['message'];

            if (!empty($jsonRes['error'])) {
                return $this->handleError($jsonRes['error'], $message, $jsonRes['status']);
            }

            if ($code != '0') {
                return $this->handleError($code, $message, 400);
            }

            $payment = $createPayment($jsonRes['orderNumber'], $reference_code);

            $prepareUser();

            $paymentCode = $generatePaymentCode();

            $updateCart($cart_consultation, 'works', $payment, $paymentCode);
            $updateCart($cart_subscription, 'subscriptions', $payment, $paymentCode);

            $object->user = new ResourcesUser($current_user);
            $object->result_response = [
                'message' => $message,
                'order_number' => $jsonRes['orderNumber'],
                'url' => $jsonRes['url'],
            ];
        }

        $object->cart_consultation = $cart_consultation ? new ResourcesCart($cart_consultation) : null;
        $object->cart_subscription = $cart_subscription ? new ResourcesCart($cart_subscription) : null;

        return $this->handleResponse($object, __('notifications.ready_for_payment'));
    }
}
