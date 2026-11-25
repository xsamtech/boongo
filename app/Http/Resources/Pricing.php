<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class Pricing extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pricing_name' => $this->pricing_name,
            'pricing_name_en' => $this->getTranslation('pricing_name', 'en'),
            'pricing_name_fr' => $this->getTranslation('pricing_name', 'fr'),
            'pricing_name_ln' => $this->getTranslation('pricing_name', 'ln'),
            'pricing_type' => $this->pricing_type,
            'coins_amount' => $this->coins_amount,
            'premium_duration_months' => $this->premium_duration_months,
            'reason' => $this->reason,
            'pricing_cost' => $this->pricing_cost,
            'currency' => $this->currency,
            'image_url' => $this->image_url,
            'icon' => $this->icon,
            'color' => $this->color,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at->format('Y-m-d H:i:s')
        ];
    }
}
