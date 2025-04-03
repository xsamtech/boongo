<?php

namespace App\Http\Controllers\API;

use App\Models\Group;
use App\Models\Partner;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Resources\Partner as ResourcesPartner;
use Carbon\Carbon;

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
            'message' => $request->message,
            'website_url' => $request->website_url
        ];
        $partners = Partner::all();

        // Validate required fields
        if ($inputs['name'] == null OR $inputs['name'] == ' ') {
            return $this->handleError($inputs['name'], __('validation.required') . ' (' . __('miscellaneous.admin.partner.data.name') . ') ', 400);
        }

        // Check if partner name already exists
        foreach ($partners as $another_partner):
            if ($another_partner->name == $inputs['name']) {
                return $this->handleError($inputs['name'], __('validation.custom.name.exists'), 400);
            }
        endforeach;

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
            'id' => $request->id,
            'name' => $request->name,
            'message' => $request->message,
            'image_64' => $request->image_64,
            'website_url' => $request->website_url
        ];

        if ($inputs['name'] != null) {
            $partners = Partner::all();
            $current_partner = Partner::find($inputs['id']);

            if (is_null($current_partner)) {
                return $this->handleError(__('notifications.find_partner_404'));
            }
    
            foreach ($partners as $another_partner):
                if ($current_partner->name != $inputs['name']) {
                    if ($another_partner->name == $inputs['name']) {
                        return $this->handleError($inputs['name'], __('validation.custom.name.exists'), 400);
                    }
                }
            endforeach;

            $partner->update([
                'name' => $inputs['name'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['message'] != null) {
            $partner->update([
                'message' => $inputs['message'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['image_64'] != null) {
            $current_partner = Partner::find($inputs['id']);

            if (is_null($current_partner)) {
                return $this->handleError(__('notifications.find_partner_404'));
            }

            // $extension = explode('/', explode(':', substr($inputs['image_64'], 0, strpos($inputs['image_64'], ';')))[1])[1];
            $replace = substr($inputs['image_64'], 0, strpos($inputs['image_64'], ',') + 1);
            // Find substring from replace here eg: data:image/png;base64,
            $image = str_replace($replace, '', $inputs['image_64']);
            $image = str_replace(' ', '+', $image);
            // Create image URL
            $image_url = 'images/partners/' . $current_partner->id . '/' . Str::random(50) . '.png';

            // Upload image
            Storage::url(Storage::disk('public')->put($image_url, base64_decode($image)));

            $partner->update([
                'image_url' => $image_url,
                'updated_at' => now()
            ]);
        }

        if ($inputs['website_url'] != null) {
            $partner->update([
                'website_url' => $inputs['website_url'],
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
     * All partnerships according to status
     *
     * @param  string $locale
     * @param  string $status_name
     * @return \Illuminate\Http\Response
     */
    public function partnershipsByStatus($locale, $status_name)
    {
        $partnership_status_group = Group::where('group_name', 'Etat du partenariat')->first();
        $status = Status::where([['status_name->' . $locale, $status_name], ['group_id', $partnership_status_group->id]])->first();

        if (is_null($status)) {
            return $this->handleError(__('notifications.find_status_404'));
        }

        $partners = Partner::whereHas('categories', function ($query) use ($status) {
                                $query->where('category_partner.status_id', $status->id);
                            })->with(['categories' => function ($query) use ($status) {
                                $query->where('category_partner.status_id', $status->id);
                            }])->get();

        return $this->handleResponse(ResourcesPartner::collection($partners), __('notifications.find_all_partners_success'));
    }

    /**
     * Terminate partnership
     *
     * @param  int $partner_id
     * @return \Illuminate\Http\Response
     */
    public function terminatePartnership($partner_id)
    {
        $partnership_status_group = Group::where('group_name', 'Etat du partenariat')->first();
        $terminated_status = Status::where([['status_name->fr', 'Terminé'], ['group_id', $partnership_status_group->id]])->first();

        if (is_null($terminated_status)) {
            return $this->handleError(__('notifications.find_status_404'));
        }

        $partner = Partner::find($partner_id);

        if (is_null($partner)) {
            return $this->handleError(__('notifications.find_partner_404'));
        }

        // Calculate the remaining days for the partnership
        $remainingDays = $partner->remainingDays(Carbon::now());

        // If the remaining days are 0, we end the partnership
        if ($remainingDays <= 0) {
            // Update all records in the "category_partner" table for this partner
            $partner->categories()->updateExistingPivot($partner->categories()->pluck('id')->toArray(), [
                'status_id' => $terminated_status->id
            ]);

            return $this->handleResponse(new ResourcesPartner($partner), __('notifications.partnership_terminated'));

        // If days remaining are > 0, return a message indicating that it is not yet finished
        } else {
            return $this->handleError(__('notifications.partnership_still_active', ['remainingDays' => $remainingDays]));
        }
    }
}
