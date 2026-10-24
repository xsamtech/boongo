<?php

namespace App\Http\Controllers\API;

use App\Models\Session;
use Illuminate\Http\Request;
use Dedoc\Scramble\Attributes\BodyParameter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Resources\Session as ResourcesSession;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class SessionController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        $sessions = Session::orderByDesc('created_at')->get();

        return $this->handleResponse(ResourcesSession::collection($sessions), __('notifications.find_all_sessions_success'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    #[BodyParameter('id', description: 'Colonne sessions.id : VARCHAR(255); NOT NULL.', required: false, type: 'string')]
    #[BodyParameter('ip_address', description: 'Colonne sessions.ip_address : VARCHAR(45); NULL autorisé.', required: true, type: 'string')]
    #[BodyParameter('user_agent', description: 'Colonne sessions.user_agent : TEXT; NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('payload', description: 'Colonne sessions.payload : LONGTEXT; NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('last_activity', description: 'Colonne sessions.last_activity : INT; NULL autorisé.', required: false, type: 'integer')]
    #[BodyParameter('user_id', description: 'Colonne sessions.user_id : BIGINT; NULL autorisé.', required: false, type: 'integer')]
    public function store(Request $request)
    {
        // Get inputs
        $inputs = [
            'id' => empty($request->id) ? Str::random(255) : $request->id,
            'ip_address' => $request->ip_address,
            'user_agent' => $request->user_agent,
            'payload' => $request->payload,
            'last_activity' => $request->last_activity,
            'user_id' => $request->user_id
        ];

        $validator = Validator::make($inputs, [
            'ip_address' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());       
        }

        $session = Session::create($inputs);

        return $this->handleResponse(new ResourcesSession($session), __('notifications.create_session_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     */
    public function show($id)
    {
        $session = Session::find($id);

        if (is_null($session)) {
            return $this->handleError(__('notifications.find_session_404'));
        }

        return $this->handleResponse(new ResourcesSession($session), __('notifications.find_session_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Notification  $notification
     */
    #[BodyParameter('ip_address', description: 'Colonne sessions.ip_address : VARCHAR(45); NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('user_agent', description: 'Colonne sessions.user_agent : TEXT; NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('payload', description: 'Colonne sessions.payload : LONGTEXT; NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('last_activity', description: 'Colonne sessions.last_activity : INT; NULL autorisé.', required: false, type: 'integer')]
    #[BodyParameter('user_id', description: 'Colonne sessions.user_id : BIGINT; NULL autorisé.', required: false, type: 'integer')]
    public function update(Request $request, Session $session)
    {
        // Get inputs
        $inputs = [
            'ip_address' => $request->ip_address,
            'user_agent' => $request->user_agent,
            'payload' => $request->payload,
            'last_activity' => $request->last_activity,
            'user_id' => $request->user_id
        ];

        foreach ($inputs as $field => $value) {
            if ($value != null) {
                $session->update([
                    $field => $value
                ]);
            }
        }

        $session->update($inputs);

        return $this->handleResponse(new ResourcesSession($session), __('notifications.update_session_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string $ip_address
     */
    public function destroy($ip_address)
    {
        $session = Session::where('ip_address', $ip_address)->first();

        if (is_null($session)) {
            return $this->handleError(__('notifications.find_session_404'));
        }

        $session->delete();

        $sessions = Session::all();

        return $this->handleResponse(ResourcesSession::collection($sessions), __('notifications.delete_session_success'));
    }
}
