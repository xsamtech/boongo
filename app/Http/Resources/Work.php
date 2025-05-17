<?php

namespace App\Http\Resources;

use App\Models\File as ModelsFile;
use App\Models\Group as ModelsGroup;
use App\Models\ToxicContent as ModelsToxicContent;
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
        $doc = ModelsFile::where([['type_id', $doc_type->id], ['work_id', $this->id]])->first();
        $docs = ModelsFile::where([['type_id', $doc_type->id], ['work_id', $this->id]])->get();
        $audio = ModelsFile::where([['type_id', $audio_type->id], ['work_id', $this->id]])->first();
        $audios = ModelsFile::where([['type_id', $audio_type->id], ['work_id', $this->id]])->get();
        $imgs = ModelsFile::where([['type_id', $img_type->id], ['work_id', $this->id]])->get();
        $photo = $imgs->first(fn($file) => isPhotoFile($file->file_url));
        $video = $imgs->first(fn($file) => isVideoFile($file->file_url));
        $is_toxic = !empty($this->user_id) ? ModelsToxicContent::where([['for_user_id', $this->user_id], ['is_unlocked', 0]])->exists() : false;

        return [
            'id' => $this->id,
            'work_title' => $this->work_title,
            'work_content' => $this->work_content,
            'work_url' => $this->work_url,
            'video_url' => $this->work_url,
            'video_source' => $this->video_source,
            'media_length' => $this->media_length,
            'is_public' => $this->is_public,
            'is_owner_blocked' => $is_toxic,
            'photo_url' => $photo ? $photo->file_url : getWebURL() . '/assets/img/cover.png',
            'video_url' => $video ? $photo->file_url : getWebURL() . '/assets/img/cover.png',
            'document_url' => !empty($files) ? (inArrayR($doc_type->id, $files, 'type_id') ? $doc->file_url : null) : null,
            'audio_url' => !empty($files) ? (inArrayR($audio_type->id, $files, 'type_id') ? $audio->file_url : null) : null,
            'images' => !empty($imgs) ? getArrayKeys($imgs, 'file_url') : null,
            'audios' => !empty($audios) ? getArrayKeys($audios, 'file_url') : null,
            'documents' => !empty($docs) ? getArrayKeys($docs, 'file_url') : null,
            'type' => Type::make($this->type),
            'status' => Status::make($this->status),
            'user_owner' => User::make($this->user_owner),
            'organization_owner' => User::make($this->organization_owner),
            'categories' => Category::collection($this->categories),
            'category' => Category::collection($this->category),
            'likes' => Like::collection($this->likes),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'created_at_explicit' => $this->created_at->format('Y') == date('Y') ? explicitDayMonth($this->created_at->format('Y-m-d H:i:s')) : explicitDate($this->created_at->format('Y-m-d H:i:s')),
            'updated_at_explicit' => $this->updated_at->format('Y') == date('Y') ? explicitDayMonth($this->updated_at->format('Y-m-d H:i:s')) : explicitDate($this->updated_at->format('Y-m-d H:i:s')),
            'created_at_ago' => timeAgo($this->created_at->format('Y-m-d H:i:s')),
            'updated_at_ago' => timeAgo($this->updated_at->format('Y-m-d H:i:s')),
            'type_id' => $this->type_id,
            'status_id' => $this->status_id,
            'user_id' => $this->user_id,
            'organization_id' => $this->organization_id,
            'category_id' => $this->category_id
        ];
    }
}
