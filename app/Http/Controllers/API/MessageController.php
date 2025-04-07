<?php

namespace App\Http\Controllers\API;

use App\Models\Circle;
use App\Models\File;
use App\Models\Group;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Organization;
use App\Models\Status;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Resources\Message as ResourcesMessage;
use App\Http\Resources\User as ResourcesUser;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class MessageController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $messages = Message::orderByDesc('created_at')->get();

        return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Groups
        $message_status_group = Group::where('group_name->fr', 'Etat du message')->first();
        $notification_status_group = Group::where('group_name->fr', 'Etat de la notification')->first();
        $notification_type_group = Group::where('group_name->fr', 'Type de notification')->first();
        $message_type_group = Group::where('group_name->fr', 'Type de message')->first();
        // Statuses
        $unread_message_status = Status::where([['status_name->fr', 'Non lu'], ['group_id', $message_status_group->id]])->first();
        $unread_notification_status = Status::where([['status_name->fr', 'Non lue'], ['group_id', $notification_status_group->id]])->first();
        // Types
        $message_in_organisation_type = Type::where([['type_name->fr', 'Message dans l\'organisation'], ['group_id', $notification_type_group->id]])->first();
        $chat_type = Type::where([['type_name->fr', 'Discussion'], ['group_id', $message_type_group->id]])->first();
        // Get inputs
        $inputs = [
            'message_content' => $request->message_content,
            'answered_for' => $request->answered_for,
            'type_id' => isset($request->type_id) ? $request->type_id : $chat_type->id,
            'status_id' => isset($request->status_id) ? $request->status_id : $unread_message_status->id,
            'user_id' => $request->user_id,
            'addressee_user_id' => $request->addressee_user_id,
            'addressee_organization_id' => $request->addressee_organization_id,
            'addressee_circle_id' => $request->addressee_circle_id,
            'event_id' => $request->event_id
        ];

        // Validate required fields
        if ($inputs['message_content'] == null OR $inputs['message_content'] == ' ') {
            return $this->handleError($inputs['message_content'], __('validation.required'), 400);
        }

        if ($inputs['user_id'] == null OR $inputs['user_id'] == ' ') {
            return $this->handleError($inputs['user_id'], __('validation.required'), 400);
        }

        $message_sender = User::find($inputs['user_id']);

        if (is_null($message_sender)) {
            return $this->handleError(__('notifications.find_sender_404'));
        }

        $message = Message::create($inputs);

        if ($request->hasFile('file_url')) {
            if ($request->file_type_id == null) {
                return $this->handleError($request->file_type_id, __('validation.required') . ' (' . __('miscellaneous.file_type') . ') ', 400);
            }

            $type = Type::find($request->file_type_id);

            if (is_null($type)) {
                return $this->handleError(__('notifications.find_type_404'));
            }

            // Group
            $file_type_group = Group::where('group_name', 'Type de fichier')->first();
            // Types
            $image_type = Type::where([['type_name', 'Image (Photo/Vidéo)'], ['group_id', $file_type_group->id]])->first();
            $document_type = Type::where([['type_name', 'Document'], ['group_id', $file_type_group->id]])->first();
            $audio_type = Type::where([['type_name', 'Audio'], ['group_id', $file_type_group->id]])->first();

            if ($type->id == $image_type->id AND $type->id == $document_type->id AND $type->id == $audio_type->id) {
                return $this->handleError(__('notifications.type_is_not_file'));
            }

            $custom_path = ($type->id == $document_type->id ? 'documents/messages' : ($type->id == $audio_type->id ? 'audios/messages' : 'images/messages'));
            $file_url =  $custom_path . '/' . $message->id . '/' . Str::random(50) . '.' . $request->file('file_url')->extension();

            // Upload file
            $dir_result = Storage::url(Storage::disk('public')->put($file_url, $request->file('file_url')));

            File::create([
                'file_name' => trim($request->file_name) != null ? $request->file_name : $request->file('file_url')->getClientOriginalName(),
                'file_url' => $dir_result,
                'type_id' => $type->id,
                'message_id' => $message->id
            ]);
        }

        /*
            HISTORY AND/OR NOTIFICATION MANAGEMENT
        */
        // If the message is sent to the organization, notify to all organization members
        if ($inputs['addressee_organization_id'] != null) {
            $addressee_organization = Organization::find($inputs['addressee_organization_id']);

            if (is_null($addressee_organization)) {
                return $this->handleError(__('notifications.find_organization_404'));
            }

            $organization_users = $addressee_organization->users;
            $other_organization_messages = Message::whereNotNull('answered_for')->where('addressee_organization_id', $inputs['addressee_organization_id'])->get();

            // If there is no other message to the organisation, send notification
            if ($other_organization_messages == null) {
                foreach ($organization_users as $user):
                    Notification::create([
                        'type_id' => $message_in_organisation_type->id,
                        'status_id' => $unread_notification_status->id,
                        'from_user_id' => $message_sender->id,
                        'to_user_id' => $user->id,
                        'message_id' => $message->id
                    ]);
                endforeach;
            }
        }

        return $this->handleResponse(new ResourcesMessage($message), __('notifications.create_message_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        // Group
        $message_status_group = Group::where('group_name->fr', 'Etat du message')->first();
        // Status
        $read_message_status = Status::where([['status_name->fr', 'Lu'], ['group_id', $message_status_group->id]])->first();
        // Request
        $message = Message::find($id);

        if (is_null($message)) {
            return $this->handleError(__('notifications.find_message_404'));
        }

        // If the message is sent to the organization or circle, identify the user who saw it
        if ($message->addressee_organization_id != null OR $message->addressee_circle_id != null) {
            if ($request->hasHeader('X-user-id')) {
                $user = User::find($request->header('X-user-id'));

                if (!is_null($user)) {
                    $message->users()->updateExistingPivot($user->id, ['status_id' => $read_message_status->id]);
                }
            }
        }

        return $this->handleResponse(new ResourcesMessage($message), __('notifications.find_message_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Message $message)
    {
        // Get inputs
        $inputs = [
            'message_content' => $request->message_content,
            'answered_for' => $request->answered_for,
            'type_id' => $request->type_id,
            'status_id' => $request->status_id,
            'user_id' => $request->user_id,
            'addressee_user_id' => $request->addressee_user_id,
            'addressee_organization_id' => $request->addressee_organization_id,
            'addressee_circle_id' => $request->addressee_circle_id,
            'event_id' => $request->event_id
        ];

        if ($inputs['message_content'] != null) {
            $message->update([
                'message_content' => $inputs['message_content'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['answered_for'] != null) {
            $message->update([
                'answered_for' => $inputs['answered_for'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['type_id'] != null) {
            $message->update([
                'type_id' => $inputs['type_id'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['status_id'] != null) {
            $message->update([
                'status_id' => $inputs['status_id'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['user_id'] != null) {
            $message->update([
                'user_id' => $inputs['user_id'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['addressee_user_id'] != null) {
            $message->update([
                'addressee_user_id' => $inputs['addressee_user_id'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['addressee_organization_id'] != null) {
            $message->update([
                'addressee_organization_id' => $inputs['addressee_organization_id'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['addressee_circle_id'] != null) {
            $message->update([
                'addressee_circle_id' => $inputs['addressee_circle_id'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['event_id'] != null) {
            $message->update([
                'event_id' => $inputs['event_id'],
                'updated_at' => now(),
            ]);
        }

        return $this->handleResponse(new ResourcesMessage($message), __('notifications.update_message_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function destroy(Message $message)
    {
        $message->delete();

        $messages = Message::orderByDesc('created_at')->get();

        return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.delete_message_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Search a message in chat.
     *
     * @param  string $locale
     * @param  string $type_name
     * @param  string $data
     * @param  int $sender_id
     * @param  int $addressee_id
     * @return \Illuminate\Http\Response
     */
    public function searchInChat($locale, $type_name, $data, $sender_id, $addressee_id)
    {
        // Requests
        $message_type = Type::where('type_name->' . $locale, $type_name)->first();

        if (is_null($message_type)) {
            return $this->handleError(__('notifications.find_type_404'));
        }

        $sender = User::find($sender_id);

        if (is_null($sender)) {
            return $this->handleError(__('notifications.find_sender_404'));
        }

        $addressee = User::find($addressee_id);

        if (is_null($sender)) {
            return $this->handleError(__('notifications.find_addressee_404'));
        }

        $messages = Message::where([['message_content', 'LIKE', '%' . $data . '%'], ['type_id', $message_type->id], ['user_id', $sender->id], ['addressee_user_id', $addressee->id]])->orWhere([['message_content', 'LIKE', '%' . $data . '%'], ['type_id', $message_type->id], ['user_id', $addressee->id], ['addressee_user_id', $sender->id]])->orderByDesc('created_at')->paginate(12);
        $count_messages = Message::where([['message_content', 'LIKE', '%' . $data . '%'], ['type_id', $message_type->id], ['user_id', $sender->id], ['addressee_user_id', $addressee->id]])->orWhere([['message_content', 'LIKE', '%' . $data . '%'], ['type_id', $message_type->id], ['user_id', $addressee->id], ['addressee_user_id', $sender->id]])->count();

        if (is_null($messages)) {
            return $this->handleResponse([], __('miscellaneous.empty_list'));
        }

        return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'), $messages->lastPage(), $count_messages);
    }

    /**
     * Search a message in group (organization or circle).
     *
     * @param  string $entity
     * @param  int $entity_id
     * @param  int $member_id
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function searchInGroup($entity, $entity_id, $member_id, $data)
    {
        // Requests
        $member = User::find($member_id);

        if (is_null($member)) {
            return $this->handleError(__('notifications.find_member_404'));
        }

        if ($entity == 'organization') {
            $organization = Organization::find($entity_id);

            if (is_null($organization)) {
                return $this->handleError(__('notifications.find_organization_404'));
            }

            $messages = Message::where([['message_content', 'LIKE', '%' . $data . '%'], ['addressee_organization_id', $organization->id]])->orderByDesc('created_at')->paginate(12);
            $count_messages = Message::where([['message_content', 'LIKE', '%' . $data . '%'], ['addressee_organization_id', $organization->id]])->count();

            if (is_null($messages)) {
                return $this->handleResponse([], __('miscellaneous.empty_list'));
            }

            return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'), $messages->lastPage(), $count_messages);
        }

        if ($entity == 'circle') {
            $circle = Circle::find($entity_id);

            if (is_null($circle)) {
                return $this->handleError(__('notifications.find_circle_404'));
            }

            $messages = Message::where([['message_content', 'LIKE', '%' . $data . '%'], ['addressee_circle_id', $circle->id]])->orderByDesc('created_at')->paginate(12);
            $count_messages = Message::where([['message_content', 'LIKE', '%' . $data . '%'], ['addressee_circle_id', $circle->id]])->count();

            if (is_null($messages)) {
                return $this->handleResponse([], __('miscellaneous.empty_list'));
            }

            return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'), $messages->lastPage(), $count_messages);
        }
    }

    /**
     * GET: Display user chat with another.
     *
     * @param  string $locale
     * @param  string $type_name
     * @param  int $sender_id
     * @param  int $addressee_user_id
     * @return \Illuminate\Http\Response
     */
    public function chatWithUser($locale, $type_name, $sender_id, $addressee_user_id)
    {
        $type = Type::where('type_name->' . $locale, $type_name)->first();

        if (is_null($type)) {
            return $this->handleError(__('notifications.find_type_404'));
        }

        $sender = User::find($sender_id);

        if (is_null($sender)) {
            return $this->handleError(__('notifications.find_sender_404'));
        }

        $addressee = User::find($addressee_user_id);

        if (is_null($addressee)) {
            return $this->handleError(__('notifications.find_addressee_404'));
        }

        $messages = Message::where([['type_id', $type->id], ['user_id', $sender->id], ['addressee_user_id', $addressee->id]])->orWhere([['type_id', $type->id], ['user_id', $addressee->id], ['addressee_user_id', $sender->id]])->orderByDesc('created_at')->paginate(12);
        $count_messages = Message::where([['type_id', $type->id], ['user_id', $sender->id], ['addressee_user_id', $addressee->id]])->orWhere([['type_id', $type->id], ['user_id', $addressee->id], ['addressee_user_id', $sender->id]])->count();

        return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'), $messages->lastPage(), $count_messages);
    }

    /**
     * GET: Display all group (organization or circle) messages.
     *
     * @param  string $entity
     * @param  int $entity_id
     * @return \Illuminate\Http\Response
     */
    public function chatWithGroup($entity, $entity_id)
    {
        if ($entity == 'organization') {
            $organization = Organization::find($entity_id);

            if (is_null($organization)) {
                return $this->handleError(__('notifications.find_organization_404'));
            }

            $messages = Message::where('addressee_organization_id', $organization->id)->orderByDesc('created_at')->paginate(12);
            $count_messages = Message::where('addressee_organization_id', $organization->id)->count();

            return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'), $messages->lastPage(), $count_messages);
        }

        if ($entity == 'circle') {
            $circle = Circle::find($entity_id);

            if (is_null($circle)) {
                return $this->handleError(__('notifications.find_circle_404'));
            }

            $messages = Message::where('addressee_circle_id', $circle->id)->orderByDesc('created_at')->paginate(12);
            $count_messages = Message::where('addressee_circle_id', $circle->id)->count();

            return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'), $messages->lastPage(), $count_messages);
        }
    }

    /**
     * GET: Display all organization/circle members with specific status.
     *
     * @param  string $locale
     * @param  string $status_name
     * @param  int $message_id
     * @return \Illuminate\Http\Response
     */
    public function membersWithMessageStatus($locale, $status_name, $message_id)
    {
        // Group
        $group = Group::where('group_name->fr', 'Etat du message')->first();
        // Status
        $status = Status::where([['status_name->' . $locale, $status_name], ['group_id', $group->id]])->first();

        if (is_null($status)) {
            return $this->handleError(__('notifications.find_status_404'));
        }

        $message = Message::find($message_id);

        if (is_null($message)) {
            return $this->handleError(__('notifications.find_message_404'));
        }

        $users = $message->users()->wherePivot('status_id', $status->id)->orderByDesc('created_at')->paginate(12);
        $count_users = $message->users()->wherePivot('status_id', $status->id)->count();

        return $this->handleResponse(ResourcesUser::collection($users), __('notifications.find_all_users_success'), $users->lastPage(), $count_users);
    }

    /**
     * Delete message for a specific user.
     *
     * @param  int $user_id
     * @param  int $message_id
     * @param  string $entity
     * @return \Illuminate\Http\Response
     */
    public function deleteForMyself($user_id, $message_id, $entity)
    {
        $message_status_group = Group::where('group_name->fr', 'Etat du message')->first();
        $deleted_message_status = Status::where([['status_name->fr', 'Supprimé'], ['group_id', $message_status_group->id]])->first();
        $user = User::find($user_id);

        if (is_null($user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        $message = Message::find($message_id);

        if (is_null($message)) {
            return $this->handleError(__('notifications.find_message_404'));
        }

        if ($entity == 'personal') {
            $message->update([
                'status_id' => $deleted_message_status->id,
                'updated_at' => now()
            ]);
        }

        if ($entity == 'group') {
            $message->users()->updateExistingPivot($user->id, ['status_id' => $deleted_message_status->id]);
        }

        return $this->handleResponse(new ResourcesMessage($message), __('notifications.find_message_success'));
    }

    /**
     * Delete message for everybody.
     *
     * @param  int $message_id
     * @return \Illuminate\Http\Response
     */
    public function deleteForEverybody($message_id)
    {
        $message = Message::find($message_id);

        if (is_null($message)) {
            return $this->handleError(__('notifications.find_message_404'));
        }

        $message->update([
            'message_content' => __('notifications.delete_message_success'),
            'updated_at' => now()
        ]);
    }

    /**
     * GET: Mark all received messages as read.
     *
     * @param  string $locale
     * @param  string $type_name
     * @param  int $sender_id
     * @param  int $addressee_user_id
     * @return \Illuminate\Http\Response
     */
    public function markAllReadUser($locale, $type_name, $sender_id, $addressee_user_id)
    {
        // Group
        $message_status_group = Group::where('group_name->fr', 'Etat du message')->first();
        // Status
        $read_message_status = Status::where([['status_name->fr', 'Lu'], ['group_id', $message_status_group->id]])->first();
        // Requests
        $type = Type::where('type_name->' . $locale, $type_name)->first();

        if (is_null($type)) {
            return $this->handleError(__('notifications.find_type_404'));
        }

        $sender = User::find($sender_id);

        if (is_null($sender)) {
            return $this->handleError(__('notifications.find_sender_404'));
        }

        $addressee = User::find($addressee_user_id);

        if (is_null($addressee)) {
            return $this->handleError(__('notifications.find_addressee_404'));
        }

        $all_messages = Message::where([['type_id', $type->id], ['user_id', $sender->id], ['addressee_user_id', $addressee->id]])->get();
        $messages = Message::where([['type_id', $type->id], ['user_id', $sender->id], ['addressee_user_id', $addressee->id]])->orderByDesc('created_at')->paginate(12);
        $count_messages = Message::where([['type_id', $type->id], ['user_id', $sender->id], ['addressee_user_id', $addressee->id]])->count();

        foreach ($all_messages as $message) {
            $message->update([
                'status_id' => $read_message_status->id,
                'updated_at' => now()
            ]);
        }

        return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'), $messages->lastPage(), $count_messages);
    }

    /**
     * GET: Mark all organization/circle messages as read.
     *
     * @param  int $user_id
     * @param  string $entity
     * @param  int $entity_id
     * @return \Illuminate\Http\Response
     */
    public function markAllReadGroup($user_id, $entity, $entity_id)
    {
        // Group
        $message_status_group = Group::where('group_name->fr', 'Etat du message')->first();
        // Status
        $read_message_status = Status::where([['status_name->fr', 'Lu'], ['group_id', $message_status_group->id]])->first();
        // Requests
        $user = User::find($user_id);

        if (is_null($user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        if ($entity == 'organization') {
            $organization = Organization::find($entity_id);

            if (is_null($organization)) {
                return $this->handleError(__('notifications.find_organization_404'));
            }

            $all_messages = Message::where('addressee_organization_id', $organization->id)->get();
            $messages = Message::where('addressee_organization_id', $organization->id)->orderByDesc('created_at')->paginate(12);
            $count_messages = Message::where('addressee_organization_id', $organization->id)->count();

            foreach ($all_messages as $message) {
                $message->users()->updateExistingPivot($user->id, ['status_id' => $read_message_status->id]);
            }

            return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'), $messages->lastPage(), $count_messages);
        }

        if ($entity == 'circle') {
            $circle = Circle::find($entity_id);

            if (is_null($circle)) {
                return $this->handleError(__('notifications.find_circle_404'));
            }

            $all_messages = Message::where('addressee_circle_id', $circle->id)->get();
            $messages = Message::where('addressee_circle_id', $circle->id)->orderByDesc('created_at')->paginate(12);
            $count_messages = Message::where('addressee_circle_id', $circle->id)->count();

            foreach ($all_messages as $message) {
                $message->users()->updateExistingPivot($user->id, ['status_id' => $read_message_status->id]);
            }

            return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'), $messages->lastPage(), $count_messages);
        }
    }
}
