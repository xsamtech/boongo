<?php

namespace Tests\Feature;

use App\Http\Controllers\API\WorkController;
use App\Models\File;
use App\Models\Work;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class WorkFileUploadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Isolate all writes in a dedicated in-memory database, without migrations.
        config(['database.connections.work_upload_tests' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        DB::setDefaultConnection('work_upload_tests');
        $schema = Schema::connection('work_upload_tests');
        $schema->create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('group_name');
        });
        $schema->create('types', function (Blueprint $table) {
            $table->id();
            $table->json('type_name');
            $table->unsignedBigInteger('group_id');
        });
        $schema->create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('currency_id');
            $table->timestamps();
        });
        $schema->create('works', function (Blueprint $table) {
            $table->id();
            foreach (['work_title', 'work_content', 'work_url', 'video_source', 'media_length', 'author', 'editor', 'is_public', 'consultation_price', 'number_of_hours', 'currency_id', 'type_id', 'status_id', 'user_id', 'organization_id', 'category_id'] as $field) {
                $table->string($field)->nullable();
            }
            $table->timestamps();
        });
        $schema->create('files', function (Blueprint $table) {
            $table->id();
            foreach (['file_name', 'file_url', 'part_title', 'part_cover_url', 'position', 'media_length'] as $field) {
                $table->string($field)->nullable();
            }
            $table->unsignedBigInteger('type_id')->nullable();
            $table->unsignedBigInteger('work_id');
            $table->timestamps();
        });
        DB::table('groups')->insert(['id' => 1, 'group_name' => 'Type de fichier']);
        foreach ([1 => 'Image (Photo/Vidéo)', 2 => 'Document', 3 => 'Audio'] as $id => $name) {
            DB::table('types')->insert(['id' => $id, 'type_name' => json_encode(['fr' => $name]), 'group_id' => 1]);
        }
        DB::table('subscriptions')->insert(['currency_id' => 1]);

        Storage::fake('s3');
        $this->withoutMiddleware();
        $this->app->instance(WorkController::class, new class extends WorkController
        {
            // Avoid unrelated resource queries while testing uploads through the real routes.
            public function handleResponse($result, $msg, $lastPage = null, $count = null, $ad = null)
            {
                return response()->json(['success' => true, 'data' => $result->resource->getAttributes()]);
            }
        });
    }

    public static function endpoints(): array
    {
        return [['/api/work'], ['/api/work/upload_files']];
    }

    private function payload(): array
    {
        $work = Work::create(['work_title' => 'Existing work', 'type_id' => 1]);

        return ['work_id' => $work->id, 'work_title' => 'New work', 'type_id' => 1];
    }

    #[DataProvider('endpoints')]
    public function test_covers_match_sparse_file_indexes_without_changing_primary_types(string $endpoint): void
    {
        $payload = $this->payload() + [
            'files_urls' => [2 => UploadedFile::fake()->create('chapter.pdf', 1, 'application/pdf'), 7 => UploadedFile::fake()->create('audio.mp3', 1, 'audio/mpeg')],
            'part_covers' => [7 => UploadedFile::fake()->image('cover.png')],
            'part_titles' => [2 => 'Chapter', 7 => 'Audio'],
            'positions' => [2 => 1, 7 => 2],
            'media_lengths' => [7 => 90],
        ];
        $this->post($endpoint, $payload, ['Accept' => 'application/json'])->assertOk();

        $this->assertSame(2, File::count());
        $document = File::where('position', 1)->firstOrFail();
        $audio = File::where('position', 2)->firstOrFail();
        $this->assertSame(2, $document->type_id);
        $this->assertNull($document->part_cover_url);
        $this->assertSame(3, $audio->type_id);
        $this->assertSame('Audio', $audio->part_title);
        $this->assertSame('90', $audio->media_length);
        $covers = Storage::disk('s3')->allFiles('images/works/'.$audio->work_id.'/covers');
        $this->assertCount(1, $covers);
        $this->assertSame(Storage::disk('s3')->url($covers[0]), $audio->part_cover_url);
        Storage::disk('s3')->assertExists('audios/works/'.$audio->work_id.'/audio.mp3');
        Storage::disk('s3')->assertExists('documents/works/'.$document->work_id.'/chapter.pdf');
    }

    #[DataProvider('endpoints')]
    public function test_primary_file_uploads_still_work_without_covers(string $endpoint): void
    {
        $payload = $this->payload() + ['files_urls' => [UploadedFile::fake()->create('video.mp4', 1, 'video/mp4')]];
        $this->post($endpoint, $payload, ['Accept' => 'application/json'])->assertOk();

        $file = File::firstOrFail();
        $this->assertSame(1, $file->type_id);
        $this->assertNull($file->part_cover_url);
        Storage::disk('s3')->assertExists('images/works/'.$file->work_id.'/video.mp4');
    }

    #[DataProvider('endpoints')]
    public function test_videos_and_disguised_text_are_rejected_as_covers_before_writing(string $endpoint): void
    {
        $payload = $this->payload();
        $workCount = Work::count();
        foreach (['cover.mp4' => 'video/mp4', 'cover.png' => 'text/plain'] as $name => $mime) {
            $this->post($endpoint, $payload + [
                'files_urls' => [UploadedFile::fake()->create('chapter.pdf', 1, 'application/pdf')],
                'part_covers' => [UploadedFile::fake()->create($name, 1, $mime)],
            ], ['Accept' => 'application/json'])->assertUnprocessable()->assertJsonValidationErrors('part_covers.0');
        }
        $this->assertSame($workCount, Work::count());
        $this->assertSame(0, File::count());
        $this->assertSame([], Storage::disk('s3')->allFiles());
    }

    #[DataProvider('endpoints')]
    public function test_orphan_covers_are_rejected(string $endpoint): void
    {
        $this->post($endpoint, $this->payload() + [
            'files_urls' => [UploadedFile::fake()->create('chapter.pdf', 1, 'application/pdf')],
            'part_covers' => [3 => UploadedFile::fake()->image('cover.png')],
        ], ['Accept' => 'application/json'])->assertUnprocessable()->assertJsonValidationErrors('part_covers.3');

        $this->assertSame(0, File::count());
        $this->assertSame([], Storage::disk('s3')->allFiles());
    }
}
