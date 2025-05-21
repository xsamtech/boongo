<?php

namespace App\Http\Controllers\API;

use App\Models\Cart;
use App\Models\Currency;
use App\Models\Group;
use App\Models\Payment;
use App\Models\Status;
use App\Models\Subscription;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Cart as ResourcesCart;
use App\Http\Resources\Subscription as ResourcesSubscription;
use App\Http\Resources\User as ResourcesUser;
use Carbon\Carbon;
use stdClass;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class SubscriptionController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $subscriptions = Subscription::all();

        return $this->handleResponse(ResourcesSubscription::collection($subscriptions), __('notifications.find_all_subscriptions_success'));
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
            'number_of_hours' => $request->number_of_hours,
            'price' => $request->price,
            'currency_id' => $request->currency_id,
            'type_id' => $request->type_id,
            'category_id' => $request->category_id
        ];

        $validator = Validator::make($inputs, [
            'number_of_hours' => ['required'],
            'price' => ['required'],
            'currency_id' => ['required'],
            'type_id' => ['required'],
            'category_id' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());       
        }

        $subscription = Subscription::create($inputs);

        return $this->handleResponse(new ResourcesSubscription($subscription), __('notifications.create_subscription_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $subscription = Subscription::find($id);

        if (is_null($subscription)) {
            return $this->handleError(__('notifications.find_subscription_404'));
        }

        return $this->handleResponse(new ResourcesSubscription($subscription), __('notifications.find_subscription_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Subscription  $subscription
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Subscription $subscription)
    {
        // Get inputs
        $inputs = [
            'number_of_hours' => $request->number_of_hours,
            'price' => $request->price,
            'currency_id' => $request->currency_id,
            'type_id' => $request->type_id,
            'category_id' => $request->category_id
        ];

        if ($inputs['number_of_hours'] != null) {
            $subscription->update([
                'number_of_hours' => $inputs['number_of_hours'],
                'updated_at' => now()
            ]);
        }

        if ($inputs['price'] != null) {
            $subscription->update([
                'price' => $inputs['price'],
                'updated_at' => now()
            ]);
        }

        if ($inputs['currency_id'] != null) {
            $subscription->update([
                'currency_id' => $inputs['currency_id'],
                'updated_at' => now()
            ]);
        }

        if ($inputs['type_id'] != null) {
            $subscription->update([
                'type_id' => $inputs['type_id'],
                'updated_at' => now()
            ]);
        }

        if ($inputs['category_id'] != null) {
            $subscription->update([
                'category_id' => $inputs['category_id'],
                'updated_at' => now()
            ]);
        }

        return $this->handleResponse(new ResourcesSubscription($subscription), __('notifications.update_subscription_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Subscription  $subscription
     * @return \Illuminate\Http\Response
     */
    public function destroy(Subscription $subscription)
    {
        $subscription->delete();

        $subscriptions = Subscription::all();

        return $this->handleResponse(ResourcesSubscription::collection($subscriptions), __('notifications.delete_subscription_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Check if user is subscribed
     *
     * @param  int $user_id
     * @return \Illuminate\Http\Response
     */
    public function isSubscribed($user_id)
    {
        // Group
        $subscription_status_group = Group::where('group_name', 'Etat de l\'abonnement')->first();
        // Status
        $valid_status = Status::where([['status_name->fr', 'Valide'], ['group_id', $subscription_status_group->id]])->first();
        // Request
        $user = User::find($user_id);

        if (is_null($user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        $hasPivotValid = User::whereHas('subscriptions', function ($q) use ($user, $valid_status) {
                        $q->where('subscription_user.user_id', $user->id)
                            ->where('subscription_user.status_id', $valid_status->id);
                    })->exists();

        if ($hasPivotValid) {
            return $this->handleResponse(1, __('notifications.find_user_success'), null);

        } else {
            return $this->handleResponse(0, __('notifications.find_user_404'), null);
        }
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
        $payment_status_group = Group::where('group_name', 'Etat du paiement')->first();
        $payment_type_group = Group::where('group_name', 'Type de paiement')->first();
        // Status
        $ongoing_status = Status::where([['status_name->fr', 'En cours'], ['group_id', $cart_status_group->id]])->first();
        $paid_status = Status::where([['status_name->fr', 'Payé'], ['group_id', $cart_status_group->id]])->first();
        $pending_status = Status::where([['status_name->fr', 'En attente'], ['group_id', $subscription_status_group->id]])->first();
        $in_progress_status = Status::where([['status_name->fr', 'En cours'], ['group_id', $payment_status_group->id]])->first();
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

        $currency = Currency::find($subscription->currency_id);

        if (is_null($currency)) {
            return $this->handleError(__('notifications.find_currency_404'));
        }

        // Validations
        if ($request->transaction_type_id == null OR !is_numeric($request->transaction_type_id)) {
            return $this->handleError($request->transaction_type_id, __('validation.required'), 400);
        }

        // If the transaction is via mobile money
        if ($request->transaction_type_id == $mobile_money_type->id) {
            $reference_code = 'REF-' . ((string) random_int(10000000, 99999999)) . '-' . $current_user->id;

            // Create response by sending request to FlexPay
            $data = array(
                'merchant' => config('services.flexpay.merchant'),
                'type' => 1,
                'phone' => $request->other_phone,
                'reference' => $reference_code,
                'amount' => ((int) $subscription->price),
                'currency' => $currency->currency_acronym,
                'callbackUrl' => getApiURL() . '/payment/store'
            );
            $data = json_encode($data);
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $gateway_mobile);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, Array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . config('services.flexpay.api_token')
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

                $jsonRes = json_decode($response, true);
                $code = $jsonRes['code']; // Push sending status

                if ($code != '0') {
                    return $this->handleError(__('miscellaneous.error_label'), __('notifications.transaction_push_failed'), 400);

                } else {
                    // Register payment, even if FlexPay will
                    $payment = Payment::where('order_number', $jsonRes['orderNumber'])->first();

                    if (is_null($payment)) {
                        $payment = Payment::create([
                            'reference' => $reference_code,
                            'order_number' => $jsonRes['orderNumber'],
                            'amount' => ((int) $subscription->price),
                            'phone' => $request->other_phone,
                            'currency' => $currency->currency_acronym,
                            'type_id' => $request->transaction_type_id,
                            'status_id' => $in_progress_status->id,
                            'user_id' => $current_user->id
                        ]);
                    }

                    // The subscription is created only if the processing succeed
                    $current_user->subscriptions()->attach($subscription->id, ['payment_id' => $payment->id, 'status_id' => $pending_status->id]);

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
                        'message' => $jsonRes['message'],
                        'order_number' => $jsonRes['orderNumber']
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
                'amount' => ((int) $subscription->price),
                'currency' => $currency->currency_acronym,
                'description' => __('miscellaneous.bank_transaction_description'),
                'callback_url' => getApiURL() . '/payment/store',
                'approve_url' => $request->app_url . '/subscribed/' . ((int) $subscription->price) . '/USD/0/' . $current_user->id . '?app_id=',
                'cancel_url' => $request->app_url . '/subscribed/' . ((int) $subscription->price) . '/USD/1/' . $current_user->id . '?app_id=',
                'decline_url' => $request->app_url . '/subscribed/' . ((int) $subscription->price) . '/USD/2/' . $current_user->id . '?app_id=',
                'home_url' => $request->app_url . '/subscribe?app_id=&subscription_id=' . $subscription->id . '&user_id=' . $current_user->id . '&api_token=' . $current_user->api_token,
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
                        $payment = Payment::create([
                            'reference' => $reference_code,
                            'order_number' => $orderNumber,
                            'amount' => ((int) $subscription->price),
                            'phone' => $request->other_phone,
                            'currency' => $currency->currency_acronym,
                            'type_id' => $request->transaction_type_id,
                            'status_id' => $in_progress_status->id,
                            'user_id' => $current_user->id
                        ]);
                    }

                    // The subscription is created only if the processing succeed
                    $current_user->subscriptions()->attach($subscription->id, ['payment_id' => $payment->id, 'status_id' => $pending_status->id]);

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

    /**
     * Validate a user subscription.
     *
     * @param  int $user_id
     */
    public function validateSubscription($user_id)
    {
        // Groups
        $subscription_status_group = Group::where('group_name', 'Etat de l\'abonnement')->first();
        $payment_status_group = Group::where('group_name', 'Etat du paiement')->first();
        // Status
        $pending_status = Status::where([['status_name->fr', 'En attente'], ['group_id', $subscription_status_group->id]])->first();
        $valid_status = Status::where([['status_name->fr', 'Valide'], ['group_id', $subscription_status_group->id]])->first();
        $done_status = Status::where([['status_name->fr', 'Effectué'], ['group_id', $payment_status_group->id]])->first();
        // Requests
        $user = User::find($user_id);

        if (is_null($user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        // $pending_subscription = Subscription::whereHas('users', function ($query) use ($pending_status, $user) {
        //                                         $query->where('subscription_user.user_id', $user->id)
        //                                                 ->where('subscription_user.status_id', $pending_status->id);
        //                                     })->orderBy('updated_at', 'desc')->first();
        $pending_subscription_user = DB::table('subscription_user')->where([['user_id', $user->id], ['status_id', $pending_status->id]])->latest()->first();

        if ($pending_subscription_user != null) {
            // $subscription_pivot = $pending_subscription->users()->find($user->id)->pivot;
            $user_payment = Payment::find($pending_subscription_user->payment_id);

            if ($user_payment != null) {
                if ($user_payment->status_id == $done_status->id) {
                    // $user->subscriptions()->updateExistingPivot($pending_subscription->id, ['status_id' => $valid_status->id]);
                    DB::table('subscription_user')->where('user_id', $user->id)->where('payment_id', $user_payment->id)->update(['status_id' => $valid_status->id]);

                    return $this->handleResponse(new ResourcesUser($user), __('notifications.update_user_success'));
                }
            }

        } else {
            return $this->handleError(new ResourcesUser($user), __('notifications.find_subscription_404'), 404);
        }
    }

    /**
     * Invalidate a user subscription.
     *
     * @param  int $user_id
     */
    public function invalidateSubscription($user_id)
    {
        // Groups
        $subscription_status_group = Group::where('group_name', 'Etat de l\'abonnement')->first();
        // Status
        $valid_status = Status::where([['status_name->fr', 'Valide'], ['group_id', $subscription_status_group->id]])->first();
        $expired_status = Status::where([['status_name->fr', 'Expiré'], ['group_id', $subscription_status_group->id]])->first();
        // Requests
        $user = User::find($user_id);

        if (is_null($user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        // $valid_subscription = Subscription::whereHas('users', function ($query) use ($valid_status, $user) {
        //                                         $query->where('subscription_user.user_id', $user->id)
        //                                                 ->where('subscription_user.status_id', $valid_status->id);
        //                                     })->orderBy('updated_at', 'desc')->first();
        $valid_subscription_user = DB::table('subscription_user')->where([['user_id', $user->id], ['status_id', $valid_status->id]])->latest()->first();

        if ($valid_subscription_user != null) {
            $subscription = Subscription::find($valid_subscription_user->subscription_id);
            // Create two date instances
            $current_date = date('Y-m-d h:i:s');
            // $subscription_date = $valid_subscription->users()->pivot->created_at->format('Y-m-d h:i:s');
            $subscription_user_date = $valid_subscription_user->created_at;
            $current_date_instance = Carbon::parse($current_date);
            $subscription_user_date_instance = Carbon::parse($subscription_user_date);
            // Determine the difference between dates
            $diff = $current_date_instance->diff($subscription_user_date_instance);
            $diffInHours = $diff->days * 24 + $diff->h + $diff->i / 60;

            if (($subscription->number_of_hours - round($diffInHours)) > 0) {
                return $this->handleError(new ResourcesUser($user), __('notifications.invalidate_subscription_failed') . ' (TimeRemaining: '. ($subscription->number_of_hours - round($diffInHours)) .')', 400);

            } else {
                $user_payment = Payment::find($valid_subscription_user->payment_id);

                // $user->subscriptions()->updateExistingPivot($valid_subscription->id, ['status_id' => $expired_status->id]);
                DB::table('subscription_user')->where('user_id', $user->id)->where('payment_id', $user_payment->id)->update(['status_id' => $expired_status->id]);

                return $this->handleResponse(new ResourcesUser($user), __('notifications.update_user_success'));
            }

        } else {
            return $this->handleError(new ResourcesUser($user), __('notifications.find_subscription_404'), 404);
        }
    }
}
