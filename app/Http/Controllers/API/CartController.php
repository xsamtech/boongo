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
use App\Http\Resources\Cart as ResourcesCart;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class CartController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
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
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Get inputs
        $inputs = [
            'payment_code' => $request->payment_code,
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
     * @return \Illuminate\Http\Response
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
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Cart $cart)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'payment_code' => $request->payment_code,
            'status_id' => $request->status_id,
            'user_id' => $request->user_id,
            'payment_id' => $request->payment_id
        ];
        if ($inputs['payment_code'] != null) {
            // Select all carts to check unique constraint
            $carts = Cart::all();
            $current_cart = Cart::find($inputs['id']);

            foreach ($carts as $another_cart):
                if ($current_cart->payment_code != $inputs['payment_code']) {
                    if ($another_cart->payment_code == $inputs['payment_code']) {
                        return $this->handleError($inputs['payment_code'], __('validation.custom.code.exists'), 400);
                    }
                }
            endforeach;

            $cart->update([
                'payment_code' => $inputs['payment_code'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['status_id'] != null) {
            $cart->update([
                'status_id' => $inputs['status_id'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['user_id'] != null) {
            $cart->update([
                'user_id' => $inputs['user_id'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['payment_id'] != null) {
            $cart->update([
                'payment_id' => $inputs['payment_id'],
                'updated_at' => now(),
            ]);
        }

        return $this->handleResponse(new ResourcesCart($cart), __('notifications.update_cart_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Cart  $cart
     * @return \Illuminate\Http\Response
     */
    public function destroy(Cart $cart)
    {
        $cart->delete();

        $carts = Cart::all();

        return $this->handleResponse(ResourcesCart::collection($carts), __('notifications.delete_cart_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Check if work is in cart
     *
     * @param  int $work_id
     * @param  int $user_id
     * @return \Illuminate\Http\Response
     */
    public function isInside($work_id, $user_id)
    {
        $work = Work::find($work_id);

        if (is_null($work)) {
            return $this->handleError(__('notifications.work_404'));
        }

        $user = User::find($user_id);

        if (is_null($user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        $status = Status::where('status_name->fr', 'En cours')->first();

        if (is_null($status)) {
            return $this->handleError(__('notifications.find_status_404'));
        }

        $hasPivot = Cart::where([['status_id', $status->id], ['user_id', $user->id]])->whereHas('works', function ($q) use ($work) {
                        $q->where('works.id', $work->id);
                    })->exists();

        if ($hasPivot) {
            return $this->handleResponse(1, __('notifications.find_work_success'), null);

        } else {
            return $this->handleResponse(0, __('notifications.find_work_404'), null);
        }
    }

    /**
     * Add work to cart.
     *
     * @param  int $work_id
     * @param  int $user_id
     * @return \Illuminate\Http\Response
     */
    public function addToCart($work_id, $user_id)
    {
        $work = Work::find($work_id);

        if (is_null($work)) {
            return $this->handleError(__('notifications.find_work_404'));
        }

        $user = User::find($user_id);

        if (is_null($user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        $status = Status::where('status_name->fr', 'En cours')->first();

        if (is_null($status)) {
            return $this->handleError(__('notifications.find_status_404'));
        }

        $cart = Cart::where([['status_id', $status->id], ['user_id', $user->id]])->first();

        if ($cart != null) {

            if (count($cart->works) > 0) {
                $cart->works()->syncWithoutDetaching([$work->id]);

            } else {
                $cart->works()->attach([$work->id]);
            }

            return $this->handleResponse(new ResourcesCart($cart), __('notifications.find_cart_success'));

        } else {
            $cart = Cart::create([
                'status_id' => $status->id,
                'user_id' => $user->id
            ]);

            $cart->works()->attach([$work->id]);

            return $this->handleResponse(new ResourcesCart($cart), __('notifications.find_cart_success'));
        }
    }

    /**
     * Remove work from cart.
     *
     * @param  int $work_id
     * @param  int $cart_id
     * @return \Illuminate\Http\Response
     */
    public function removeFromCart($work_id, $cart_id)
    {
        $work = Work::find($work_id);

        if (is_null($work)) {
            return $this->handleError(__('notifications.find_work_404'));
        }

        $cart = Cart::find($cart_id);

        if (is_null($cart)) {
            return $this->handleError(__('notifications.find_cart_404'));
        }

        $cart->works()->detach($work->id);

        return $this->handleResponse(new ResourcesCart($cart), __('notifications.delete_media_success'));
    }

    /**
     * Purchase ordered product/service.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $user_id
     * @return \Illuminate\Http\Response
     */
    public function purchase(Request $request, $user_id)
    {
        // FlexPay accessing data
        $gateway_mobile = config('services.flexpay.gateway_mobile');
        $gateway_card = config('services.flexpay.gateway_card_v2');
        // Vonage accessing data
        // $basic  = new \Vonage\Client\Credentials\Basic(config('vonage.api_key'), config('vonage.api_secret'));
        // $client = new \Vonage\Client($basic);
        // Groups
        $cart_status_group = Group::where('group_name', 'Etat du panier')->first();
        $subscription_status_group = Group::where('group_name', 'Etat de l\'abonnement')->first();
        $payment_type_group = Group::where('group_name', 'Type de paiement')->first();
        // Status
        $ongoing_status = Status::where([['status_name->fr', 'En cours'], ['group_id', $cart_status_group->id]])->first();
        $paid_status = Status::where([['status_name->fr', 'Payé'], ['group_id', $cart_status_group->id]])->first();
        $valid_status = Status::where([['status_name->fr', 'Valide'], ['group_id', $subscription_status_group->id]])->first();
        // Types
        $mobile_money_type = Type::where([['type_name->fr', 'Mobile money'], ['group_id', $payment_type_group->id]])->first();
        $bank_card_type = Type::where([['type_name->fr', 'Carte bancaire'], ['group_id', $payment_type_group->id]])->first();

        if (is_null($mobile_money_type)) {
            return $this->handleError(__('miscellaneous.public.home.posts.boost.transaction_type.mobile_money'), __('notifications.find_type_404'), 404);
        }

        if (is_null($bank_card_type)) {
            return $this->handleError(__('miscellaneous.public.home.posts.boost.transaction_type.bank_card'), __('notifications.find_type_404'), 404);
        }

        // Requests
        $current_user = User::find($user_id);

        if (is_null($current_user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        $cart = Cart::where([['status_id', $ongoing_status->id], ['user_id', $current_user->id]])->first();

        if (is_null($cart)) {
            $cart = Cart::create([
                'status_id' => $ongoing_status->id, 
                'user_id' => $current_user->id
            ]);
        }

        $subscription = Subscription::find($request->subscription_id);

        if (is_null($subscription)) {
            return $this->handleError(__('notifications.find_subscription_404'));
        }

        // Validations
        if ($request->transaction_type_id == null OR !is_numeric($request->transaction_type_id)) {
            return $this->handleError(__('miscellaneous.found_value') . ' ' . $request->transaction_type_id, __('validation.required', ['field_name' => __('miscellaneous.public.home.posts.boost.transaction_type.title')]), 400);
        }

        // If the transaction is via mobile money
        if ($request->transaction_type_id == $mobile_money_type->id) {
            $reference_code = 'REF-' . ((string) random_int(10000000, 99999999)) . '-' . $current_user->id;

            return $this->handleError($reference_code);
            die();
            // Create response by sending request to FlexPay
            $data = array(
                // 'merchant' => config('services.flexpay.merchant'),
                'merchant' => 'DIKITIVI',
                'type' => $request->transaction_type_id,
                'phone' => $request->other_phone,
                'reference' => $reference_code,
                'amount' => ((int) $subscription->price),
                'currency' => 'USD',
                'callbackUrl' => getApiURL() . '/payment/store'
            );
            $data = json_encode($data);
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $gateway_mobile);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, Array(
                'Content-Type: application/json',
                'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJcL2xvZ2luIiwicm9sZXMiOlsiTUVSQ0hBTlQiXSwiZXhwIjoxNzc3MjEyNjA5LCJzdWIiOiJmNmJjMWUzYTkxYTQzNTQzMjNmODc0YWY1NGZmNzUyMyJ9.n2VVIuubjSo1f5ZFB7UfR8K-ckT1cMPTN1saiY3NhLA'
                // 'Authorization: Bearer ' . config('services.flexpay.api_token')
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                return $this->handleError(curl_errno($ch), __('notifications.transaction_request_failed'), 400);

            } else {
                curl_close($ch); 

                $jsonRes = json_decode($response);
                $code = $jsonRes->code; // Push sending status

                if ($code != '0') {
                    return $this->handleError(__('miscellaneous.error_label'), __('notifications.transaction_push_failed'), 400);

                } else {
                    // Register payment, even if FlexPay will
                    $payment = Payment::where('order_number', $jsonRes->orderNumber)->first();

                    if (is_null($payment)) {
                        Payment::create([
                            'reference' => $reference_code,
                            'order_number' => $jsonRes->orderNumber,
                            'amount' => $subscription->price,
                            'phone' => $request->other_phone,
                            'currency' => 'USD',
                            'type_id' => $request->transaction_type_id,
                            'status_id' => $code,
                            'user_id' => $current_user->id
                        ]);
                    }

                    // The subscription is created only if the processing succeed
                    $current_user->subscriptions()->syncWithPivotValues([$subscription->id], ['payment_id' => $payment->id, 'status_id' => $valid_status->id]);

                    // The cart is updated only if the processing succeed
                    $random_string = (string) random_int(1000000, 9999999);
                    $generated_number = 'BNG-' . $random_string . '-' . date('Y.m.d');

                    $cart->update([
                        'payment_code' => $generated_number,
                        'status_id' => $paid_status->id,
                        'user_id' => $current_user->id,
                        'payment_id' => $payment->id,
                        'updated_at' => now()
                    ]);

                    $object = new stdClass();

                    $object->result_response = [
                        'message' => $jsonRes->message,
                        'order_number' => $jsonRes->orderNumber
                    ];
                    $object->cart = new ResourcesCart($cart);

                    return $this->handleResponse($object, __('notifications.create_subscription_success'));
                }
            }
        }

        // If the transaction is via bank card
        if ($request->transaction_type_id == $bank_card_type->id) {
            $reference_code = 'REF-' . ((string) random_int(10000000, 99999999)) . '-' . $current_user->id;

            // Create response by sending request to FlexPay
            $body = json_encode(array(
                'authorization' => 'Bearer ' . config('services.flexpay.api_token'),
                'merchant' => config('services.flexpay.merchant'),
                'reference' => $reference_code,
                'amount' => $subscription->price,
                'currency' => 'USD',
                'description' => __('miscellaneous.bank_transaction_description'),
                'callback_url' => getApiURL() . '/payment/store',
                'approve_url' => $request->app_url . '/subscribed/' . $subscription->price . '/USD/0/' . $current_user->id,
                'cancel_url' => $request->app_url . '/subscribed/' . $subscription->price . '/USD/1/' . $current_user->id,
                'decline_url' => $request->app_url . '/subscribed/' . $subscription->price . '/USD/2/' . $current_user->id,
                'home_url' => $request->app_url . '/subscribed',
            ));

            $curl = curl_init($gateway_card);

            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $curlResponse = curl_exec($curl);

            $jsonRes = json_decode($curlResponse, true);
            $code = $jsonRes['code'];
            $message = $jsonRes['message'];

            if (!empty($jsonRes['error'])) {
                return $this->handleError($jsonRes['error'], $message, $jsonRes['status']);

            } else {
                if ($code != '0') {
                    return $this->handleError($code, $message, 400);

                } else {
                    $url = $jsonRes['url'];
                    $orderNumber = $jsonRes['orderNumber'];
                    // Register payment, even if FlexPay will
                    $payment = Payment::where('order_number', $orderNumber)->first();

                    if (is_null($payment)) {
                        Payment::create([
                            'reference' => $reference_code,
                            'order_number' => $orderNumber,
                            'amount' => $subscription->price,
                            'phone' => $request->other_phone,
                            'currency' => 'USD',
                            'type_id' => $request->transaction_type_id,
                            'status_id' => $code,
                            'user_id' => $current_user->id
                        ]);
                    }

                    // The subscription is created only if the processing succeed
                    $current_user->subscriptions()->syncWithPivotValues([$subscription->id], ['payment_id' => $payment->id, 'status_id' => $valid_status->id]);

                    // The cart is updated only if the processing succeed
                    $random_string = (string) random_int(1000000, 9999999);
                    $generated_number = 'BNG-' . $random_string . '-' . date('Y.m.d');

                    $cart->update([
                        'payment_code' => $generated_number,
                        'status_id' => $paid_status->id,
                        'user_id' => $current_user->id,
                        'payment_id' => $payment->id,
                        'updated_at' => now()
                    ]);

                    $object = new stdClass();

                    $object->result_response = [
                        'message' => $message,
                        'order_number' => $orderNumber,
                        'url' => $url
                    ];
                    $object->cart = new ResourcesCart($cart);

                    return $this->handleResponse($object, __('notifications.create_subscription_success'));
                }
            }
        }
    }
}
