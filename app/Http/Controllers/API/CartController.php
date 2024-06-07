<?php

namespace App\Http\Controllers\API;

use App\Models\Cart;
use App\Models\Status;
use App\Models\User;
use App\Models\Work;
use Illuminate\Http\Request;
use App\Http\Resources\Cart as ResourcesCart;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class CartController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $carts = Cart::all();

        return $this->handleResponse(ResourcesCart::collection($carts), __('notifications.find_all_carts_success'));
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
            'payment_code' => $request->payment_code,
            'status_id' => $request->status_id,
            'user_id' => $request->user_id,
            'payment_id' => $request->payment_id
        ];
        // Select all carts to check unique constraint
        $carts = Cart::all();

        // Validate required fields
        if (trim($inputs['user_id']) == null) {
            return $this->handleError($inputs['user_id'], __('validation.required'), 400);
        }

        // Check if cart payment code already exists
        foreach ($carts as $another_book):
            if ($another_book->payment_code == $inputs['payment_code']) {
                return $this->handleError($inputs['payment_code'], __('validation.custom.code.exists'), 400);
            }
        endforeach;

        $cart = Cart::create($inputs);

        return $this->handleResponse(new ResourcesCart($cart), __('notifications.create_cart_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $cart = Cart::find($id);

        if (is_null($cart)) {
            return $this->handleError(__('notifications.find_cart_404'));
        }

        return $this->handleResponse(new ResourcesCart($cart), __('notifications.find_cart_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Cart  $cart
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Cart $cart)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'payment_code' => $request->payment_code,
            'status_id' => $request->status_id,
            'user_id' => $request->user_id,
            'payment_id' => $request->payment_id
        ];
        if ($inputs['payment_code'] != null) {
            // Select all carts to check unique constraint
            $carts = Cart::all();
            $current_cart = Cart::find($inputs['id']);

            foreach ($carts as $another_cart):
                if ($current_cart->payment_code != $inputs['payment_code']) {
                    if ($another_cart->payment_code == $inputs['payment_code']) {
                        return $this->handleError($inputs['payment_code'], __('validation.custom.code.exists'), 400);
                    }
                }
            endforeach;

            $cart->update([
                'payment_code' => $inputs['payment_code'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['status_id'] != null) {
            $cart->update([
                'status_id' => $inputs['status_id'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['user_id'] != null) {
            $cart->update([
                'user_id' => $inputs['user_id'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['payment_id'] != null) {
            $cart->update([
                'payment_id' => $inputs['payment_id'],
                'updated_at' => now(),
            ]);
        }

        return $this->handleResponse(new ResourcesCart($cart), __('notifications.update_cart_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Cart  $cart
     * @return \Illuminate\Http\Response
     */
    public function destroy(Cart $cart)
    {
        $cart->delete();

        $carts = Cart::all();

        return $this->handleResponse(ResourcesCart::collection($carts), __('notifications.delete_cart_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Check if work is in cart
     *
     * @param  int $work_id
     * @param  int $user_id
     * @return \Illuminate\Http\Response
     */
    public function isInside($work_id, $user_id)
    {
        $work = Work::find($work_id);

        if (is_null($work)) {
            return $this->handleError(__('notifications.work_404'));
        }

        $user = User::find($user_id);

        if (is_null($user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        $status = Status::where('status_name->fr', 'En cours')->first();

        if (is_null($status)) {
            return $this->handleError(__('notifications.find_status_404'));
        }

        $hasPivot = Cart::where([['status_id', $status->id], ['user_id', $user->id]])->whereHas('works', function ($q) use ($work) {
                        $q->where('works.id', $work->id);
                    })->exists();

        if ($hasPivot) {
            return $this->handleResponse(1, __('notifications.find_work_success'), null);

        } else {
            return $this->handleResponse(0, __('notifications.find_work_404'), null);
        }
    }

    /**
     * Add work to cart.
     *
     * @param  int $work_id
     * @param  int $user_id
     * @return \Illuminate\Http\Response
     */
    public function addToCart($work_id, $user_id)
    {
        $work = Work::find($work_id);

        if (is_null($work)) {
            return $this->handleError(__('notifications.find_work_404'));
        }

        $user = User::find($user_id);

        if (is_null($user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        $status = Status::where('status_name->fr', 'En cours')->first();

        if (is_null($status)) {
            return $this->handleError(__('notifications.find_status_404'));
        }

        $cart = Cart::where([['status_id', $status->id], ['user_id', $user->id]])->first();

        if ($cart != null) {

            if (count($cart->works) > 0) {
                $cart->works()->syncWithoutDetaching([$work->id]);

            } else {
                $cart->works()->attach([$work->id]);
            }

            return $this->handleResponse(new ResourcesCart($cart), __('notifications.find_cart_success'));

        } else {
            $cart = Cart::create([
                'status_id' => $status->id,
                'user_id' => $user->id
            ]);

            $cart->works()->attach([$work->id]);

            return $this->handleResponse(new ResourcesCart($cart), __('notifications.find_cart_success'));
        }
    }

    /**
     * Remove work from cart.
     *
     * @param  int $work_id
     * @param  int $cart_id
     * @return \Illuminate\Http\Response
     */
    public function removeFromCart($work_id, $cart_id)
    {
        $work = Work::find($work_id);

        if (is_null($work)) {
            return $this->handleError(__('notifications.find_work_404'));
        }

        $cart = Cart::find($cart_id);

        if (is_null($cart)) {
            return $this->handleError(__('notifications.find_cart_404'));
        }

        $cart->works()->detach($work->id);

        return $this->handleResponse(new ResourcesCart($cart), __('notifications.delete_media_success'));
    }
}
