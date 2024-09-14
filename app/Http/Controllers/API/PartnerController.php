<?php

namespace App\Http\Controllers\API;

use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
        $partners = Partner::orderByDesc('created_at')->get();

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
            'is_active' => 1
        ];
        // Select all partners to make them inactive
        $partners = Partner::where('is_active', 1)->get();

        // Validate required fields
        if ($inputs['name'] == null OR $inputs['name'] == ' ') {
            return $this->handleError($inputs['name'], __('validation.required'), 400);
        }

        if ($partners != null) {
            foreach ($partners as $another_partner):
                $another_partner->update([
                    'is_active' => 0,
                    'updated_at' => now(),
                ]);
            endforeach;
        }

        $partner = Partner::create($inputs);

        if ($request->image_64 != null) {
            // $extension = explode('/', explode(':', substr($request->image_64, 0, strpos($request->image_64, ';')))[1])[1];
            $replace = substr($request->image_64, 0, strpos($request->image_64, ',') + 1);
            // Find substring from replace here eg: data:image/png;base64,
            $image = str_replace($replace, '', $request->image_64);
            $image = str_replace(' ', '+', $image);
            // Create image URL
            $image_url = 'images/partners/' . $partner->id . '/' . Str::random(50) . '.png';

            // Upload image
            Storage::url(Storage::disk('public')->put($image_url, base64_decode($image)));

            $partner->update([
                'image_url' => $image_url,
                'updated_at' => now()
            ]);
        }

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

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Search a partner
     *
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function search($data)
    {
        $partners = Partner::where('name', 'LIKE', '%' . $data . '%')->get();

        return $this->handleResponse(ResourcesPartner::collection($partners), __('notifications.find_all_partners_success'));
    }

    /**
     * Find by (in)active
     *
     * @param  int $is_active
     * @return \Illuminate\Http\Response
     */
    public function findByActive($is_active)
    {
        $partners = Partner::where('is_active', $is_active)->get();

        return $this->handleResponse(ResourcesPartner::collection($partners), __('notifications.find_all_partners_success'));
    }
}
