<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class PricingDescription extends JsonResource
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
            'description_title' => $this->description_title,
            'description_title_en' => $this->getTranslation('description_title', 'en'),
            'description_title_fr' => $this->getTranslation('description_title', 'fr'),
            'description_title_ln' => $this->getTranslation('description_title', 'ln'),
            'description_content' => $this->description_content,
            'description_content_en' => $this->getTranslation('description_content', 'en'),
            'description_content_fr' => $this->getTranslation('description_content', 'fr'),
            'description_content_ln' => $this->getTranslation('description_content', 'ln'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at->format('Y-m-d H:i:s'),
            'pricing_id' => $this->pricing_id,
        ];
    }
}
