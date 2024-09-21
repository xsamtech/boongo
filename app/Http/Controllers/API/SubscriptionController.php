<?php

namespace App\Http\Controllers\API;

use App\Models\Group;
use App\Models\Payment;
use App\Models\Status;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\Subscription as ResourcesSubscription;
use App\Http\Resources\User as ResourcesUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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

        $status = Status::where('status_name->fr', 'En cours')->first();

        if (is_null($status)) {
            return $this->handleError(__('notifications.find_status_404'));
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
                    $pending_subscription_user->update(['status_id' => $valid_status->id]);

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
            $diffInHours = $current_date_instance->diffInHours($subscription_user_date_instance);

            if ($diffInHours < $subscription->number_of_hours) {
                return $this->handleError(new ResourcesUser($user), __('notifications.invalidate_subscription_failed' . ' (TimeRemaining: '. $diffInHours .')'), 400);

            } else {
                // $user->subscriptions()->updateExistingPivot($valid_subscription->id, ['status_id' => $expired_status->id]);
                $valid_subscription_user->update(['status_id' => $expired_status->id]);

                return $this->handleResponse(new ResourcesUser($user), __('notifications.update_user_success'));
            }

        } else {
            return $this->handleError(new ResourcesUser($user), __('notifications.find_subscription_404'), 404);
        }
    }
}
