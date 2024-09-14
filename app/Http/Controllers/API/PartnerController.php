<?php

namespace App\Http\Controllers\API;

use App\Models\Partner;
use Illuminate\Http\Request;
use App\Http\Resources\Partner as ResourcesPartner;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class PartnerController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $partners = Partner::all();

        return $this->handleResponse(ResourcesPartner::collection($partners), __('notifications.find_all_partners_success'));
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
            'name' => $request->name,
            'image_url' => $request->image_url,
            'is_active' => 1
        ];
        // Select all partners to make them inactive
        $partners = Partner::all();

        // Validate required fields
        if ($inputs['image_url'] == null OR $inputs['image_url'] == ' ') {
            return $this->handleError($inputs['image_url'], __('validation.required'), 400);
        }

        foreach ($partners as $another_partner):
            $another_partner->update([
                'is_active' => 0,
                'updated_at' => now(),
            ]);
        endforeach;

        $partner = Partner::create($inputs);

        return $this->handleResponse(new ResourcesPartner($partner), __('notifications.create_partner_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $partner = Partner::find($id);

        if (is_null($partner)) {
            return $this->handleError(__('notifications.find_partner_404'));
        }

        return $this->handleResponse(new ResourcesPartner($partner), __('notifications.find_partner_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Partner  $partner
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Partner $partner)
    {
        // Get inputs
        $inputs = [
            'name' => $request->name,
            'image_url' => $request->image_url,
            'is_active' => $request->is_active
        ];

        if ($inputs['name'] != null) {
            $partner->update([
                'name' => $inputs['name'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['image_url'] != null) {
            $partner->update([
                'image_url' => $inputs['image_url'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['is_active'] != null) {
            $partner->update([
                'is_active' => $inputs['is_active'],
                'updated_at' => now(),
            ]);
        }

        return $this->handleResponse(new ResourcesPartner($partner), __('notifications.update_partner_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Partner  $partner
     * @return \Illuminate\Http\Response
     */
    public function destroy(Partner $partner)
    {
        $partner->delete();

        $partners = Partner::all();

        return $this->handleResponse(ResourcesPartner::collection($partners), __('notifications.delete_partner_success'));
    }
}
