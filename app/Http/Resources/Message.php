<?php

namespace App\Http\Resources;

use App\Models\Group as ModelsGroup;
use App\Models\Type as ModelsType;
use App\Models\File as ModelsFile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class Message extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Group
        $file_type_group = ModelsGroup::where('group_name', 'Type de fichier')->first();
        // Types
        $doc_type = ModelsType::where([['type_name->fr', 'Document'], ['group_id', $file_type_group->id]])->first();
        $image_type = ModelsType::where([['type_name->fr', 'Image'], ['group_id', $file_type_group->id]])->first();
        $audio_type = ModelsType::where([['type_name->fr', 'Audio'], ['group_id', $file_type_group->id]])->first();
        // Requests
        $docs = ModelsFile::where([['type_id', $doc_type->id], ['message_id', $this->id]])->get();
        $images = ModelsFile::where([['type_id', $image_type->id], ['message_id', $this->id]])->get();
        $audios = ModelsFile::where([['type_id', $audio_type->id], ['message_id', $this->id]])->get();

        return [
            'id' => $this->id,
            'message_content' => $this->message_content,
            'answered_for' => $this->answered_for,
            'type' => Type::make($this->type),
            'status' => Status::make($this->status),
            'user' => User::make($this->user),
            'addressee_user' => User::make($this->addressee_user),
            'addressee_organization' => Organization::make($this->addressee_organization),
            'addressee_circle' => Circle::make($this->addressee_circle),
            'event' => Event::make($this->event),
            'likes' => Like::collection($this->likes),
            'documents' => $docs,
            'images' => $images,
            'audios' => $audios,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'created_at_explicit' => $this->created_at->format('Y') == date('Y') ? explicitDayMonth($this->created_at->format('Y-m-d H:i:s')) : explicitDate($this->created_at->format('Y-m-d H:i:s')),
            'updated_at_explicit' => $this->updated_at->format('Y') == date('Y') ? explicitDayMonth($this->updated_at->format('Y-m-d H:i:s')) : explicitDate($this->updated_at->format('Y-m-d H:i:s')),
            'created_at_ago' => timeAgo($this->created_at->format('Y-m-d H:i:s')),
            'updated_at_ago' => timeAgo($this->updated_at->format('Y-m-d H:i:s')),
            'addressee_organization_id' => $this->addressee_organization_id,
            'addressee_circle_id' => $this->addressee_circle_id,
            'addressee_user_id' => $this->addressee_user_id,
            'event_id' => $this->event_id
        ];
    }
}
