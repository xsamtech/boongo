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
            'work_url' => $this->work_url,
            'type' => Type::make($this->type),
            'status' => Status::make($this->status),
            'user_owner' => User::make($this->user_owner),
            'categories' => Category::collection($this->categories),
            'image' => !empty($files) ? (inArrayR($img_type->id, $files, 'type_id') ? $img : null) : null,
            'document' => !empty($files) ? (inArrayR($doc_type->id, $files, 'type_id') ? $doc : null) : null,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'type_id' => $this->type_id,
            'status_id' => $this->status_id
        ];
    }
}
