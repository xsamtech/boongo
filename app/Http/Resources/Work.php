<?php

namespace App\Http\Resources;

use App\Support\FileOrdering;
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
        $files = FileOrdering::sort($this->files);
        // Group
        $file_type_group = ModelsGroup::where('group_name', 'Type de fichier')->first();
        // Types
        $img_type = ModelType::where([['type_name->fr', 'Image (Photo/Vidéo)'], ['group_id', $file_type_group->id]])->first();
        $doc_type = ModelType::where([['type_name->fr', 'Document'], ['group_id', $file_type_group->id]])->first();
        $audio_type = ModelType::where([['type_name->fr', 'Audio'], ['group_id', $file_type_group->id]])->first();
        // Requests
        $is_toxic = !empty($this->user_id) ? ModelsToxicContent::where([['for_user_id', $this->user_id], ['is_unlocked', 0]])->exists() : false;
        $docs = $files->where('type_id', $doc_type->id)->values();
        $doc = $docs->first();
        $audios = $files->where('type_id', $audio_type->id)->values();
        $audio = $audios->first();
        $imgs = $files->where('type_id', $img_type->id)->values();
        $photo = null;
        $video = null;

        foreach ($imgs as $img) {
            $url = $img->file_url;

            if (isPhotoFile($url) && !$photo) {
                $photo = $img;
            } elseif (isVideoFile($url) && !$video) {
                $video = $img;
            }

            if ($photo && $video) {
                break;
            }
        }

        return [
            'id' => $this->id,
            'work_title' => $this->work_title,
            'work_content' => $this->work_content,
            'work_url' => $this->work_url,
            'video_source' => $this->video_source,
            'media_length' => $this->media_length,
            'author' => $this->author,
            'editor' => $this->editor,
            'is_public' => $this->is_public,
            'consultation_price' => !empty($this->consultation_price) ? formatIntegerNumber($this->consultation_price) : null,
            'number_of_hours' => $this->number_of_hours,
            'is_owner_blocked' => $is_toxic,
            'photo_url' => !empty($photo) ? $photo->file_url : getWebURL() . '/assets/img/cover.png',
            'video_url' => !empty($video) ? $video->file_url : null,
            'document_url' => $doc?->file_url,
            'audio_url' => $audio?->file_url,
            'images' => !empty($imgs) ? File::collection($imgs) : null,
            'audios' => !empty($audios) ? File::collection($audios) : null,
            'documents' => !empty($docs) ? File::collection($docs) : null,
            'currency' => Currency::make($this->currency),
            'type' => Type::make($this->type),
            'status' => Status::make($this->status),
            'user_owner' => LiteUser::make($this->user_owner),
            'organization_owner' => Organization::make($this->organization_owner),
            'category' => Category::make($this->category),
            'categories' => Category::collection($this->categories),
            'likes' => Like::collection($this->likes),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'created_at_explicit' => $this->created_at->format('Y') == date('Y') ? explicitDayMonth($this->created_at->format('Y-m-d H:i:s')) : explicitDate($this->created_at->format('Y-m-d H:i:s')),
            'updated_at_explicit' => $this->updated_at->format('Y') == date('Y') ? explicitDayMonth($this->updated_at->format('Y-m-d H:i:s')) : explicitDate($this->updated_at->format('Y-m-d H:i:s')),
            'created_at_ago' => timeAgo($this->created_at->format('Y-m-d H:i:s')),
            'updated_at_ago' => timeAgo($this->updated_at->format('Y-m-d H:i:s')),
            'currency_id' => $this->currency_id,
            'type_id' => $this->type_id,
            'status_id' => $this->status_id,
            'user_id' => $this->user_id,
            'organization_id' => $this->organization_id,
            'category_id' => $this->category_id
        ];
    }
}
