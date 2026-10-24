<?php

namespace App\Http\Controllers\API;

use App\Models\Group;
use App\Models\Status;
use Illuminate\Http\Request;
use Dedoc\Scramble\Attributes\BodyParameter;
use App\Http\Resources\Status as ResourcesStatus;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class StatusController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        $statuses = Status::all();

        return $this->handleResponse(ResourcesStatus::collection($statuses), __('notifications.find_all_statuses_success'));
    }

    /**
     * Store a resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    #[BodyParameter('status_name_en', description: 'Colonne statuses.status_name : JSON; NOT NULL. Traduction en transmise séparément.', required: false, type: 'string')]
    #[BodyParameter('status_name_fr', description: 'Colonne statuses.status_name : JSON; NOT NULL. Traduction fr transmise séparément.', required: false, type: 'string')]
    #[BodyParameter('status_name_ln', description: 'Colonne statuses.status_name : JSON; NOT NULL. Traduction ln transmise séparément.', required: false, type: 'string')]
    #[BodyParameter('status_description', description: 'Colonne statuses.status_description : TEXT; NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('icon', description: 'Colonne statuses.icon : VARCHAR(45); NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('color', description: 'Colonne statuses.color : VARCHAR(45); NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('group_id', description: 'Colonne statuses.group_id : BIGINT; NULL autorisé.', required: true, type: 'integer')]
    public function store(Request $request)
    {
        // Get inputs
        $inputs = [
            'status_name' => [
                'en' => $request->status_name_en,
                'fr' => $request->status_name_fr,
                'ln' => $request->status_name_ln
            ],
            'status_description' => $request->status_description,
            'icon' => $request->icon,
            'color' => $request->color,
            'group_id' => $request->group_id
        ];
        // Select all statuses belonging to a group to check unique constraint
        $statuses = Status::where('group_id', $inputs['group_id'])->get();

        // Validate required fields
        if ($inputs['status_name'] == null) {
            return $this->handleError($inputs['status_name'], __('validation.required'), 400);
        }

        if ($inputs['group_id'] == null OR $inputs['group_id'] == ' ') {
            return $this->handleError($inputs['group_id'], __('validation.required'), 400);
        }

        // Check if status name already exists
        foreach ($statuses as $another_status):
            if ($another_status->status_name == $inputs['status_name']) {
                return $this->handleError($inputs['status_name'], __('validation.custom.status_name.exists'), 400);
            }
        endforeach;

        $status = Status::create($inputs);

        return $this->handleResponse(new ResourcesStatus($status), __('notifications.create_status_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     */
    public function show($id)
    {
        $status = Status::find($id);

        if (is_null($status)) {
            return $this->handleError(__('notifications.find_status_404'));
        }

        return $this->handleResponse(new ResourcesStatus($status), __('notifications.find_status_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Status  $status
     */
    #[BodyParameter('id', description: 'Colonne statuses.id : BIGINT; NOT NULL. Identifiant du corps utilisé par les contrôles existants, distinct du paramètre de route.', required: false, type: 'integer')]
    #[BodyParameter('status_name_en', description: 'Colonne statuses.status_name : JSON; NOT NULL. Traduction en transmise séparément.', required: false, type: 'string')]
    #[BodyParameter('status_name_fr', description: 'Colonne statuses.status_name : JSON; NOT NULL. Traduction fr transmise séparément.', required: false, type: 'string')]
    #[BodyParameter('status_name_ln', description: 'Colonne statuses.status_name : JSON; NOT NULL. Traduction ln transmise séparément.', required: false, type: 'string')]
    #[BodyParameter('status_description', description: 'Colonne statuses.status_description : TEXT; NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('icon', description: 'Colonne statuses.icon : VARCHAR(45); NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('color', description: 'Colonne statuses.color : VARCHAR(45); NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('group_id', description: 'Colonne statuses.group_id : BIGINT; NULL autorisé.', required: false, type: 'integer')]
    public function update(Request $request, Status $status)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'status_name' => [
                'en' => $request->status_name_en,
                'fr' => $request->status_name_fr,
                'ln' => $request->status_name_ln
            ],
            'status_description' => $request->status_description,
            'icon' => $request->icon,
            'color' => $request->color,
            'group_id' => $request->group_id
        ];

        if ($inputs['status_name'] != null) {
            // Select all statuses and specific status to check unique constraint
            $statuses = Status::where('group_id', $inputs['group_id'])->get();
            $current_status = Status::find($inputs['id']);

            foreach ($statuses as $another_status):
                if (($current_status->status_name != $inputs['status_name']) && ($another_status->status_name == $inputs['status_name'])) {
                    return $this->handleError($inputs['status_name'], __('validation.custom.status_name.exists'), 400);
                }
            endforeach;

            $status->update([
                'status_name' => [
                    'en' => $request->status_name_en,
                    'fr' => $request->status_name_fr,
                    'ln' => $request->status_name_ln
                ],
                'updated_at' => now()
            ]);
        }

        foreach (['status_description', 'icon', 'color', 'group_id'] as $field) {
            if ($inputs[$field] != null) {
                $status->update([
                    $field => $inputs[$field],
                    'updated_at' => now()
                ]);
            }
        }

        return $this->handleResponse(new ResourcesStatus($status), __('notifications.update_status_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Status  $status
     */
    public function destroy(Status $status)
    {
        $status->delete();

        $statuses = Status::all();

        return $this->handleResponse(ResourcesStatus::collection($statuses), __('notifications.delete_status_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Search a status by its real name.
     *
     * @param  string $locale
     * @param  string $data
     */
    public function search($locale, $data)
    {
        $status = Status::where('status_name->' . $locale, $data)->first();

        if (is_null($status)) {
            return $this->handleError(__('notifications.find_status_404'));
        }

        return $this->handleResponse(new ResourcesStatus($status), __('notifications.find_status_success'));
    }

    /**
     * Find all type by group.
     *
     * @param  string $group_name
     */
    public function findByGroup($group_name)
    {
        $group = Group::where('group_name', $group_name)->first();

        if (is_null($group)) {
            return $this->handleError(__('notifications.find_group_404'));
        }

        $statuses = Status::where('group_id', $group->id)->get();

        return $this->handleResponse(ResourcesStatus::collection($statuses), __('notifications.find_all_statuses_success'));
    }
}
