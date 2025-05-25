<?php

namespace App\Http\Controllers\Web;

use App\Models\File;
use App\Models\Type;
use App\Models\Work;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function work(Request $request)
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
            // User by "username"
            $user_profile = $this::$api_client_manager::call('GET', getApiURL() . '/user/profile/xanderssamoth');
            // Group names
            $work_type_group = 'Type d\'œuvre';
            // All types by group
            $types_by_group = $this::$api_client_manager::call('GET', getApiURL() . '/type/find_by_group/' . $work_type_group);
            // All categories by group
            $categories = $this::$api_client_manager::call('GET', getApiURL() . '/category', $user_profile->data->api_token);
            $works = $this::$api_client_manager::call('GET', getApiURL()  . '/work' . ($request->has('page') ? '?page=' . $request->get('page') : ''), $user_profile->data->api_token);

            return view('work-test', [
                'types' => $types_by_group->data,
                'categories' => $categories->data,
                'works' => $works->data,
                'lastPage' => $works->lastPage,
            ]);
        }
    }

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
        // Get inputs
        $inputs = [
            'work_title' => $request->work_title,
            'work_content' => $request->work_content,
            'work_url' => $request->work_url,
            'video_source' => $request->video_source,
            'media_length' => $request->media_length,
            'is_public' => $request->is_public,
            'type_id' => $request->type_id,
            'status_id' => $request->status_id,
            'user_id' => $request->user_id
        ];

        // Validate required fields
        if ($inputs['work_title'] == null) {
            return Redirect::back()->with('error_message', __('validation.custom.title.required'));
        }

        if ($inputs['type_id'] == null) {
            return Redirect::back()->with('error_message', __('validation.custom.type_name.required'));
        }

        $work = Work::create($inputs);

        if ($request->categories_ids != null) {
            $work->categories()->sync($request->categories_ids);
        }

        if ($request->image_64 != null) {
            if ($request->image_type_id == null) {
                return Redirect::back()->with('error_message', __('validation.required') . ': "image_type_id"');
            }

            // $extension = explode('/', explode(':', substr($request->image_64, 0, strpos($request->image_64, ';')))[1])[1];
            $replace = substr($request->image_64, 0, strpos($request->image_64, ',') + 1);
            // Find substring from replace here eg: data:image/png;base64,
            $image = str_replace($replace, '', $request->image_64);
            $image = str_replace(' ', '+', $image);
            // Create image URL
            $image_url = 'images/works/' . $work->id . '/' . Str::random(50) . '.png';

            // Upload image
            try {
                Storage::url(Storage::disk('s3')->put($image_url, base64_decode($image)));

            } catch (\Throwable $th) {
                return $this->handleError($th, __('notifications.create_image64_500'), 500);
            }

            File::create([
                'file_name' => trim($request->image_name) != null ? $request->image_name : $work->work_title,
                'file_url' => config('filesystems.disks.s3.url') . $image_url,
                'type_id' => $request->image_type_id,
                'work_id' => $work->id
            ]);
        }

        if ($request->hasFile('video_file_url')) {
            $user_profile = $this::$api_client_manager::call('GET', getApiURL() . '/user/profile/xanderssamoth');

            $this::$api_client_manager::call('POST', getApiURL() . '/work/upload_files', $user_profile->data->api_token, ['document_file_type_id' => 6, 'video_file_url' => $request->file('video_file_url')]);
        }

        if ($request->hasFile('document_file_url')) {
            $user_profile = $this::$api_client_manager::call('GET', getApiURL() . '/user/profile/xanderssamoth');

            $this::$api_client_manager::call('POST', getApiURL() . '/work/upload_files', $user_profile->data->api_token, ['document_file_type_id' => 7, 'document_file_url' => $request->file('document_file_url')]);
        }

        if ($request->hasFile('audio_file_url')) {
            $user_profile = $this::$api_client_manager::call('GET', getApiURL() . '/user/profile/xanderssamoth');

            $this::$api_client_manager::call('POST', getApiURL() . '/work/upload_files', $user_profile->data->api_token, ['document_file_type_id' => 8, 'audio_file_url' => $request->file('audio_file_url')]);
        }

        return Redirect::back()->with('success_message', __('notifications.create_work_success'));
    }
}
