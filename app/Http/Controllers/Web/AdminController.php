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
        // Prepare the data to send
        $data = [
            ['name' => 'work_title', 'contents' => $request->work_title],
            ['name' => 'work_content', 'contents' => $request->work_content],
            ['name' => 'work_url', 'contents' => $request->work_url],
            ['name' => 'type_id', 'contents' => $request->type_id],
            ['name' => 'status_id', 'contents' => $request->status_id],
            ['name' => 'user_id', 'contents' => $request->user_id],
            ['name' => 'file_type_id', 'contents' => $request->file_type_id],
            ['name' => 'image_type_id', 'contents' => $request->image_type_id],
            ['name' => 'image_64', 'contents' => $request->image_64],
        ];

        // Add the file if present
        if ($request->hasFile('file_url')) {
            $data[] = [
                'name' => 'file_url',
                'contents' => fopen($request->file('file_url')->getPathname(), 'r'),
                'filename' => $request->file('file_url')->getClientOriginalName()
            ];
        }

        $work = $this::$api_client_manager::call('POST', getApiURL() . '/work', null, [
            'multipart' => $data,
        ]);

        if ($work->success) {
            return Redirect::back()->with('success_message', $work->message);

        } else {
            return Redirect::back()->with('error_message', $work->message);
        }
    }
}
