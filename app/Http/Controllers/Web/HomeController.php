<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\ApiClientManager;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class HomeController extends Controller
{
    public static $api_client_manager;

    public function __construct()
    {
        $this::$api_client_manager = new ApiClientManager();
    }

    // ==================================== HTTP GET METHODS ====================================
    /**
     * GET: Change language
     *
     * @param  $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changeLanguage($locale)
    {
        app()->setLocale($locale);
        session()->put('locale', $locale);

        return redirect()->back();
    }

    /**
     * GET: Welcome/Home page
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Group names
        $work_type_group = 'Type d\'œuvre';
        // All types by group
        $types_by_group = $this::$api_client_manager::call('GET', getApiURL() . '/type/find_by_group/' . $work_type_group);
        // All categories by group
        $categories = $this::$api_client_manager::call('GET', getApiURL() . '/category');

        return view('form-test', [
            'types' => $types_by_group->data,
            'categories' => $categories->data
        ]);
    }

    // ==================================== HTTP POST METHODS ====================================

}
