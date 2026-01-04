<?php

namespace App\Http\Controllers\Web;

use App\Models\File;
use App\Models\Type;
use App\Models\Work;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Controllers\ApiClientManager;
use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class ManagerController extends Controller
{
    public static $api_client_manager;

    public function __construct()
    {
        $this::$api_client_manager = new ApiClientManager();
        $this->middleware('auth');
    }

    // ==================================== HTTP GET METHODS ====================================
    /**
     * GET: Partners page
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function dashboard(Request $request)
    {

        return view('dashboard');
    }

    // ==================================== HTTP POST METHODS ====================================

}
