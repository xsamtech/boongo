<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class BaseController extends Controller
{
    /**
     * Handle response
     *
     * @param  $result
     * @param  $msg
     * @param  $lastPage
     * @param  $count
     * @param  $ad
     * @return \Illuminate\Http\Response
     */
    public function handleResponse($result, $msg, $lastPage = null, $count = null, $ad = null)
    {
        // Building the basic response
        $res = [
            'success' => true,
            'message' => $msg,
            'data'    => $result,
            'ad'    => $ad,
        ];

        // Add conditional keys only if they are defined
        if ($lastPage !== null) {
            $res['lastPage'] = $lastPage;
        }

        if ($count !== null) {
            $res['count'] = $count;
        }

        // Return JSON response
        return response()->json($res, 200);
    }

    /**
     * Handle response error
     *
     * @param  $error
     * @param array  $errorMsg
     * @param  $code
     * @return \Illuminate\Http\Response
     */
    public function handleError($error, $errorMsg = [], $code = 404)
    {
        if (empty($errorMsg)) {
            $res = [
                'success' => false,
                'message' => $error
            ];

            return response()->json($res, $code);
        }

        if (!empty($errorMsg)) {
            $res = [
                'success' => false,
                'data' => $error
            ];

            $res['message'] = $errorMsg;

            return response()->json($res, $code);
        }
    }

    /**
     * Generic function to update cart (consultation or subscription)
     * 
     * @param  $cart
     * @param  $payment
     * @param  $paid_status
     */
    // private function updateCart($cart, $payment, $paid_status)
    // {
    //     $random_string = (string) random_int(1000000, 9999999);
    //     $generated_number = 'BNG-' . $random_string . '-' . date('Y.m.d');

    //     $cart->update([
    //         'payment_code' => $generated_number,
    //         'status_id' => $paid_status->id,
    //         'payment_id' => $payment->id,
    //         'updated_at' => now()
    //     ]);
    // }

    /**
     * Generic function for creating a payment and updating carts
     * 
     * @param  $total_to_pay
     * @param  $current_user
     * @param  $reference_code
     * @param  $payment_type
     * @param  $cart_consultation
     * @param  $cart_subscription
     * @param  $status_paid
     * @param  $in_progress_status
     * @param  $request
     */
    // private function processPaymentAndUpdateCart($total_to_pay, $current_user, $reference_code, $payment_type, $cart_consultation, $cart_subscription, $status_paid, $in_progress_status, $request)
    // {
    //     // Create the FlexPay request
    //     $body = json_encode(array(
    //         'authorization' => 'Bearer ' . config('services.flexpay.api_token'),
    //         'merchant' => config('services.flexpay.merchant'),
    //         'reference' => $reference_code,
    //         'amount' => round($total_to_pay),
    //         'currency' => $current_user->currency->currency_acronym,
    //         'description' => __('miscellaneous.bank_transaction_description'),
    //         'callback_url' => getApiURL() . '/payment/store',
    //         'approve_url' => $request->app_url . '/subscribed/' . round($total_to_pay) . '/USD/0/' . $current_user->_id . '?app_id=',
    //         'cancel_url' => $request->app_url . '/subscribed/' . round($total_to_pay) . '/USD/1/' . $current_user->id . '?app_id=',
    //         'decline_url' => $request->app_url . '/subscribed/' . round($total_to_pay) . '/USD/2/' . $current_user->id . '?app_id=',
    //         'home_url' => $request->app_url . '/subscribe?app_id=&cart_consultation_id=' . $cart_consultation->id . '&cart_subscription_id=' . $cart_subscription->id . '&user_id=' . $current_user->id . '&api_token=' . $current_user->api_token,
    //     ));

    //     // Initialiser cURL et envoyer la requête
    //     $curl = curl_init(config('services.flexpay.gateway_card_v2'));

    //     curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
    //     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    //     $curlResponse = curl_exec($curl);
    //     $jsonRes = json_decode($curlResponse, true);

    //     if (curl_errno($curl)) {
    //         return $this->handleError(curl_errno($curl), __('notifications.transaction_request_failed'), 400);
    //     }

    //     curl_close($curl);

    //     $code = $jsonRes['code'];
    //     $message = $jsonRes['message'];

    //     if ($code != '0') {
    //         return $this->handleError(__('miscellaneous.error_label'), __('notifications.transaction_push_failed'), 400);
    //     }

    //     // Création du paiement
    //     $payment = Payment::where('order_number', $jsonRes['orderNumber'])->first();

    //     if (is_null($payment)) {
    //         $payment = Payment::create([
    //             'reference' => $reference_code,
    //             'order_number' => $jsonRes['orderNumber'],
    //             'amount' => round($total_to_pay),
    //             'phone' => $request->other_phone,
    //             'currency' => $current_user->currency->currency_acronym,
    //             'channel' => $request->channel,
    //             'type_id' => $payment_type->id,
    //             'status_id' => $in_progress_status->id,
    //             'user_id' => $current_user->id
    //         ]);
    //     }

    //     // Mise à jour des paniers
    //     if ($cart_consultation) {
    //         $this->updateCart($cart_consultation, $payment, $status_paid);
    //     }

    //     if ($cart_subscription) {
    //         $this->updateCart($cart_subscription, $payment, $status_paid);
    //     }

    //     return $jsonRes;
    // }
}
