<?php

namespace App\Http\Controllers\API;

use App\Models\ReadNotification;
use Illuminate\Http\Request;
use Dedoc\Scramble\Attributes\BodyParameter;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ReadNotification as ResourcesReadNotification;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class ReadNotificationController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $read_notifications = ReadNotification::orderByDesc('created_at')->get();

        return $this->handleResponse(ResourcesReadNotification::collection($read_notifications), __('notifications.find_all_notifications_success'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    #[BodyParameter('text_content', description: 'Colonne read_notifications.text_content : TEXT; NOT NULL.', required: false, type: 'string')]
    #[BodyParameter('redirect_url', description: 'Colonne read_notifications.redirect_url : TEXT; NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('screen', description: 'Colonne read_notifications.screen : VARCHAR(255); NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('entity', description: 'Colonne read_notifications.entity : VARCHAR(255); NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('entity_id', description: 'Colonne read_notifications.entity_id : BIGINT; NULL autorisé.', required: false, type: 'integer')]
    #[BodyParameter('icon', description: 'Colonne read_notifications.icon : TEXT; NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('image_url', description: 'Colonne read_notifications.image_url : TEXT; NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('created_at', description: 'Colonne read_notifications.created_at : TIMESTAMP; NOT NULL. Défaut SQL : CURRENT_TIMESTAMP.', required: false, type: 'string', format: 'date-time')]
    #[BodyParameter('updated_at', description: 'Colonne read_notifications.updated_at : TIMESTAMP; NOT NULL. Défaut SQL : CURRENT_TIMESTAMP.', required: false, type: 'string', format: 'date-time')]
    #[BodyParameter('notification_id', description: 'Colonne read_notifications.notification_id : BIGINT; NULL autorisé.', required: true, type: 'integer')]
    #[BodyParameter('user_id', description: 'Colonne user_id : BIGINT; NOT NULL.', required: true, type: 'integer')]
    public function store(Request $request)
    {
        // Get inputs
        $inputs = [
            'text_content' => $request->text_content,
            'redirect_url' => $request->redirect_url,
            'screen' => $request->screen,
            'entity' => $request->entity,
            'entity_id' => $request->entity_id,
            'icon' => $request->icon,
            'image_url' => $request->image_url,
            'created_at' => $request->created_at,
            'updated_at' => $request->updated_at,
            'notification_id' => $request->notification_id,
            'user_id' => $request->user_id,
        ];

        $validator = Validator::make($inputs, [
            'notification_id' => ['required'],
            'user_id' => ['required'],
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());       
        }

        $read_notification = ReadNotification::create($inputs);

        return $this->handleResponse(new ResourcesReadNotification($read_notification), __('notifications.create_notification_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $read_notification = ReadNotification::find($id);

        if (is_null($read_notification)) {
            return $this->handleError(__('notifications.find_notification_404'));
        }

        return $this->handleResponse(new ResourcesReadNotification($read_notification), __('notifications.find_notification_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ReadNotification  $read_notification
     * @return \Illuminate\Http\Response
     */
    #[BodyParameter('id', description: 'Colonne read_notifications.id : BIGINT; NOT NULL. Identifiant du corps utilisé par les contrôles existants, distinct du paramètre de route.', required: false, type: 'integer')]
    #[BodyParameter('text_content', description: 'Colonne read_notifications.text_content : TEXT; NOT NULL.', required: false, type: 'string')]
    #[BodyParameter('redirect_url', description: 'Colonne read_notifications.redirect_url : TEXT; NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('screen', description: 'Colonne read_notifications.screen : VARCHAR(255); NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('entity', description: 'Colonne read_notifications.entity : VARCHAR(255); NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('entity_id', description: 'Colonne read_notifications.entity_id : BIGINT; NULL autorisé.', required: false, type: 'integer')]
    #[BodyParameter('icon', description: 'Colonne read_notifications.icon : TEXT; NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('image_url', description: 'Colonne read_notifications.image_url : TEXT; NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('created_at', description: 'Colonne read_notifications.created_at : TIMESTAMP; NOT NULL. Défaut SQL : CURRENT_TIMESTAMP.', required: false, type: 'string', format: 'date-time')]
    #[BodyParameter('updated_at', description: 'Colonne read_notifications.updated_at : TIMESTAMP; NOT NULL. Défaut SQL : CURRENT_TIMESTAMP.', required: false, type: 'string', format: 'date-time')]
    #[BodyParameter('notification_id', description: 'Colonne read_notifications.notification_id : BIGINT; NULL autorisé.', required: true, type: 'integer')]
    #[BodyParameter('user_id', description: 'Colonne user_id : BIGINT; NOT NULL.', required: true, type: 'integer')]
    public function update(Request $request, ReadNotification $read_notification)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'text_content' => $request->text_content,
            'redirect_url' => $request->redirect_url,
            'screen' => $request->screen,
            'entity' => $request->entity,
            'entity_id' => $request->entity_id,
            'icon' => $request->icon,
            'image_url' => $request->image_url,
            'created_at' => $request->created_at,
            'updated_at' => $request->updated_at,
            'notification_id' => $request->notification_id,
            'user_id' => $request->user_id,
        ];

        $validator = Validator::make($inputs, [
            'notification_id' => ['required'],
            'user_id' => ['required'],
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());       
        }

        $read_notification->update($inputs);

        return $this->handleResponse(new ResourcesReadNotification($read_notification), __('notifications.update_notification_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReadNotification  $read_notification
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReadNotification $read_notification)
    {
        $read_notification->delete();

        $read_notifications = ReadNotification::all();

        return $this->handleResponse(ResourcesReadNotification::collection($read_notifications), __('notifications.delete_notification_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Select all user read notifications.
     *
     * @param  $user_id
     * @return \Illuminate\Http\Response
     */
    public function selectByUser($user_id)
    {
        $read_notifications = ReadNotification::where('to_user_id', $user_id)->orderByDesc('created_at')->paginate(10);

        return $this->handleResponse(ResourcesReadNotification::collection($read_notifications), __('notifications.find_all_notifications_success'), $read_notifications->lastPage(), $read_notifications->total());
    }
}
