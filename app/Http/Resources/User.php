<?php

namespace App\Http\Resources;

use App\Models\Group as ModelsGroup;
use App\Models\Payment as ModelsPayment;
use App\Models\Status as ModelsStatus;
use App\Models\Subscription as ModelsSubscription;
use App\Models\User as ModelsUser;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // Group
        $subscription_status_group = ModelsGroup::where('group_name', 'Etat de l\'abonnement')->first();
        // Status
        $valid_status = ModelsStatus::where([['status_name->fr', 'Valide'], ['group_id', $subscription_status_group->id]])->first();
        $pending_status = ModelsStatus::where([['status_name->fr', 'En attente'], ['group_id', $subscription_status_group->id]])->first();
        // Requests
        // $current_user = ModelsUser::find($this->id);
        $roles = Role::collection($this->roles)->sortByDesc('created_at')->toArray();
        $is_subscribed = ModelsUser::whereHas('subscriptions', function ($q) use ($valid_status) {
                                        $q->where('subscription_user.user_id', $this->id)
                                            ->where('subscription_user.status_id', $valid_status->id);
                                    })->exists();
        $pending_subscription = ModelsSubscription::whereHas('users', function ($q) use ($pending_status) {
                                                        $q->where('subscription_user.user_id', $this->id)
                                                            ->where('subscription_user.status_id', $pending_status->id);
                                                    })->latest()->first();
        $valid_subscription = ModelsSubscription::whereHas('users', function ($q) use ($valid_status) {
                                                        $q->where('subscription_user.user_id', $this->id)
                                                            ->where('subscription_user.status_id', $valid_status->id);
                                                    })->latest()->first();
        $recent_payment = ModelsPayment::where('user_id', $this->id)->latest()->first();
        $payment = new Payment($recent_payment);
        // $payment = ModelsPayment::find($current_user->subscriptions()->latest()->first()->pivot->payment_id);

        if ($pending_subscription != null) {
            return [
                'id' => $this->id,
                'firstname' => $this->firstname,
                'lastname' => $this->lastname,
                'surname' => $this->surname,
                'gender' => $this->gender,
                'birthdate' => $this->birthdate,
                'age' => !empty($this->birthdate) ? $this->age() : null,
                'city' => $this->city,
                'address_1' => $this->address_1,
                'address_2' => $this->address_2,
                'p_o_box' => $this->p_o_box,
                'email' => $this->email,
                'phone' => $this->phone,
                'username' => $this->username,
                'password' => $this->password,
                'email_verified_at' => $this->email_verified_at,
                'phone_verified_at' => $this->phone_verified_at,
                'remember_token' => $this->remember_token,
                'api_token' => $this->api_token,
                'avatar_url' => $this->avatar_url != null ? (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/public/storage/' . $this->avatar_url : (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/assets/img/avatar-' . $this->gender . '.png',
                'country' => Country::make($this->country),
                'status' => Status::make($this->status),
                'is_partner' => inArrayR('Partenaire', $roles, 'role_name') ? true : false,
                'roles' => Role::collection($this->roles),
                'is_subscribed' => $is_subscribed ? true : false,
                'pending_subscription' => $pending_subscription,
                // 'subscriptions' => Subscription::collection($this->subscriptions)->sortByDesc('created_at')->toArray(),
                'carts' => Cart::collection($this->carts)->sortByDesc('created_at')->toArray(),
                'recent_payment' => $payment,
                // 'payments' => Payment::collection($this->payments)->sortByDesc('created_at')->toArray(),
                'created_at' => $this->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $this->updated_at->format('Y-m-d H:i:s')
            ];

        } else if ($valid_subscription != null) {
            return [
                'id' => $this->id,
                'firstname' => $this->firstname,
                'lastname' => $this->lastname,
                'surname' => $this->surname,
                'gender' => $this->gender,
                'birthdate' => $this->birthdate,
                'age' => !empty($this->birthdate) ? $this->age() : null,
                'city' => $this->city,
                'address_1' => $this->address_1,
                'address_2' => $this->address_2,
                'p_o_box' => $this->p_o_box,
                'email' => $this->email,
                'phone' => $this->phone,
                'username' => $this->username,
                'password' => $this->password,
                'email_verified_at' => $this->email_verified_at,
                'phone_verified_at' => $this->phone_verified_at,
                'remember_token' => $this->remember_token,
                'api_token' => $this->api_token,
                'avatar_url' => $this->avatar_url != null ? (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/public/storage/' . $this->avatar_url : (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/assets/img/avatar-' . $this->gender . '.png',
                'country' => Country::make($this->country),
                'status' => Status::make($this->status),
                'is_partner' => inArrayR('Partenaire', $roles, 'role_name') ? true : false,
                'roles' => Role::collection($this->roles),
                'is_subscribed' => $is_subscribed ? true : false,
                'valid_subscription' => $valid_subscription,
                // 'subscriptions' => Subscription::collection($this->subscriptions)->sortByDesc('created_at')->toArray(),
                'carts' => Cart::collection($this->carts)->sortByDesc('created_at')->toArray(),
                'recent_payment' => $payment,
                // 'payments' => Payment::collection($this->payments)->sortByDesc('created_at')->toArray(),
                'created_at' => $this->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $this->updated_at->format('Y-m-d H:i:s')
            ];

        } else {
            return [
                'id' => $this->id,
                'firstname' => $this->firstname,
                'lastname' => $this->lastname,
                'surname' => $this->surname,
                'gender' => $this->gender,
                'birthdate' => $this->birthdate,
                'age' => !empty($this->birthdate) ? $this->age() : null,
                'city' => $this->city,
                'address_1' => $this->address_1,
                'address_2' => $this->address_2,
                'p_o_box' => $this->p_o_box,
                'email' => $this->email,
                'phone' => $this->phone,
                'username' => $this->username,
                'password' => $this->password,
                'email_verified_at' => $this->email_verified_at,
                'phone_verified_at' => $this->phone_verified_at,
                'remember_token' => $this->remember_token,
                'api_token' => $this->api_token,
                'avatar_url' => $this->avatar_url != null ? (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/public/storage/' . $this->avatar_url : (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/assets/img/avatar-' . $this->gender . '.png',
                'country' => Country::make($this->country),
                'status' => Status::make($this->status),
                'is_partner' => inArrayR('Partenaire', $roles, 'role_name') ? true : false,
                'roles' => Role::collection($this->roles),
                'is_subscribed' => $is_subscribed ? true : false,
                // 'subscriptions' => Subscription::collection($this->subscriptions)->sortByDesc('created_at')->toArray(),
                'carts' => Cart::collection($this->carts)->sortByDesc('created_at')->toArray(),
                'recent_payment' => $payment,
                // 'payments' => Payment::collection($this->payments)->sortByDesc('created_at')->toArray(),
                'created_at' => $this->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $this->updated_at->format('Y-m-d H:i:s')
            ];
        }
    }
}
