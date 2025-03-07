<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\ApiClientManager;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class AdminController extends Controller
{
    public static $api_client_manager;

    public function __construct()
    {
        $this::$api_client_manager = new ApiClientManager();
    }

    // ==================================== HTTP GET METHODS ====================================
    /**
     * GET: Partners page
     *
     * @return \Illuminate\View\View
     */
    public function partners()
    {
        $partners = $this::$api_client_manager::call('GET', getApiURL() . '/partner');

        return view('partner-test', [
            'partners' => $partners->data,
        ]);
    }

    /**
     * GET: Partner datas
     *
     * @param  $id
     * @return \Illuminate\View\View
     */
    public function partnersDatas($id)
    {
        $partner = $this::$api_client_manager::call('GET', getApiURL() . '/partner/' . $id);

        return view('partner-test', [
            'partner' => $partner->data,
        ]);
    }

    // ==================================== HTTP POST METHODS ====================================
    /**
     * Store a new work.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Support\Facades\Redirect
     */
    public function addWork(Request $request)
    {
        // Add a work
        $work = $this::$api_client_manager::call('POST', getApiURL() . '/work', null, [
            'work_title' => $request->work_title,
            'work_content' => $request->work_content,
            'work_url' => $request->work_url,
            'type_id' => $request->type_id,
            'status_id' => $request->status_id,
            'user_id' => $request->user_id,
            'file_type_id' => $request->file_type_id,
            'file_type_id' => $request->file('file_url'),
            'image_type_id' => $request->image_type_id,
            'image_64' => $request->image_64,
        ]);

        if ($work->success) {
            return Redirect::back()->with('success_message', $work->message);

        } else {
            return Redirect::back()->with('error_message', $work->message);
        }
    }
}
