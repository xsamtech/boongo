<?php

namespace App\Http\Controllers\API;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Resources\Organization as ResourcesOrganization;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class OrganizationController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $organizations = Organization::orderByDesc('created_at')->get();

        return $this->handleResponse(ResourcesOrganization::collection($organizations), __('notifications.find_all_organizations_success'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Get inputs
        $inputs = [
            'org_name' => $request->org_name,
            'org_description' => $request->org_description,
            'id_number' => $request->id_number,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'p_o_box' => $request->p_o_box,
            'legal_status' => $request->legal_status,
            'year_of_creation' => $request->year_of_creation,
            'website_url' => $request->website_url,
            'type_id' => $request->type_id,
            'status_id' => $request->status_id,
            'user_id' => $request->user_id
        ];
        $organizations = Organization::all();

        // Validate required fields
        if ($inputs['org_name'] == null OR $inputs['org_name'] == ' ') {
            return $this->handleError($inputs['org_name'], __('validation.required') . ' (' . __('miscellaneous.admin.organization.data.org_name') . ') ', 400);
        }

        // Check if organization name already exists
        foreach ($organizations as $another_organization):
            if ($another_organization->org_name == $inputs['org_name']) {
                return $this->handleError($inputs['org_name'], __('validation.custom.name.exists'), 400);
            }
        endforeach;

        $organization = Organization::create($inputs);

        if ($request->image_64 != null) {
            // $extension = explode('/', explode(':', substr($request->image_64, 0, strpos($request->image_64, ';')))[1])[1];
            $replace = substr($request->image_64, 0, strpos($request->image_64, ',') + 1);
            // Find substring from replace here eg: data:image/png;base64,
            $image = str_replace($replace, '', $request->image_64);
            $image = str_replace(' ', '+', $image);
            // Create image URL
            $image_url = 'images/organizations/' . $organization->id . '/' . Str::random(50) . '.png';

            // Upload image
            Storage::url(Storage::disk('public')->put($image_url, base64_decode($image)));

            $organization->update([
                'cover_url' => $image_url,
                'updated_at' => now()
            ]);
        }

        return $this->handleResponse(new ResourcesOrganization($organization), __('notifications.create_organization_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $organization = Organization::find($id);

        if (is_null($organization)) {
            return $this->handleError(__('notifications.find_organization_404'));
        }

        return $this->handleResponse(new ResourcesOrganization($organization), __('notifications.find_organization_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Organization  $organization
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Organization $organization)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'org_name' => $request->org_name,
            'org_description' => $request->org_description,
            'id_number' => $request->id_number,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'p_o_box' => $request->p_o_box,
            'legal_status' => $request->legal_status,
            'year_of_creation' => $request->year_of_creation,
            'website_url' => $request->website_url,
            'type_id' => $request->type_id,
            'status_id' => $request->status_id,
            'user_id' => $request->user_id
        ];

        if ($inputs['org_name'] != null) {
            $organizations = Organization::all();
            $current_organization = Organization::find($inputs['id']);

            if (is_null($current_organization)) {
                return $this->handleError(__('notifications.find_organization_404'));
            }
    
            foreach ($organizations as $another_organization):
                if ($current_organization->org_name != $inputs['org_name']) {
                    if ($another_organization->org_name == $inputs['org_name']) {
                        return $this->handleError($inputs['org_name'], __('validation.custom.name.exists'), 400);
                    }
                }
            endforeach;

            $organization->update([
                'org_name' => $inputs['org_name'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['org_description'] != null) {
            $organization->update([
                'org_description' => $inputs['org_description'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['id_number'] != null) {
            $organization->update([
                'id_number' => $inputs['id_number'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['phone'] != null) {
            $organization->update([
                'phone' => $inputs['phone'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['email'] != null) {
            $organization->update([
                'email' => $inputs['email'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['address'] != null) {
            $organization->update([
                'address' => $inputs['address'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['p_o_box'] != null) {
            $organization->update([
                'p_o_box' => $inputs['p_o_box'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['legal_status'] != null) {
            $organization->update([
                'legal_status' => $inputs['legal_status'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['year_of_creation'] != null) {
            $organization->update([
                'year_of_creation' => $inputs['year_of_creation'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['website_url'] != null) {
            $organization->update([
                'website_url' => $inputs['website_url'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['image_64'] != null) {
            $current_organization = Organization::find($inputs['id']);

            if (is_null($current_organization)) {
                return $this->handleError(__('notifications.find_organization_404'));
            }

            // $extension = explode('/', explode(':', substr($request->image_64, 0, strpos($request->image_64, ';')))[1])[1];
            $replace = substr($request->image_64, 0, strpos($request->image_64, ',') + 1);
            // Find substring from replace here eg: data:image/png;base64,
            $image = str_replace($replace, '', $request->image_64);
            $image = str_replace(' ', '+', $image);
            // Create image URL
            $image_url = 'images/organizations/' . $current_organization->id . '/' . Str::random(50) . '.png';

            // Upload image
            Storage::url(Storage::disk('public')->put($image_url, base64_decode($image)));

            $organization->update([
                'cover_url' => $image_url,
                'updated_at' => now()
            ]);
        }

        return $this->handleResponse(new ResourcesOrganization($organization), __('notifications.update_organization_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Organization  $organization
     * @return \Illuminate\Http\Response
     */
    public function destroy(Organization $organization)
    {
        $organization->delete();

        $organizations = Organization::all();

        return $this->handleResponse(ResourcesOrganization::collection($organizations), __('notifications.delete_organization_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Search (by filtering or not) an organization
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request, $data)
    {
        $query = Organization::query();

        // Apply the filter to the organization name
        $query->where('org_name', 'LIKE', '%' . $data . '%');

        // Add dynamic conditions
        $query->when($request->type_id, function ($query) use ($request) {
            return $query->where('type_id', $request->type_id);
        });

        $query->when($request->status_id, function ($query) use ($request) {
            return $query->where('status_id', $request->status_id);
        });

        $query->when($request->user_id, function ($query) use ($request) {
            return $query->where('user_id', $request->user_id);
        });

        // Retrieves the query results
        $organizations = $query->get();

        return $this->handleResponse(ResourcesOrganization::collection($organizations), __('notifications.find_all_organizations_success'));
    }

    /**
     * Retrieves all organizations belonging to a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $user_id
     * @return \Illuminate\Http\Response
     */
    public function findAllByOwner(Request $request, $user_id)
    {
        // Get the user
        $user = User::find($user_id);

        if (is_null($user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        $query = Organization::query();

        $query->where('user_id', $user->id);

        // Add dynamic conditions
        $query->when($request->type_id, function ($query) use ($request) {
            return $query->where('type_id', $request->type_id);
        });

        $organizations = $query->get();

        return $this->handleResponse(ResourcesOrganization::collection($organizations), __('notifications.find_all_organizations_success'));
    }
}
