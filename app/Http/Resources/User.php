<?php

namespace App\Http\Resources;

use App\Models\Group as ModelsGroup;
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
        $pending_status = Status::where([['status_name->fr', 'En attente'], ['group_id', $subscription_status_group->id]])->first();
        // Requests
        $roles = Role::collection($this->roles)->sortByDesc('created_at')->toArray();
        $is_subscribed = ModelsUser::whereHas('subscriptions', function ($q) use ($valid_status) {
                                        $q->where('subscription_user.user_id', $this->id)
                                            ->where('subscription_user.status_id', $valid_status->id);
                                    })->exists();
        $pending_subscription = ModelsSubscription::whereHas('users', function ($q) use ($pending_status) {
                                                        $q->where('subscription_user.user_id', $this->id)
                                                            ->where('subscription_user.status_id', $pending_status->id);
                                                    })->orderByDesc('updated_at')->first();
        $valid_subscription = ModelsSubscription::whereHas('users', function ($q) use ($valid_status) {
                                                        $q->where('subscription_user.user_id', $this->id)
                                                            ->where('subscription_user.status_id', $valid_status->id);
                                                    })->orderByDesc('updated_at')->first();

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
            'avatar_url' => $this->avatar_url != null ? (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/public/storage/' . $this->avatar_url : (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/public/assets/img/user.png',
            'country' => Country::make($this->country),
            'status' => Status::make($this->status),
            'is_partner' => inArrayR('Partenaire', $roles, 'role_name') ? true : false,
            'roles' => Role::collection($this->roles),
            'is_subscribed' => $is_subscribed ? true : false,
            'pending_subscription' => $pending_subscription,
            'valid_subscription' => $valid_subscription,
            // 'subscriptions' => Subscription::collection($this->subscriptions)->sortByDesc('created_at')->toArray(),
            'carts' => Cart::collection($this->carts)->sortByDesc('created_at')->toArray(),
            'payments' => Payment::collection($this->payments)->sortByDesc('created_at')->toArray(),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s')
        ];
    }
}
