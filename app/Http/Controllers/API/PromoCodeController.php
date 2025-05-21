<?php

namespace App\Http\Controllers\API;

use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\PromoCode as ResourcesPromoCode;
use App\Models\Group;
use App\Models\Partner;
use App\Models\Status;
use App\Models\User;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class PromoCodeController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $promo_codes = PromoCode::orderByDesc('created_at')->get();

        return $this->handleResponse(ResourcesPromoCode::collection($promo_codes), __('notifications.find_all_promo_codes_success'));
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
            'for_partner_id' => $request->for_partner_id,
            'code' => $request->code,
            'is_active' => isset($request->is_active) ? $request->is_active : 1,
            'user_id' => $request->user_id
        ];

        $validator = Validator::make($inputs, [
            'code' => ['required'],
            'user_id' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());       
        }

        $promo_code = PromoCode::create($inputs);

        return $this->handleResponse(new ResourcesPromoCode($promo_code), __('notifications.create_promo_code_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $promo_code = PromoCode::find($id);

        if (is_null($promo_code)) {
            return $this->handleError(__('notifications.find_promo_code_404'));
        }

        return $this->handleResponse(new ResourcesPromoCode($promo_code), __('notifications.find_promo_code_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PromoCode  $promo_code
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PromoCode $promo_code)
    {
        // Get inputs
        $inputs = [
            'for_partner_id' => $request->for_partner_id,
            'code' => $request->code,
            'is_active' => $request->is_active,
            'user_id' => $request->user_id
        ];

        if ($inputs['for_partner_id'] != null) {
            $promo_code->update([
                'for_partner_id' => $inputs['for_partner_id'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['code'] != null) {
            $promo_code->update([
                'code' => $inputs['code'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['is_active'] != null) {
            $promo_code->update([
                'is_active' => $inputs['is_active'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['user_id'] != null) {
            $promo_code->update([
                'user_id' => $inputs['user_id'],
                'updated_at' => now(),
            ]);
        }

        return $this->handleResponse(new ResourcesPromoCode($promo_code), __('notifications.update_promo_code_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PromoCode  $promo_code
     * @return \Illuminate\Http\Response
     */
    public function destroy(PromoCode $promo_code)
    {
        $promo_code->delete();

        $promo_codes = PromoCode::all();

        return $this->handleResponse(ResourcesPromoCode::collection($promo_codes), __('notifications.delete_promo_code_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Check if user is subscribed
     *
     * @param  int $user_id
     * @param  string $code
     * @param  string $for_partner_id
     * @return \Illuminate\Http\Response
     */
    public function activateSubscription($user_id, $code, $for_partner_id)
    {
        // $promo_code = PromoCode::find($id);

        // Group
        $partnership_status_group = Group::where('group_name', 'Etat du partenariat')->first();
        // Status
        $active_status = Status::where([['status_name->fr', 'Actif'], ['group_id', $partnership_status_group->id]])->first();
        // Request
        $user = User::find($user_id);

        if (is_null($user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        $partner = Partner::find($for_partner_id);

        if (is_null($partner)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        $promo_code_exists = $partner->categories()->wherePivot([['promo_code', $code], ['status_id', $active_status->id]])->exists();

        if ($promo_code_exists) {
            $terminated_status = Status::where([['status_name->fr', 'Terminé'], ['group_id', $partnership_status_group->id]])->first();

            // Register user promo code
            $promo_code = PromoCode::create([
                'for_partner_id' => $partner->id,
                'code' => $code,
                'is_active' => 1,
                'user_id' => $user->id
            ]);

            // Terminate partnership with the promo code
            $partner->categories()->updateExistingPivot($partner->categories()->pluck('id')->toArray(), [
                'status_id' => $terminated_status->id
            ]);

            return $this->handleResponse(new ResourcesPromoCode($promo_code), __('notifications.create_promo_code_success'));

        } else {
            return $this->handleError(__('notifications.find_promo_code_404'));
        }
    }
}
