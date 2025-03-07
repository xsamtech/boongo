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

        if ($request->hasFile('file_url')) {
            if ($request->file_type_id == null) {
                return Redirect::back()->with('error_message', __('validation.required') . ': "file_type_id"');
            }

            $type = Type::find($request->file_type_id);

            if (is_null($type)) {
                return Redirect::back()->with('error_message', __('notifications.find_type_404'));
            }

            $file_url = ($request->file_type_id == 7 ? 'documents/works/' : ($request->file_type_id == 8 ? 'audios/works/' : 'images/works/')) . $work->id . '/' . Str::random(50) . '.' . $request->file('file_url')->extension();

            // Upload file
            $dir_result = Storage::url(Storage::disk('public')->put($file_url, $request->file('file_url')));

            File::create([
                'file_name' => trim($request->file_name) != null ? $request->file_name : $work->work_title,
                'file_url' => $dir_result,
                'type_id' => $request->file_type_id,
                'work_id' => $work->id
            ]);
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
            Storage::url(Storage::disk('public')->put($image_url, base64_decode($image)));

            File::create([
                'file_name' => trim($request->image_name) != null ? $request->image_name : $work->work_title,
                'file_url' => '/storage/' . $image_url,
                'type_id' => $request->image_type_id,
                'work_id' => $work->id
            ]);
        }

        return Redirect::back()->with('success_message', $work->message);
    }
}
