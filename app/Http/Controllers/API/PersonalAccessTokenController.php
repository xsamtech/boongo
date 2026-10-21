<?php

namespace App\Http\Controllers\API;

use App\Models\PersonalAccessToken;
use Illuminate\Http\Request;
use Dedoc\Scramble\Attributes\BodyParameter;
use App\Http\Resources\PersonalAccessToken as ResourcesPersonalAccessToken;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class PersonalAccessTokenController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $personal_access_tokens = PersonalAccessToken::orderByDesc('created_at')->get();

        return $this->handleResponse(ResourcesPersonalAccessToken::collection($personal_access_tokens), __('notifications.find_all_personal_access_tokens_success'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    #[BodyParameter('tokenable_type', description: 'Colonne personal_access_tokens.tokenable_type : VARCHAR(255); NOT NULL.', required: false, type: 'string')]
    #[BodyParameter('tokenable_id', description: 'Colonne personal_access_tokens.tokenable_id : BIGINT; NOT NULL.', required: false, type: 'integer')]
    #[BodyParameter('name', description: 'Colonne personal_access_tokens.name : VARCHAR(255); NOT NULL.', required: false, type: 'string')]
    #[BodyParameter('token', description: 'Colonne personal_access_tokens.token : VARCHAR(64); NOT NULL.', required: false, type: 'string')]
    #[BodyParameter('abilities', description: 'Colonne personal_access_tokens.abilities : TEXT; NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('last_used_at', description: 'Colonne personal_access_tokens.last_used_at : TIMESTAMP; NULL autorisé.', required: false, type: 'string', format: 'date-time')]
    #[BodyParameter('expires_at', description: 'Colonne personal_access_tokens.expires_at : TIMESTAMP; NULL autorisé.', required: false, type: 'string', format: 'date-time')]
    public function store(Request $request)
    {
        // Get inputs
        $inputs = [
            'tokenable_type' => $request->tokenable_type,
            'tokenable_id' => $request->tokenable_id,
            'name' => $request->name,
            'token' => $request->token,
            'abilities' => $request->abilities,
            'last_used_at' => $request->last_used_at,
            'expires_at' => $request->expires_at
        ];
        // Select all personal access tokens to check unique constraint
        $personal_access_tokens = PersonalAccessToken::all();

        // Validate required fields
        if (trim($inputs['token']) == null) {
            return $this->handleError($inputs['token'], __('validation.required'), 400);
        }

        // Check if token already exists
        foreach ($personal_access_tokens as $another_personal_access_token):
            if ($another_personal_access_token->token == $inputs['token']) {
                return $this->handleError($inputs['token'], __('validation.custom.token.exists'), 400);
            }
        endforeach;

        $personal_access_token = PersonalAccessToken::create($inputs);

        return $this->handleResponse(new ResourcesPersonalAccessToken($personal_access_token), __('notifications.create_personal_access_token_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $personal_access_token = PersonalAccessToken::find($id);

        if (is_null($personal_access_token)) {
            return $this->handleError(__('notifications.find_personal_access_token_404'));
        }

        return $this->handleResponse(new ResourcesPersonalAccessToken($personal_access_token), __('notifications.find_personal_access_token_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    #[BodyParameter('id', description: 'Colonne personal_access_tokens.id : BIGINT; NOT NULL. Identifiant du corps utilisé par les contrôles existants, distinct du paramètre de route.', required: false, type: 'integer')]
    #[BodyParameter('tokenable_type', description: 'Colonne personal_access_tokens.tokenable_type : VARCHAR(255); NOT NULL.', required: false, type: 'string')]
    #[BodyParameter('tokenable_id', description: 'Colonne personal_access_tokens.tokenable_id : BIGINT; NOT NULL.', required: false, type: 'integer')]
    #[BodyParameter('name', description: 'Colonne personal_access_tokens.name : VARCHAR(255); NOT NULL.', required: false, type: 'string')]
    #[BodyParameter('token', description: 'Colonne personal_access_tokens.token : VARCHAR(64); NOT NULL.', required: false, type: 'string')]
    #[BodyParameter('abilities', description: 'Colonne personal_access_tokens.abilities : TEXT; NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('last_used_at', description: 'Colonne personal_access_tokens.last_used_at : TIMESTAMP; NULL autorisé.', required: false, type: 'string', format: 'date-time')]
    #[BodyParameter('expires_at', description: 'Colonne personal_access_tokens.expires_at : TIMESTAMP; NULL autorisé.', required: false, type: 'string', format: 'date-time')]
    public function update(Request $request, PersonalAccessToken $personal_access_token)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'tokenable_type' => $request->tokenable_type,
            'tokenable_id' => $request->tokenable_id,
            'name' => $request->name,
            'token' => $request->token,
            'abilities' => $request->abilities,
            'last_used_at' => $request->last_used_at,
            'expires_at' => $request->expires_at
        ];
        // Select all personal access tokens to check unique constraint
        $personal_access_tokens = PersonalAccessToken::all();
        $current_personal_access_token = PersonalAccessToken::find($inputs['id']);

        foreach (['tokenable_type', 'tokenable_id', 'name'] as $field) {
            if ($inputs[$field] != null) {
                $personal_access_token->update([
                    $field => $inputs[$field],
                    'updated_at' => now()
                ]);
            }
        }

        if ($inputs['token'] != null) {
            foreach ($personal_access_tokens as $another_personal_access_token):
                if (($current_personal_access_token->token != $inputs['token']) && ($another_personal_access_token->token == $inputs['token'])) {
                    return $this->handleError($inputs['token'], __('validation.custom.token.exists'), 400);
                }
            endforeach;

            $personal_access_token->update([
                'token' => $request->token,
                'updated_at' => now(),
            ]);
        }

        foreach (['abilities', 'last_used_at', 'expires_at'] as $field) {
            if ($inputs[$field] != null) {
                $personal_access_token->update([
                    $field => $inputs[$field],
                    'updated_at' => now()
                ]);
            }
        }

        return $this->handleResponse(new ResourcesPersonalAccessToken($personal_access_token), __('notifications.update_personal_access_token_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PersonalAccessToken  $personal_access_token
     * @return \Illuminate\Http\Response
     */
    public function destroy(PersonalAccessToken $personal_access_token)
    {
        $personal_access_token->delete();

        $personal_access_tokens = PersonalAccessToken::all();

        return $this->handleResponse(ResourcesPersonalAccessToken::collection($personal_access_tokens), __('notifications.delete_personal_access_token_success'));
    }
}
