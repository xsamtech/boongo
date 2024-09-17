<?php

namespace App\Http\Controllers\API;

use App\Models\Payment;
use App\Models\Status;
use Illuminate\Http\Request;
use App\Http\Resources\Payment as ResourcesPayment;
use App\Models\Group;
use App\Models\Type;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class PaymentController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $payments = Payment::orderByDesc('created_at')->get();

        return $this->handleResponse(ResourcesPayment::collection($payments), __('notifications.find_all_payments_success'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Groups
        $payment_type_group = Group::where('group_name', 'Type de paiement')->first();
        // Types
        $mobile_money_type = Type::where([['type_name->fr', 'Mobile money'], ['group_id', $payment_type_group->id]])->first();
        $bank_card_type = Type::where([['type_name->fr', 'Carte bancaire'], ['group_id', $payment_type_group->id]])->first();
        $user_id = is_numeric(explode('-', $request->reference)[2]) ? (int) explode('-', $request->reference)[2] : null;
        // Check if payment already exists
        $payment = Payment::where('order_number', $request->orderNumber)->first();

        // If payment exists
        if ($payment != null) {
            $payment->update([
                'reference' => $request->reference,
                'provider_reference' => $request->provider_reference,
                'order_number' => $request->orderNumber,
                'amount' => $request->amount,
                'amount_customer' => $request->amountCustomer,
                'phone' => $request->phone,
                'currency' => $request->currency,
                'channel' => $request->channel,
                'type_id' => isset($request->type) ? ($request->type == 1 ? $mobile_money_type->id : $bank_card_type->id) : null,
                'status_id' => $request->code,
                'user_id' => $user_id,
                'updated_at' => now()
            ]);

            return $this->handleResponse(new ResourcesPayment($payment), __('notifications.update_payment_success'));

        // Otherwise, create new payment
        } else {
            $payment = Payment::create([
                'reference' => $request->reference,
                'provider_reference' => $request->provider_reference,
                'order_number' => $request->orderNumber,
                'amount' => $request->amount,
                'amount_customer' => $request->amountCustomer,
                'phone' => $request->phone,
                'currency' => $request->currency,
                'channel' => $request->channel,
                'created_at' => $request->createdAt,
                'type_id' => isset($request->type) ? ($request->type == 1 ? $mobile_money_type->id : $bank_card_type->id) : null,
                'status_id' => $request->code,
                'user_id' => $user_id
            ]);

            return $this->handleResponse(new ResourcesPayment($payment), __('notifications.create_payment_success'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $payment = Payment::find($id);

        if (is_null($payment)) {
            return $this->handleError(__('notifications.find_payment_404'));
        }

        return $this->handleResponse(new ResourcesPayment($payment), __('notifications.find_payment_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Payment $payment)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'reference' => $request->reference,
            'provider_reference' => $request->provider_reference,
            'order_number' => $request->order_number,
            'amount' => $request->amount,
            'amount_customer' => $request->amount_customer,
            'phone' => $request->phone,
            'currency' => $request->currency,
            'channel' => $request->channel,
            'type_id' => $request->type_id,
            'status_id' => $request->status_id,
            'user_id' => $request->user_id,
            'updated_at' => now()
        ];

        $payment->update($inputs);

        return $this->handleResponse(new ResourcesPayment($payment), __('notifications.update_payment_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Payment $payment)
    {
        $payment->delete();

        $payments = Payment::all();

        return $this->handleResponse(ResourcesPayment::collection($payments), __('notifications.delete_payment_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * find all payments having a same phone number.
     *
     * @param  string $phone_number
     * @return \Illuminate\Http\Response
     */
    public function findByPhone($phone_number)
    {
        $payments = Payment::where('phone', $phone_number)->get();

        return $this->handleResponse(ResourcesPayment::collection($payments), __('notifications.find_all_payments_success'));
    }

    /**
     * find payment by order number.
     *
     * @param  string $order_number
     * @param  string $user_id
     * @return \Illuminate\Http\Response
     */
    public function findByOrderNumber($order_number)
    {
        $payment = Payment::where('order_number', $order_number)->first();

        if (is_null($payment)) {
            return $this->handleResponse(null, __('notifications.find_payment_404'));
        }

        return $this->handleResponse(new ResourcesPayment($payment), __('notifications.find_payment_success'));
    }

    /**
     * find payment by order number and user.
     *
     * @param  string $order_number
     * @param  string $user_id
     * @return \Illuminate\Http\Response
     */
    public function findByOrderNumberUser($order_number, $user_id)
    {
        $payment = Payment::where([['order_number', $order_number], ['user_id', $user_id]])->first();

        if (is_null($payment)) {
            return $this->handleResponse(null, __('notifications.find_payment_404'));
        }

        return $this->handleResponse(new ResourcesPayment($payment), __('notifications.find_payment_success'));
    }

    /**
     * Change payment status.
     *
     * @param  $id
     * @param  $status_id
     * @return \Illuminate\Http\Response
     */
    public function switchStatus($id, $status_id)
    {
        $status = Status::find($status_id);

        if (is_null($status)) {
            return $this->handleError(__('notifications.find_status_404'));
        }

        $payment = Payment::find($id);

        // update "status_id" column
        $payment->update([
            'status_id' => $status->id,
            'updated_at' => now()
        ]);

        return $this->handleResponse(new ResourcesPayment($payment), __('notifications.find_payment_success'));
    }
}
