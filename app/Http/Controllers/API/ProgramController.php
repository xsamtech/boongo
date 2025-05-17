<?php

namespace App\Http\Controllers\API;

use App\Models\File;
use App\Models\Group;
use App\Models\Program;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Resources\Program as ResourcesProgram;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class ProgramController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $programs = Program::orderByDesc('created_at')->paginate(4);
        $count_programs = Program::count();

        return $this->handleResponse(ResourcesProgram::collection($programs), __('notifications.find_all_programs_success'), $programs->lastPage(), $count_programs);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $inputs = [
            'course_year_id' => $request->course_year_id,
            'organization_id' => $request->organization_id
        ];

        $program = Program::create($inputs);

        if ($request->hasFile('file_url')) {
            if ($request->file_type_id == null) {
                return $this->handleError($request->file_type_id, __('validation.required') . ' (' . __('miscellaneous.file_type') . ') ', 400);
            }

            $type = Type::find($request->file_type_id);

            if (is_null($type)) {
                return $this->handleError(__('notifications.find_type_404'));
            }

            // Group
            $file_type_group = Group::where('group_name', 'Type de fichier')->first();
            // Types
            $image_type = Type::where([['type_name->fr', 'Image (Photo/Vidéo)'], ['group_id', $file_type_group->id]])->first();
            $document_type = Type::where([['type_name->fr', 'Document'], ['group_id', $file_type_group->id]])->first();
            $audio_type = Type::where([['type_name->fr', 'Audio'], ['group_id', $file_type_group->id]])->first();

            if ($type->id == $image_type->id AND $type->id == $document_type->id AND $type->id == $audio_type->id) {
                return $this->handleError(__('notifications.type_is_not_file'));
            }

            $custom_path = ($type->id == $document_type->id ? 'documents/programs' : ($type->id == $audio_type->id ? 'audios/programs' : 'images/programs'));
            $file_url =  $custom_path . '/' . $program->id . '/' . Str::random(50) . '.' . $request->file('file_url')->extension();

            // Upload file
            $dir_result = Storage::url(Storage::disk('public')->put($file_url, $request->file('file_url')));

            File::create([
                'file_name' => trim($request->file_name) != null ? $request->file_name : null,
                'file_url' => $dir_result,
                'type_id' => $request->file_type_id,
                'program_id' => $program->id
            ]);
        }

        return $this->handleResponse(new ResourcesProgram($program), __('notifications.create_program_success'));
    }

    /**
     * Display the specified resource.
     * 
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $program = Program::find($id);

        if (is_null($program)) {
            return $this->handleError(__('notifications.find_program_404'));
        }

        return $this->handleResponse(new ResourcesProgram($program), __('notifications.find_program_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Program  $program
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Program $program)
    {
        // Get inputs
        $inputs = [
            'course_year_id' => $request->course_year_id,
            'organization_id' => $request->organization_id
        ];

        if ($inputs['course_year_id'] != null) {
            $program->update([
                'course_year_id' => $inputs['course_year_id'],
                'updated_at' => now()
            ]);
        }

        if ($inputs['organization_id'] != null) {
            $program->update([
                'organization_id' => $inputs['organization_id'],
                'updated_at' => now(),
            ]);
        }

        return $this->handleResponse(new ResourcesProgram($program), __('notifications.update_program_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Program  $program
     * @return \Illuminate\Http\Response
     */
    public function destroy(Program $program)
    {
        $program->delete();

        $programs = Program::all();

        return $this->handleResponse(ResourcesProgram::collection($programs), __('notifications.delete_program_success'));
    }
}
