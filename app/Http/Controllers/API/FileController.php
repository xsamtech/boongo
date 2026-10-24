<?php

namespace App\Http\Controllers\API;

use App\Models\File;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Dedoc\Scramble\Attributes\BodyParameter;
use App\Http\Resources\File as ResourcesFile;
use App\Http\Resources\FileGroups;
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
     */
    public function index()
    {
        $files = File::orderedForApi()->paginate(10);
        $count_files = File::count();

        return $this->handleResponse(new FileGroups($files->getCollection()), __('notifications.find_all_files_success'), $files->lastPage(), $count_files);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @requestMediaType multipart/form-data
     */
    #[BodyParameter('part_title', description: 'Colonne files.part_title : TEXT; NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('position', description: 'Colonne files.position : INT; NULL autorisé.', required: false, type: 'integer')]
    #[BodyParameter('media_length', description: 'Colonne files.media_length : INT; NULL autorisé.', required: false, type: 'integer')]
    #[BodyParameter('work_id', description: 'Colonne files.work_id : BIGINT; NULL autorisé.', required: false, type: 'integer')]
    #[BodyParameter('program_id', description: 'Colonne files.program_id : BIGINT; NULL autorisé.', required: false, type: 'integer')]
    #[BodyParameter('message_id', description: 'Colonne files.message_id : BIGINT; NULL autorisé.', required: false, type: 'integer')]
    #[BodyParameter('part_cover_image64', description: 'Image encodée en base64 (data:image/...;base64,...).', required: false, type: 'string')]
    #[BodyParameter('file_content', description: 'Fichier à téléverser (multipart/form-data).', required: false, type: 'string', format: 'binary')]
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
            $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'tif', 'svg', 'heif', 'heic', 'ico', 'mp4', 'm4v', 'mov', 'avi', 'wmv', 'flv', 'mkv', 'webm', 'mpeg', 'mpg', 'mpe', 'mpv', '3gp', '3g2', 'mts', 'm2ts', 'ts', 'vob', 'ogv', 'ogg', 'rm', 'rmvb', 'asf', 'divx', 'f4v', 'mxf', 'm2v', 'm4v', 'hevc'];
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
            $image = str_replace($replace, '', $request->part_cover_image64);
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
     */
    #[BodyParameter('id', description: 'Colonne files.id : BIGINT; NOT NULL. Identifiant du corps utilisé par les contrôles existants, distinct du paramètre de route.', required: false, type: 'integer')]
    #[BodyParameter('file_name', description: 'Colonne files.file_name : TEXT; NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('file_content', description: 'Fichier à téléverser (multipart/form-data).', required: false, type: 'string', format: 'binary')]
    #[BodyParameter('part_title', description: 'Colonne files.part_title : TEXT; NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('part_cover_image64', description: 'Image encodée en base64 (data:image/...;base64,...).', required: false, type: 'string')]
    #[BodyParameter('position', description: 'Colonne files.position : INT; NULL autorisé.', required: false, type: 'integer')]
    #[BodyParameter('media_length', description: 'Colonne files.media_length : INT; NULL autorisé.', required: false, type: 'integer')]
    #[BodyParameter('type_id', description: 'Colonne files.type_id : BIGINT; NULL autorisé.', required: false, type: 'integer')]
    #[BodyParameter('work_id', description: 'Colonne files.work_id : BIGINT; NULL autorisé.', required: false, type: 'integer')]
    #[BodyParameter('program_id', description: 'Colonne files.program_id : BIGINT; NULL autorisé.', required: false, type: 'integer')]
    #[BodyParameter('message_id', description: 'Colonne files.message_id : BIGINT; NULL autorisé.', required: false, type: 'integer')]
    public function update(Request $request, File $file)
    {
        // Group
        $file_type_group = Group::where('group_name', 'Type de fichier')->first();
        // Types
        $image_type = Type::where([['type_name->fr', 'Image (Photo/Vidéo)'], ['group_id', $file_type_group->id]])->first();
        $document_type = Type::where([['type_name->fr', 'Document'], ['group_id', $file_type_group->id]])->first();
        $audio_type = Type::where([['type_name->fr', 'Audio'], ['group_id', $file_type_group->id]])->first();
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'file_name' => $request->file_name,
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

                foreach ($files as $another_file) {
                    if ($current_file->file_name != $inputs['file_name'] && $another_file->file_name == $inputs['file_name']) {
                        return $this->handleError($inputs['file_name'], __('validation.custom.file_name.exists'), 400);
                    }
                }
            }

            $file->update([
                'file_name' => $inputs['file_name'],
                'updated_at' => now()
            ]);
        }

        if ($request->hasFile('file_content')) {
            $singleFile = $request->file('file_content');
            $current_file = File::find($inputs['id']);
            $work = Work::find($current_file->work_id);

            if (is_null($work)) {
                return $this->handleError(__('notifications.find_work_404'));
            }

            // Types of extensions for different file types
            $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'tif', 'svg', 'heif', 'heic', 'ico', 'mp4', 'm4v', 'mov', 'avi', 'wmv', 'flv', 'mkv', 'webm', 'mpeg', 'mpg', 'mpe', 'mpv', '3gp', '3g2', 'mts', 'm2ts', 'ts', 'vob', 'ogv', 'ogg', 'rm', 'rmvb', 'asf', 'divx', 'f4v', 'mxf', 'm2v', 'm4v', 'hevc'];
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

        if ($inputs['part_cover_image64'] != null) {
            // Check if id is required
            if (!$request->filled('id')) {
                return $this->handleError('id', __('validation.custom.id.required'), 400);
            }

            $curr_file = File::find($inputs['id']);

            if ($curr_file == null) {
                return $this->handleError(__('notifications.find_file_404'));
            }

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
                'part_cover_url' => config('filesystems.disks.s3.url') . $image_url,
                'updated_at' => now()
            ]);
        }

        foreach (['position', 'media_length', 'type_id', 'work_id', 'program_id', 'message_id'] as $field) {
            if ($inputs[$field] != null) {
                $file->update([
                    $field => $inputs[$field],
                    'updated_at' => now()
                ]);
            }
        }

        return $this->handleResponse(new ResourcesFile($file), __('notifications.update_file_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\File  $file
     */
    public function destroy(File $file)
    {
        $file->delete();

        $files = File::orderedForApi()->paginate(10);
        $count_files = File::count();

        return $this->handleResponse(new FileGroups($files->getCollection()), __('notifications.delete_file_success'), $files->lastPage(), $count_files);
    }
}
