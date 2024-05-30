<?php

namespace App\Http\Controllers\API;

use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Http\Resources\Subscription as ResourcesSubscription;

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
            'type_id' => $request->type_id
        ];

        // Validate required fields
        if (trim($inputs['number_of_hours']) == null) {
            return $this->handleError($inputs['number_of_hours'], __('validation.required'), 400);
        }

        if (trim($inputs['price']) == null OR !is_numeric($inputs['price'])) {
            $inputs['price'] = 1;
        }

        if (trim($inputs['type_id']) == null OR !is_numeric($inputs['type_id'])) {
            return $this->handleError($inputs['type_id'], __('validation.required'), 400);
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
            'id' => $request->id,
            'number_of_hours' => $request->number_of_hours,
            'price' => $request->price,
            'type_id' => $request->type_id
        ];

        if (trim($inputs['number_of_hours']) != null AND is_numeric($inputs['number_of_hours'])) {
            // Select all subscriptions and current subscription to check unique constraint
            $subscriptions = Subscription::all();
            $current_subscription = Subscription::find($inputs['id']);

            if ($inputs['number_of_hours'] != null) {
                foreach ($subscriptions as $another_subscription):
                    if ($current_subscription->number_of_hours != $inputs['number_of_hours']) {
                        if ($another_subscription->number_of_hours == $inputs['number_of_hours']) {
                            return $this->handleError($inputs['number_of_hours'], __('validation.custom.number_of_hours.exists'), 400);
                        }
                    }
                endforeach;

                $subscription->update([
                    'number_of_hours' => $inputs['number_of_hours'],
                    'updated_at' => now()
                ]);
            }
        }

        if ($inputs['price'] != null) {
            $subscription->update([
                'price' => $inputs['price'],
                'updated_at' => now()
            ]);
        }

        if ($inputs['type_id'] != null) {
            $subscription->update([
                'type_id' => $inputs['type_id'],
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
}
