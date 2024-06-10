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

    // ==================================== HTTP POST METHODS ====================================
    /**
     * GET: Welcome/Home page
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Support\Facades\Redirect
     */
    public function addWork(Request $request)
    {
        $api_token = '1|fjhakjU33XG5KPJ9HnGmw4a90rhlpvi2xM06alhkf5a69ecc';
        // Find status by name
        $relevant_status_name = 'Pertinente';
        $relevant_status = $this::$api_client_manager::call('GET', getApiURL() . '/status/search/fr/' . $relevant_status_name);
        // Find type by name
        $document_type_name = 'Document';
        $document_type = $this::$api_client_manager::call('GET', getApiURL() . '/type/search/fr/' . $document_type_name);
        // User inputs
        $inputs = [
            'work_title' => $request->register_work_title,
            'work_content' => $request->register_work_content,
            'work_url' => $request->register_work_url,
            'type_id' => $request->type_id,
            'status_id' => $relevant_status->data->id,
            'categories_ids' => $request->register_categories_ids,
        ];
        // Add an admin
        $work = $this::$api_client_manager::call('POST', getApiURL() . '/work', $api_token, $inputs);

        if ($work->success AND $relevant_status->success AND $document_type->success) {
            // Udpate avatar if it is changed
            if ($request->data_other_user != null) {
                $this::$api_client_manager::call('PUT', getApiURL() . '/work/add_image/' . $work->data->id, $api_token, [
                    'work_id' => $work->data->id,
                    'image_64' => $request->data_other_user
                ]);
            }

            // Upload document
            if ($request->register_document != null) {
                $this::$api_client_manager::call('POST', getApiURL() . '/work/upload_files', $api_token, [
                    'type_id' => $document_type->data->id,
                    'work_id' => $work->data->id,
                    'image_64' => $request->data_other_user
                ]);
            }

            return Redirect::back()->with('success_message', __('notifications.registered_data'));

        } else {
            $resp_error = $inputs['work_title'] . '~' . $inputs['work_content'] . '~' . $inputs['work_url'] . '~' . (!empty($work->message) ? $work->message : (!empty($relevant_status->message) ? $relevant_status->message : (!empty($document_type->message) ? $document_type->message : 'ERROR')));

            return Redirect::back()->with('error_message', $resp_error);
        }
    }
}
