<?php

namespace App\Http\Controllers\API;

use App\Models\Group;
use App\Models\Status;
use Illuminate\Http\Request;
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
     * @return \Illuminate\Http\Response
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
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Get inputs
        $inputs = [
            'status_name' => [
                'en' => $request->status_name_en,
                'fr' => $request->status_name_fr
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
     * @return \Illuminate\Http\Response
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
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Status $status)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'status_name' => [
                'en' => $request->status_name_en,
                'fr' => $request->status_name_fr
            ],
            'status_description' => $request->status_description,
            'icon' => $request->icon,
            'color' => $request->color,
            'group_id' => $request->group_id
        ];
        // Select all statuses and specific status to check unique constraint
        $statuses = Status::where('group_id', $inputs['group_id'])->get();
        $current_status = Status::find($inputs['id']);

        if ($inputs['status_name'] != null) {
            foreach ($statuses as $another_status):
                if ($current_status->status_name != $inputs['status_name']) {
                    if ($another_status->status_name == $inputs['status_name']) {
                        return $this->handleError($inputs['status_name'], __('validation.custom.status_name.exists'), 400);
                    }
                }
            endforeach;

            $status->update([
                'status_name' => [
                    'en' => $request->status_name_en,
                    'fr' => $request->status_name_fr
                ],
                'updated_at' => now()
            ]);
        }

        if ($inputs['status_description'] != null) {
            $status->update([
                'status_description' => $request->status_description,
                'updated_at' => now(),
            ]);
        }

        if ($inputs['icon'] != null) {
            $status->update([
                'icon' => $request->icon,
                'updated_at' => now(),
            ]);
        }

        if ($inputs['color'] != null) {
            $status->update([
                'color' => $request->color,
                'updated_at' => now(),
            ]);
        }

        if ($inputs['group_id'] != null) {
            $status->update([
                'group_id' => $request->group_id,
                'updated_at' => now(),
            ]);
        }

        return $this->handleResponse(new ResourcesStatus($status), __('notifications.update_status_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Status  $status
     * @return \Illuminate\Http\Response
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
     * @return \Illuminate\Http\Response
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
     * @return \Illuminate\Http\Response
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
