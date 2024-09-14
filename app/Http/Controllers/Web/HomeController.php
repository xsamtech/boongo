<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\ApiClientManager;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

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
        if ($request->has('type')) {
            if ($request->get('type') == 'empty') {
                return redirect('/');
            }

            // Group names
            $work_type_group = 'Type d\'œuvre';
            // All types by group
            $types_by_group = $this::$api_client_manager::call('GET', getApiURL() . '/type/find_by_group/' . $work_type_group);
            // All categories by group
            $categories = $this::$api_client_manager::call('GET', getApiURL() . '/category');
            $works = $this::$api_client_manager::call('GET', getApiURL()  . '/work/find_all_by_type/fr/' . $request->get('type') . ($request->has('page') ? '?page=' . $request->get('page') : ''));

            if ($works->success) {
                return view('work-test', [
                    'types' => $types_by_group->data,
                    'categories' => $categories->data,
                    'works' => $works->data,
                    'lastPage' => $works->lastPage,
                ]);

            } else {
                $all_works = $this::$api_client_manager::call('GET', getApiURL()  . '/work' . ($request->has('page') ? '?page=' . $request->get('page') : ''));

                return view('work-test', [
                    'types' => $types_by_group->data,
                    'categories' => $categories->data,
                    'works' => $all_works->data,
                    'lastPage' => $all_works->lastPage,
                ]);
            }

        } else {
            // Group names
            $work_type_group = 'Type d\'œuvre';
            // All types by group
            $types_by_group = $this::$api_client_manager::call('GET', getApiURL() . '/type/find_by_group/' . $work_type_group);
            // All categories by group
            $categories = $this::$api_client_manager::call('GET', getApiURL() . '/category');
            $works = $this::$api_client_manager::call('GET', getApiURL()  . '/work' . ($request->has('page') ? '?page=' . $request->get('page') : ''));

            return view('work-test', [
                'types' => $types_by_group->data,
                'categories' => $categories->data,
                'works' => $works->data,
                'lastPage' => $works->lastPage,
            ]);
        }
    }

    /**
     * GET: About page
     *
     * @return \Illuminate\View\View
     */
    public function about()
    {
        $titles = Lang::get('miscellaneous.public.about.content.titles');

        return view('about', ['titles' => $titles]);
    }

    /**
     * GET: About, inner pages
     *
     * @return \Illuminate\View\View
     */
    public function aboutEntity($entity)
    {
        $titles = Lang::get('miscellaneous.public.about.' . $entity . '.titles');

        return view('about', [
            'titles' => $titles,
            'entity' => $entity,
            'entity_title' => __('miscellaneous.public.about.' . $entity . '.title'),
            'entity_menu' => __('miscellaneous.menu.' . $entity),
        ]);
    }

    // ==================================== HTTP POST METHODS ====================================

}
