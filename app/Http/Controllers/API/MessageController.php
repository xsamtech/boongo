<?php

namespace App\Http\Controllers\API;

use App\Models\File;
use App\Models\Group;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Status;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Resources\Message as ResourcesMessage;
use App\Http\Resources\Organization as ResourcesOrganization;
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
        $message_answer_type = Type::where([['type_name->fr', 'Réponse au message'], ['group_id', $notification_type_group->id]])->first();
        $message_in_community_type = Type::where([['type_name->fr', 'Message dans la communauté'], ['group_id', $notification_type_group->id]])->first();
        $chat_type = Type::where([['type_name->fr', 'Discussion'], ['group_id', $message_type_group->id]])->first();
        // Get inputs
        $inputs = [
            'message_content' => $request->message_content,
            'answered_for' => $request->answered_for,
            'type_id' => isset($request->type_id) ? $request->type_id : $chat_type->id,
            'status_id' => !empty($request->addressee_community_id) OR !empty($request->addressee_team_id) ? null : (isset($request->status_id) ? $request->status_id : $unread_message_status->id),
            'user_id' => $request->user_id,
            'addressee_user_id' => $request->addressee_user_id,
            'addressee_community_id' => $request->addressee_community_id,
            'addressee_team_id' => $request->addressee_team_id
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

        // If the message is sent to a user
        if ($inputs['addressee_user_id'] != null) {
            $addressee_user = User::find($inputs['addressee_user_id']);

            if (is_null($addressee_user)) {
                return $this->handleError(__('notifications.find_addressee_404'));
            }

            // Check if it's the first discussion before send
            $chat_with_addressee = Message::where([['user_id', $message_sender->id], ['addressee_user_id', $addressee_user->id]])->orWhere([['user_id', $addressee_user->id], ['addressee_user_id', $message_sender->id]])->get();

            /*
                HISTORY AND/OR NOTIFICATION MANAGEMENT
            */
            if (is_null($chat_with_addressee)) {
                $notification = Notification::create([
                    'type_id' => $new_chat_type->id,
                    'status_id' => $unread_notification_status->id,
                    'from_user_id' => $message_sender->id,
                    'to_user_id' => $addressee_user->id,
                    'message_id' => $message->id
                ]);

                History::create([
                    'type_id' => $activities_history_type->id,
                    'status_id' => $unread_history_status->id,
                    'from_user_id' => $message_sender->id,
                    'for_notification_id' => $notification->id
                ]);
            }
        }

        // If the message is sent to the community, notify to all community members
        if ($inputs['addressee_community_id'] != null) {
            $message = Message::create($inputs);

            $addressee_community = Community::find($inputs['addressee_community_id']);
            $community_users = $addressee_community->users;
            $users_ids = $community_users->pluck('id')->toArray();

            $message->users()->syncWithPivotValues($users_ids, ['status_id' => $unread_message_status->id]);

            if (is_null($addressee_community)) {
                return $this->handleError(__('notifications.find_community_404'));
            }

            /*
                HISTORY AND/OR NOTIFICATION MANAGEMENT
            */
            // If the message is not an answer to another message, send notification
            if ($message->answered_for == null) {
                foreach ($addressee_community->users as $user):
                    Notification::create([
                        'type_id' => $message_in_community_type->id,
                        'status_id' => $unread_notification_status->id,
                        'from_user_id' => $message_sender->id,
                        'to_user_id' => $user->id,
                        'message_id' => $message->id
                    ]);
                endforeach;

                $notification = Notification::where([['type_id', $message_in_community_type->id], ['message_id', $message->id]])->first();

                History::create([
                    'type_id' => $activities_history_type->id,
                    'status_id' => $unread_history_status->id,
                    'from_user_id' => $message_sender->id,
                    'for_notification_id' => $notification->id
                ]);
            }
        }

        // If the message is an answer
        if ($inputs['answered_for'] != null) {
            $originating_message = Message::find($inputs['answered_for']);

            if (is_null($originating_message)) {
                return $this->handleError(__('notifications.find_originating_message_404'));
            }

            /*
                HISTORY AND/OR NOTIFICATION MANAGEMENT
            */
            Notification::create([
                'type_id' => $message_answer_type->id,
                'status_id' => $unread_notification_status->id,
                'from_user_id' => $message_sender->id,
                'to_user_id' => $originating_message->user_id,
                'message_id' => $message->id
            ]);
        }

        return $this->handleResponse(new ResourcesMessage($message), __('notifications.create_message_success'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Message $message)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Message $message)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Message $message)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Message $message)
    {
        //
    }
}
