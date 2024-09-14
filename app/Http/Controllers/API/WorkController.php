<?php

namespace App\Http\Controllers\API;

use App\Models\File;
use App\Models\Notification;
use App\Models\Session;
use App\Models\Status;
use App\Models\Type;
use App\Models\User;
use App\Models\Work;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Resources\Session as ResourcesSession;
use App\Http\Resources\Work as ResourcesWork;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class WorkController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $works = Work::orderByDesc('created_at')->paginate(12);
        $count_works = Work::count();

        return $this->handleResponse(ResourcesWork::collection($works), __('notifications.find_all_works_success'), $works->lastPage(), $count_works);
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
            'work_title' => $request->work_title,
            'work_content' => $request->work_content,
            'work_url' => $request->work_url,
            'type_id' => $request->type_id,
            'status_id' => $request->status_id,
            'user_id' => $request->user_id
        ];

        // Validate required fields
        if ($inputs['work_title'] == null) {
            return $this->handleError($inputs['work_title'], __('validation.custom.title.required'), 400);
        }

        if ($inputs['type_id'] == null) {
            return $this->handleError($inputs['type_id'], __('validation.custom.type_name.required'), 400);
        }

        $work = Work::create($inputs);

        if ($request->categories_ids != null) {
            $work->categories()->sync($request->categories_ids);
        }

        if ($request->hasFile('file_url')) {
            if ($request->file_type_id == null) {
                return $this->handleError($request->file_type_id, __('validation.required') . ' (' . __('miscellaneous.file_type') . ') ', 400);
            }

            $type = Type::find($request->file_type_id);

            if (is_null($type)) {
                return $this->handleError(__('notifications.find_type_404'));
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
                return $this->handleError($request->image_type_id, __('validation.required'), 400);
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

        return $this->handleResponse(new ResourcesWork($work), __('notifications.create_work_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $work = Work::find($id);

        if (is_null($work)) {
            return $this->handleError(__('notifications.find_work_404'));
        }

        if ($request->hasHeader('X-user-id') and $request->hasHeader('X-ip-address') or $request->hasHeader('X-user-id') and !$request->hasHeader('X-ip-address')) {
            $session = Session::where('user_id', $request->header('X-user-id'))->first();

            if (is_null($session)) {
                $session = Session::create([
                    'id' => Str::random(255),
                    'ip_address' =>  $request->hasHeader('X-ip-address') ? $request->header('X-ip-address') : null,
                    'user_agent' => $request->header('X-user-agent'),
                    'user_id' => $request->header('X-user-id')
                ]);

                $session->works()->attach([$work->id]);

            } else {
                if (count($session->works) == 0) {
                    $session->works()->attach([$work->id]);
                }

                if (count($session->works) > 0) {
                    $session->works()->syncWithoutDetaching([$work->id]);
                }
            }
        }

        if ($request->hasHeader('X-ip-address')) {
            $session = Session::where('ip_address', $request->header('X-ip-address'))->first();

            if (is_null($session)) {
                $session = Session::create([
                    'id' => Str::random(255),
                    'ip_address' =>  $request->header('X-ip-address'),
                    'user_agent' => $request->header('X-user-agent')
                ]);

                $session->works()->attach([$work->id]);

            } else {
                if (count($session->works) == 0) {
                    $session->works()->attach([$work->id]);
                }

                if (count($session->works) > 0) {
                    $session->works()->syncWithoutDetaching([$work->id]);
                }
            }
        }

        return $this->handleResponse(new ResourcesWork($work), __('notifications.find_work_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Work  $work
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Work $work)
    {
        // Get inputs
        $inputs = [
            // 'id' => $request->id,
            'work_title' => $request->work_title,
            'work_content' => $request->work_content,
            'work_url' => $request->work_url,
            'type_id' => $request->type_id,
            'status_id' => $request->status_id,
            'user_id' => $request->user_id
        ];
        // $current_work = Work::find($inputs['id']);

        if ($inputs['work_title'] != null) {
            $work->update([
                'work_title' => $inputs['work_title'],
                'updated_at' => now()
            ]);
        }

        if ($inputs['work_content'] != null) {
            $work->update([
                'work_content' => $inputs['work_content'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['work_url'] != null) {
            $work->update([
                'work_url' => $inputs['work_url'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['type_id'] != null) {
            $work->update([
                'type_id' => $inputs['type_id'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['status_id'] != null) {
            $work->update([
                'status_id' => $inputs['status_id'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['user_id'] != null) {
            $work->update([
                'user_id' => $inputs['user_id'],
                'updated_at' => now(),
            ]);
        }

        if ($request->categories_ids == null) {
            if (count($work->categories) > 0) {
                $work->categories()->detach();
            }

        } else {
            $work->categories()->sync($request->categories_ids);
        }

        return $this->handleResponse(new ResourcesWork($work), __('notifications.update_work_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Work  $work
     * @return \Illuminate\Http\Response
     */
    public function destroy(Work $work)
    {
        $work->delete();

        $works = Work::all();

        return $this->handleResponse(ResourcesWork::collection($works), __('notifications.delete_work_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Find current trends.
     *
     * @param  string $year
     * @return \Illuminate\Http\Response
     */
    public function trends($year)
    {
        $works = Work::whereHas('sessions', function ($query) use ($year) {
                    $query->whereYear('sessions.created_at', '=', $year);
                })->distinct()->limit(7)->get()->reverse()->values();
        $count_all = Work::whereHas('sessions', function ($query) use ($year) {
                    $query->whereYear('sessions.created_at', '=', $year);
                })->distinct()->count();

        return $this->handleResponse(ResourcesWork::collection($works), __('notifications.find_all_works_success'), null, $count_all);
    }

    /**
     * Get all by title.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request, $data)
    {
        $works = Work::where('work_title', 'LIKE', '%' . $data . '%')->orderByDesc('created_at')->paginate(12);
        $count_all = Work::where('work_title', 'LIKE', '%' . $data . '%')->count();

        if ($request->hasHeader('X-user-id') and $request->hasHeader('X-ip-address') or $request->hasHeader('X-user-id') and !$request->hasHeader('X-ip-address')) {
            $session = Session::where('user_id', $request->header('X-user-id'))->first();

            if (!empty($session)) {
                if (count($session->works) == 0) {
                    $session->works()->attach($works->pluck('id'));
                }

                if (count($session->works) > 0) {
                    $session->works()->syncWithoutDetaching($works->pluck('id'));
                }
            }
        }

        if ($request->hasHeader('X-ip-address')) {
            $session = Session::where('ip_address', $request->header('X-ip-address'))->first();

            if (!empty($session)) {
                if (count($session->works) == 0) {
                    $session->works()->attach($works->pluck('id'));
                }

                if (count($session->works) > 0) {
                    $session->works()->syncWithoutDetaching($works->pluck('id'));
                }
            }
        }

        return $this->handleResponse(ResourcesWork::collection($works), __('notifications.find_all_works_success'), $works->lastPage(), $count_all);
    }

    /**
     * Get by user.
     *
     * @param  int $user_id
     * @return \Illuminate\Http\Response
     */
    public function findAllByUser($user_id)
    {
        $user = User::find($user_id);

        if (is_null($user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        $works = Work::where('user_id', $user->id)->orderByDesc('updated_at')->paginate(12);
        $count_all = Work::where('user_id', $user->id)->count();

        return $this->handleResponse(ResourcesWork::collection($works), __('notifications.find_all_works_success'), $works->lastPage(), $count_all);
    }

    /**
     * Get by type.
     *
     * @param  string $locale
     * @param  string $type_name
     * @return \Illuminate\Http\Response
     */
    public function findAllByType($locale, $type_name)
    {
        $type = Type::where('type_name->' . $locale, $type_name)->first();

        if (is_null($type)) {
            return $this->handleError(__('notifications.find_type_404'));
        }

        $works = Work::where('type_id', $type->id)->orderByDesc('created_at')->paginate(12);
        $count_all = Work::where('type_id', $type->id)->count();

        return $this->handleResponse(ResourcesWork::collection($works), __('notifications.find_all_works_success'), $works->lastPage(), $count_all);
    }

    /**
     * Get by type and status.
     *
     * @param  string $locale
     * @param  string $type_name
     * @param  string $status_name
     * @return \Illuminate\Http\Response
     */
    public function findAllByTypeStatus($locale, $type_name, $status_name)
    {
        $type = Type::where('type_name->' . $locale, $type_name)->first();

        if (is_null($type)) {
            return $this->handleError(__('notifications.find_type_404'));
        }

        $status = Status::where('status_name->' . $locale, $status_name)->first();

        if (is_null($status)) {
            return $this->handleError(__('notifications.find_status_404'));
        }

        $works = Work::where([['type_id', $type->id], ['status_id', $status->id]])->orderByDesc('created_at')->paginate(12);
        $count_all = Work::where([['type_id', $type->id], ['status_id', $status->id]])->count();

        return $this->handleResponse(ResourcesWork::collection($works), __('notifications.find_all_works_success'), $works->lastPage(), $count_all);
    }

    /**
     * Find work views.
     *
     * @param  int  $work_id
     * @return \Illuminate\Http\Response
     */
    public function findViews($work_id)
    {
        $work = Work::find($work_id);

        if (is_null($work)) {
            return $this->handleError(__('notifications.find_work_404'));
        }

        $sessions = Session::whereHas('works', function ($query) use ($work) {
                        // $query->where('work_session.read', 1)
                        $query->where('work_session.work_id', $work->id)
                            ->orderByDesc('work_session.created_at');
                    })->get();
        $count_all = Session::whereHas('works', function ($query) use ($work) {
                        // $query->where('work_session.read', 1)
                        $query->where('work_session.work_id', $work->id)
                            ->orderByDesc('work_session.created_at');
                    })->count();

        return $this->handleResponse(ResourcesSession::collection($sessions), __('notifications.find_all_sessions_success'), null, $count_all);
    }

    /**
     * Filter works by categories.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function filterByCategories(Request $request)
    {
        $works = Work::whereHas('categories', function ($query) use ($request) {
                        $query->whereIn('categories.id', $request->categories_ids);
                    })->orderByDesc('works.created_at')->paginate(12);
        $count_all = Work::whereHas('categories', function ($query) use ($request) {
                        $query->whereIn('categories.id', $request->categories_ids);
                    })->count();

        return $this->handleResponse(ResourcesWork::collection($works), __('notifications.find_all_works_success'), $works->lastPage(), $count_all);
    }

    /**
     * Filter works by categories.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $locale
     * @param  string $type_name
     * @param  string $status_name
     * @return \Illuminate\Http\Response
     */
    public function filterByCategoriesTypeStatus(Request $request, $locale, $type_name, $status_name)
    {
        $type = Type::where('type_name->' . $locale, $type_name)->first();

        if (is_null($type)) {
            return $this->handleError(__('notifications.find_type_404'));
        }

        $status = Status::where('status_name->' . $locale, $status_name)->first();

        if (is_null($status)) {
            return $this->handleError(__('notifications.find_status_404'));
        }

        if ($request->categories_ids[0] == 0) {
            $works = Work::where([['type_id', $type->id], ['status_id', $status->id]])->orderByDesc('created_at')->get();
            $count_all = Work::where([['type_id', $type->id], ['status_id', $status->id]])->count();

            return $this->handleResponse(ResourcesWork::collection($works), __('notifications.find_all_works_success'), null, $count_all);

        } else {
            $works = Work::whereHas('categories', function ($query) use ($request) {
                            $query->whereIn('categories.id', $request->categories_ids);
                        })->where([['works.type_id', $type->id], ['works.status_id', $status->id]])->orderByDesc('works.created_at')->get();
            $count_all = Work::whereHas('categories', function ($query) use ($request) {
                            $query->whereIn('categories.id', $request->categories_ids);
                        })->where([['works.type_id', $type->id], ['works.status_id', $status->id]])->count();

            return $this->handleResponse(ResourcesWork::collection($works), __('notifications.find_all_works_success'), null, $count_all);
        }
    }

    /**
     * Switch the work view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $work_id
     * @return \Illuminate\Http\Response
     */
    public function switchView(Request $request, $work_id)
    {
        $work = Work::find($work_id);

        if (is_null($work)) {
            return $this->handleError(__('notifications.find_work_404'));
        }

        if (!$request->hasHeader('X-user-id') and !$request->hasHeader('X-ip-address')) {
            return $this->handleError(__('validation.custom.owner.required'));
        }

        if ($request->hasHeader('X-user-id') and $request->hasHeader('X-ip-address') or $request->hasHeader('X-user-id') and !$request->hasHeader('X-ip-address')) {
            $session = Session::where('user_id', $request->header('X-user-id'))->first();

            if (!empty($session)) {
                if (count($session->works) == 0) {
                    $session->works()->attach([$work->id => ['read' => 1]]);
                }

                if (count($session->works) > 0) {
                    foreach ($session->works as $work) {
                        $session->works()->syncWithoutDetaching([$work->id => ['read' => ($work->pivot->read == 1 ? 0 : 1)]]);
                    }
                }

                if ($work->user_id != null) {
                    $status_unread = Status::where('status_name->fr', 'Non lue')->first();
                    $type_consulting = Type::where('type_name->fr', 'Consultation d\'œuvre')->first();
                    $visitor = User::find($request->header('X-user-id'));

                    if (is_null($visitor)) {
                        return $this->handleError(__('notifications.find_visitor_404'));
                    }

                    /*
                        HISTORY AND/OR NOTIFICATION MANAGEMENT
                     */
                    if (!empty($visitor)) {
                        Notification::create([
                            'type_id' => $type_consulting->id,
                            'status_id' => $status_unread->id,
                            'from_user_id' => $visitor->id,
                            'to_user_id' => $work->user_id,
                            'work_id' => $work->id
                        ]);
                    }
                }
            }
        }

        if (!$request->hasHeader('X-user-id') and $request->hasHeader('X-ip-address')) {
            $session = Session::where('ip_address', $request->header('X-ip-address'))->first();

            if (!empty($session)) {
                if ($session->works() == null) {
                    $session->works()->attach([$work->id => ['read' => 1]]);
                }

                if ($session->works() != null) {
                    foreach ($session->works as $work) {
                        $session->works()->syncWithoutDetaching([$work->id => ['read' => ($work->pivot->read == 1 ? 0 : 1)]]);
                    }
                }
            }
        }
    }

    /**
     * Edit some files in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $type_id
     * @param  int $work_id
     * @return \Illuminate\Http\Response
     */
    public function uploadFiles(Request $request)
    {
        $type = Type::find($request->type_id);

        if (is_null($type)) {
            return $this->handleError(__('notifications.find_type_404'));
        }

        $work = Work::find($request->work_id);

        if (is_null($work)) {
            return $this->handleError(__('notifications.find_work_404'));
        }

        if ($request->hasFile('file_url')) {
            $file_url = ($type->id == 7 ? 'documents/works/' : ($type->id == 8 ? 'audios/works/' : 'images/works/')) . $work->id . '/' . Str::random(50) . '.' . $request->file('file_url')->extension();

            // Upload file
            Storage::url(Storage::disk('public')->put($file_url, $request->file('file_url')));

            File::create([
                'file_name' => trim($request->file_name) != null ? $request->file_name : $work->work_title,
                'file_url' => $file_url,
                'type_id' => $type->id,
                'work_id' => $work->id
            ]);
        }

        return $this->handleResponse(new ResourcesWork($work), __('notifications.update_work_success'));
    }

    /**
     * Update work picture in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function addImage(Request $request, $id)
    {
        $type = Type::where('type_name->fr', 'Image (Photo/Vidéo)')->first();
        $inputs = [
            'work_id' => $request->work_id,
            'image_64' => $request->image_64
        ];
        // $extension = explode('/', explode(':', substr($inputs['image_64'], 0, strpos($inputs['image_64'], ';')))[1])[1];
        $replace = substr($inputs['image_64'], 0, strpos($inputs['image_64'], ',') + 1);
        // Find substring from replace here eg: data:image/png;base64,
        $image = str_replace($replace, '', $inputs['image_64']);
        $image = str_replace(' ', '+', $image);
        // Create image URL
		$image_url = 'images/works/' . $id . '/' . Str::random(50) . '.png';

		// Upload image
		Storage::url(Storage::disk('public')->put($image_url, base64_decode($image)));

		$work = Work::find($id);

        File::create([
            'file_name' => trim($request->file_name) != null ? $request->file_name : $work->work_title,
            'file_url' => $image_url,
            'type_id' => $type->id,
            'work_id' => $work->id
        ]);

        return $this->handleResponse(new ResourcesWork($work), __('notifications.update_work_success'));
    }
}
