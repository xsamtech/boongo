<?php

namespace App\Http\Resources;

use App\Models\File as ModelFile;
use App\Models\Group as ModelsGroup;
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
        // Group
        $file_type_group = ModelsGroup::where('group_name', 'Type de fichier')->first();
        // Types
        $img_type = ModelType::where([['type_name->fr', 'Image (Photo/Vidéo)'], ['group_id', $file_type_group->id]])->first();
        $doc_type = ModelType::where([['type_name->fr', 'Document'], ['group_id', $file_type_group->id]])->first();
        $audio_type = ModelType::where([['type_name->fr', 'Audio'], ['group_id', $file_type_group->id]])->first();
        // Requests
        $img = ModelFile::where([['type_id', $img_type->id], ['work_id', $this->id]])->first();
        $doc = ModelFile::where([['type_id', $doc_type->id], ['work_id', $this->id]])->first();
        $audio = ModelFile::where([['type_id', $audio_type->id], ['work_id', $this->id]])->first();

        return [
            'id' => $this->id,
            'work_title' => $this->work_title,
            'work_content' => $this->work_content,
            'work_url' => $this->work_url,
            'video_url' => $this->work_url,
            'video_source' => $this->video_source,
            'media_length' => $this->media_length,
            'is_public' => $this->is_public,
            'photo_url' => !empty($files) ? (inArrayR($img_type->id, $files, 'type_id') ? (isPhotoFile(getWebURL() . $img->file_url) ? $img->file_url : null) : null) : getWebURL() . '/assets/img/cover.png',
            'video_url' => !empty($files) ? (inArrayR($img_type->id, $files, 'type_id') ? (isVideoFile($img->file_url) ? $img->file_url : null) : null) : getWebURL() . '/assets/img/cover.png',
            'document_url' => !empty($files) ? (inArrayR($doc_type->id, $files, 'type_id') ? $doc->file_url : null) : null,
            'audio_url' => !empty($files) ? (inArrayR($audio_type->id, $files, 'type_id') ? $audio->file_url : null) : null,
            'type' => Type::make($this->type),
            'status' => Status::make($this->status),
            'user_owner' => User::make($this->user_owner),
            'organization_owner' => User::make($this->organization_owner),
            'categories' => Category::collection($this->categories),
            'likes' => Like::collection($this->likes),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'type_id' => $this->type_id,
            'status_id' => $this->status_id,
            'user_id' => $this->user_id,
            'organization_id' => $this->organization_id
        ];
    }
}
