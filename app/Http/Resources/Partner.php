<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class Partner extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // Call the remainingDays() function with the current date
        $remainingDays = $this->remainingDays(Carbon::now());

        return [
            'id' => $this->id,
            'name' => $this->name,
            'message' => $this->message,
            'image_url' => $this->image_url != null ? getWebURL() . '/public/storage/' . $this->image_url : getWebURL() . '/public/assets/img/ad.png',
            'website_url' => $this->website_url,
            'remaining_days' => $remainingDays,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s')
        ];
    }
}
