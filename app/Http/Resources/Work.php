<?php

namespace App\Http\Resources;

use App\Models\File as ModelFile;
use App\Models\Type as ModelType;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class Work extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $files = File::collection($this->files)->sortByDesc('created_at')->toArray();
        $img_type = ModelType::where('type_name->fr', 'Image (Photo/Vidéo)')->first();
        $doc_type = ModelType::where('type_name->fr', 'Document')->first();
        $img = ModelFile::where([['type_id', $img_type->id], ['work_id', $this->id]])->first();
        $doc = ModelFile::where([['type_id', $doc_type->id], ['work_id', $this->id]])->first();

        return [
            'id' => $this->id,
            'work_title' => $this->work_title,
            'work_content' => $this->work_content,
            'video_url' => $this->work_url,
            'image_url' => !empty($files) ? (inArrayR($img_type->id, $files, 'type_id') ? getWebURL() . '/public' . $img->file_url : null) : 'https://boongo7.com/assets/img/cover.png',
            'document_url' => !empty($files) ? (inArrayR($doc_type->id, $files, 'type_id') ? getWebURL() . '/public' . $doc->file_url : null) : null,
            // 'image_url' => !empty($files) ? (inArrayR($img_type->id, $files, 'type_id') ? getWebURL() . '/boongo/public' . $img->file_url : null) : null,
            // 'document_url' => !empty($files) ? (inArrayR($doc_type->id, $files, 'type_id') ? getWebURL() . '/boongo/public' . $doc->file_url : null) : null,
            'type' => Type::make($this->type),
            'status' => Status::make($this->status),
            'user_owner' => User::make($this->user_owner),
            'categories' => Category::collection($this->categories),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'type_id' => $this->type_id,
            'status_id' => $this->status_id
        ];
    }
}
