<?php

namespace App\Http\Controllers\API;

use App\Models\File;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Resources\File as ResourcesFile;
use App\Models\Group;
use App\Models\Type;
use App\Models\Work;
use Illuminate\Support\Facades\Storage;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class FileController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $files = File::orderBy('created_at')->paginate(10);
        $count_files = File::count();

        return $this->handleResponse(ResourcesFile::collection($files), __('notifications.find_all_files_success'), $files->lastPage(), $count_files);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Group
        $file_type_group = Group::where('group_name', 'Type de fichier')->first();
        // Types
        $image_type = Type::where([['type_name->fr', 'Image (Photo/Vidéo)'], ['group_id', $file_type_group->id]])->first();
        $document_type = Type::where([['type_name->fr', 'Document'], ['group_id', $file_type_group->id]])->first();
        $audio_type = Type::where([['type_name->fr', 'Audio'], ['group_id', $file_type_group->id]])->first();
        // Get inputs
        $inputs = [
            'part_title' => $request->part_title,
            'position' => $request->position,
            'media_length' => $request->media_length,
            'work_id' => $request->work_id,
            'program_id' => $request->program_id,
            'message_id' => $request->message_id
        ];

        // Validate required fields
        if (trim($inputs['part_title']) != null) {
            if ($inputs['work_id'] == null OR $inputs['work_id'] == ' ') {
                return $this->handleError(__('notifications.find_work_404'));
            }
        }

        $file = File::create($inputs);

        if ($request->hasFile('file_content')) {
            $singleFile = $request->file('file_content');
            $work = Work::find($file->work_id);

            if (is_null($work)) {
                return $this->handleError(__('notifications.find_work_404'));
            }

            // Types of extensions for different file types
            $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'tif', 'svg', 'heif', 'heic', 'ico'];
            $document_extensions = ['pdf', 'doc', 'docx', 'txt'];
            $audio_extensions = ['mp3', 'wav', 'flac'];

            $file_extension = strtolower($singleFile->getClientOriginalExtension());

            // File type check
            $custom_uri = '';
            $is_valid_type = false;
            $file_type_id = null;

            if (in_array($file_extension, $image_extensions)) { // File is an image
                $custom_uri = 'images/works';
                $file_type_id = $image_type->id;
                $is_valid_type = true;

            } elseif (in_array($file_extension, $document_extensions)) { // File is a document
                $custom_uri = 'documents/works';
                $file_type_id = $document_type->id;
                $is_valid_type = true;

            } elseif (in_array($file_extension, $audio_extensions)) { // File is an audio
                $custom_uri = 'audios/works';
                $file_type_id = $audio_type->id;
                $is_valid_type = true;
            }

            // If the extension does not match any valid type
            if (!$is_valid_type) {
                return $this->handleError(__('notifications.type_is_not_file'));
            }

            // Generate a unique path for the file
            $filename = $singleFile->getClientOriginalName();
            $cleaned_filename = sanitizeFileName($filename);
            $file_url =  $custom_uri . '/' . $work->id . '/' . $cleaned_filename ;

            // Upload file
            try {
                $singleFile->storeAs($custom_uri . '/' . $work->id, $cleaned_filename, 's3');

            } catch (\Throwable $th) {
                return $this->handleError($th, __('notifications.create_work_file_500'), 500);
            }

            $file->update([
                'file_name' => $cleaned_filename,
                'part_cover_url' => config('filesystems.disks.s3.url') . $file_url,
                'type_id' => $file_type_id,
                'work_id' => $work->id
            ]);
        }

        if ($request->part_cover_image64 != null) {
            $file_type_group = Group::where('group_name', 'Type de fichier')->first();
            $type = Type::where([['type_name->fr', 'Image (Photo/Vidéo)'], ['group_id', $file_type_group->id]])->first();
            // $extension = explode('/', explode(':', substr($request->part_cover_image64, 0, strpos($request->part_cover_image64, ';')))[1])[1];
            $replace = substr($request->part_cover_image64, 0, strpos($request->part_cover_image64, ',') + 1);
            // Find substring from replace here eg: data:image/png;base64,
            $image = str_replace($replace, '', $inputs['image_64']);
            $image = str_replace(' ', '+', $image);
            // Create image URL
            $image_url = 'images/works/' . $file->work_id . '/' . Str::random(50) . '.png';

            // Upload image
            Storage::url(Storage::disk('s3')->put($image_url, base64_decode($image)));

            $file->update([
                'file_name' => 'PART COVER',
                'part_cover_url' => config('filesystems.disks.s3.url') . $image_url,
                'type_id' => $type->id,
                'work_id' => $file->work_id,
                'updated_at' => now()
            ]);
        }

        return $this->handleResponse(new ResourcesFile($file), __('notifications.create_file_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $file = File::find($id);

        if (is_null($file)) {
            return $this->handleError(__('notifications.find_file_404'));
        }

        return $this->handleResponse(new ResourcesFile($file), __('notifications.find_file_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\File  $file
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, File $file)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'file_name' => $request->file_name,
            'file_url' => $request->file_url,
            'part_title' => $request->part_title,
            'part_cover_image64' => $request->part_cover_image64,
            'position' => $request->position,
            'media_length' => $request->media_length,
            'type_id' => $request->type_id,
            'work_id' => $request->work_id,
            'program_id' => $request->program_id,
            'message_id' => $request->message_id
        ];

        if (trim($inputs['file_name']) != null) {
            if ($inputs['work_id'] != null) {
                // Check if id is required
                if (!$request->filled('id')) {
                    return $this->handleError('id', __('validation.custom.id.required'), 400);
                }

                // Select all files and current file to check unique constraint
                $files = File::where('work_id', $inputs['work_id'])->get();
                $current_file = File::find($inputs['id']);

                if ($current_file == null) {
                    return $this->handleError(__('notifications.find_file_404'));
                }

                if ($files != null) {
                    foreach ($files as $another_file):
                        if ($current_file->file_name != $inputs['file_name']) {
                            if ($another_file->file_name == $inputs['file_name']) {
                                return $this->handleError($inputs['file_name'], __('validation.custom.file_name.exists'), 400);
                            }
                        }
                    endforeach;

                    $file->update([
                        'file_name' => $inputs['file_name'],
                        'updated_at' => now()
                    ]);

                } else {
                    $file->update([
                        'file_name' => $inputs['file_name'],
                        'updated_at' => now()
                    ]);
                }

            } else {
                $file->update([
                    'file_name' => $inputs['file_name'],
                    'updated_at' => now()
                ]);
            }
        }

        if ($inputs['file_url'] != null) {
            $file->update([
                'file_url' => $inputs['file_url'],
                'updated_at' => now()
            ]);
        }

        if ($inputs['part_title'] != null) {
            $file->update([
                'part_title' => $inputs['part_title'],
                'updated_at' => now()
            ]);
        }

        if ($inputs['part_cover_image64'] != null) {
            // Check if id is required
            if (!$request->filled('id')) {
                return $this->handleError('id', __('validation.custom.id.required'), 400);
            }

            $curr_file = File::find($inputs['id']);

            if ($curr_file == null) {
                return $this->handleError(__('notifications.find_file_404'));
            }

            $file_type_group = Group::where('group_name', 'Type de fichier')->first();
            $type = Type::where([['type_name->fr', 'Image (Photo/Vidéo)'], ['group_id', $file_type_group->id]])->first();
            // $extension = explode('/', explode(':', substr($inputs['part_cover_image64'], 0, strpos($inputs['part_cover_image64'], ';')))[1])[1];
            $replace = substr($inputs['part_cover_image64'], 0, strpos($inputs['part_cover_image64'], ',') + 1);
            // Find substring from replace here eg: data:image/png;base64,
            $image = str_replace($replace, '', $inputs['part_cover_image64']);
            $image = str_replace(' ', '+', $image);
            // Create image URL
            $image_url = 'images/works/' . $curr_file->work_id . '/' . Str::random(50) . '.png';

            // Upload image
            Storage::url(Storage::disk('s3')->put($image_url, base64_decode($image)));

            $file->update([
                'file_name' => 'PART COVER',
                'part_cover_url' => config('filesystems.disks.s3.url') . $image_url,
                'type_id' => $type->id,
                'work_id' => $curr_file->work_id,
                'updated_at' => now()
            ]);
        }

        if ($inputs['position'] != null) {
            $file->update([
                'position' => $inputs['position'],
                'updated_at' => now()
            ]);
        }

        if ($inputs['media_length'] != null) {
            $file->update([
                'media_length' => $inputs['media_length'],
                'updated_at' => now()
            ]);
        }

        if ($inputs['type_id'] != null) {
            $file->update([
                'type_id' => $inputs['type_id'],
                'updated_at' => now()
            ]);
        }

        if ($inputs['work_id'] != null) {
            $file->update([
                'work_id' => $inputs['work_id'],
                'updated_at' => now()
            ]);
        }

        if ($inputs['program_id'] != null) {
            $file->update([
                'program_id' => $inputs['program_id'],
                'updated_at' => now()
            ]);
        }

        if ($inputs['message_id'] != null) {
            $file->update([
                'message_id' => $inputs['message_id'],
                'updated_at' => now()
            ]);
        }

        return $this->handleResponse(new ResourcesFile($file), __('notifications.update_file_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\File  $file
     * @return \Illuminate\Http\Response
     */
    public function destroy(File $file)
    {
        $file->delete();

        $files = File::orderBy('created_at')->paginate(10);
        $count_files = File::count();

        return $this->handleResponse(ResourcesFile::collection($files), __('notifications.delete_file_success'), $files->lastPage(), $count_files);
    }
}
