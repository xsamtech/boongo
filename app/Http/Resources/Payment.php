<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class Payment extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'provider_reference' => $this->provider_reference,
            'order_number' => $this->order_number,
            'amount' => $this->amount,
            'amount_customer' => $this->amount_customer,
            'phone' => $this->phone,
            'coins_amount' => $this->coins_amount,
            'coin_unit_price' => $this->coin_unit_price,
            'premium_duration_months' => $this->premium_duration_months,
            'premium_plan_amount' => $this->premium_plan_amount,
            'premium_plan_currency' => $this->premium_plan_currency,
            'currency' => $this->currency,
            'channel' => $this->channel,
            'reason' => $this->reason,
            'entity' => $this->entity,
            'entity_id' => $this->entity_id,
            'type' => Type::make($this->type),
            'status' => Status::make($this->status),
            'created_at' => timeAgo($this->created_at->format('Y-m-d H:i:s')),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'coins_credited_at' => $this->coins_credited_at->format('Y-m-d H:i:s'),
            'premium_activated_at' => $this->premium_activated_at->format('Y-m-d H:i:s'),
            'type_id' => $this->type_id,
            'status_id' => $this->status_id,
            'user_id' => $this->user_id
        ];
    }
}
