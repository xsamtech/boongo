<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class Organization extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'org_name' => $this->org_name,
            'org_description' => $this->org_description,
            'id_number' => $this->id_number,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'p_o_box' => $this->p_o_box,
            'legal_status' => $this->legal_status,
            'year_of_creation' => $this->year_of_creation,
            'website_url' => $this->website_url,
            'type' => Type::make($this->type),
            'status' => Status::make($this->status),
            'programs' => Program::collection($this->programs),
            'cover_url' => $this->cover_url != null ? getWebURL() . '/public/storage/' . $this->cover_url : getWebURL() . '/assets/img/banner.png',
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'type_id' => $this->type_id,
            'status_id' => $this->status_id,
            'user_id' => $this->user_id
        ];
    }
}
