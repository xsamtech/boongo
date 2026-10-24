<?php

namespace Tests\Feature;

use App\Http\Controllers\API\FileController;
use App\Http\Resources\FileGroups;
use App\Http\Resources\Message as MessageResource;
use App\Http\Resources\Program as ProgramResource;
use App\Http\Resources\Work as WorkResource;
use App\Models\File;
use App\Models\Message;
use App\Models\Program;
use App\Models\Work;
use App\Support\FileOrdering;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FileResponseOrderingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.file_ordering_tests' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        DB::setDefaultConnection('file_ordering_tests');
        $schema = Schema::connection('file_ordering_tests');
        $schema->create('files', function (Blueprint $table) {
            $table->id();
            $table->integer('type_id')->nullable();
            $table->integer('position')->nullable();
            foreach (['file_name', 'file_url', 'part_title', 'part_cover_url'] as $field) {
                $table->string($field)->nullable();
            }
            $table->timestamps();
        });
        $schema->create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('group_name');
            $table->timestamps();
        });
        $schema->create('types', function (Blueprint $table) {
            $table->id();
            $table->json('type_name');
            $table->integer('group_id');
            $table->timestamps();
        });
        DB::table('groups')->insert(['id' => 1, 'group_name' => 'Type de fichier', 'created_at' => now(), 'updated_at' => now()]);
        foreach ([1 => 'Image (Photo/Vidéo)', 2 => 'Document', 3 => 'Audio'] as $id => $name) {
            DB::table('types')->insert(['id' => $id, 'type_name' => json_encode(['fr' => $name]), 'group_id' => 1, 'created_at' => now(), 'updated_at' => now()]);
        }
        $this->withoutMiddleware();
    }

    private function seedFiles(): void
    {
        foreach ([[1, null], [2, 0], [1, 10], [1, 2], [1, 2], [null, 1], [3, -1]] as $index => [$type, $position]) {
            File::create([
                'id' => $index + 1, 'type_id' => $type, 'position' => $position,
                'file_url' => 'https://example.test/file-'.($index + 1).'.png',
                'part_cover_url' => 'https://example.test/cover.png',
            ]);
        }
    }

    public function test_database_and_collection_order_agree_for_null_zero_and_tied_positions(): void
    {
        $this->seedFiles();
        $expected = [4, 5, 3, 1, 2, 7, 6];
        $this->assertSame($expected, File::orderedForApi()->pluck('id')->all());
        $this->assertSame($expected, FileOrdering::sort(File::orderByDesc('id')->get())->pluck('id')->all());
        $this->assertSame([4, 5, 3], File::orderedForApi()->paginate(3)->pluck('id')->all());
        $this->assertSame([1, 2, 7], File::orderedForApi()->paginate(3, ['*'], 'page', 2)->pluck('id')->all());
    }

    public function test_file_index_and_delete_return_groups_with_pagination(): void
    {
        $this->seedFiles();
        $response = $this->getJson('/api/file')->assertOk()->assertJsonPath('count', 7)->assertJsonPath('lastPage', 1);
        $data = $response->json('data');
        $this->assertSame([1, 2, 3, 'untyped'], array_keys($data));
        $this->assertSame([4, 5, 3, 1], array_column($data[1], 'id'));
        $this->assertSame([2], array_column($data[2], 'id'));
        $this->assertSame([6], array_column($data['untyped'], 'id'));

        $deleted = (new FileController)->destroy(File::findOrFail(5))->getData(true);
        $this->assertSame([4, 3, 1], array_column($deleted['data'][1], 'id'));
        $this->assertSame(6, $deleted['count']);
        $this->assertSame(1, $deleted['lastPage']);
    }

    public function test_program_files_are_grouped_even_when_relation_is_already_loaded(): void
    {
        $this->seedFiles();
        $program = new Program(['created_at' => now(), 'updated_at' => now()]);
        $program->setRelation('files', File::orderByDesc('id')->get());
        $program->setRelation('course_year', null);
        $data = (new ProgramResource($program))->toArray(Request::create('/'));
        $this->assertInstanceOf(FileGroups::class, $data['files']);
        $groups = $data['files']->resolve(Request::create('/'));
        $this->assertSame([4, 5, 3, 1], array_column($groups[1], 'id'));
        $this->assertSame([7], array_column($groups[3], 'id'));
        $this->assertSame([6], array_column($groups['untyped'], 'id'));
    }

    public function test_work_and_message_keep_named_type_groups_and_order_each_group(): void
    {
        $this->seedFiles();
        $files = File::orderByDesc('id')->get();
        $work = new Work(['created_at' => now(), 'updated_at' => now()]);
        $message = new Message(['created_at' => now(), 'updated_at' => now()]);
        foreach ([$work, $message] as $model) {
            $model->setRelation('files', $files);
            foreach (['likes', 'categories'] as $relation) {
                $model->setRelation($relation, collect());
            }
            foreach (['type', 'status', 'currency', 'user_owner', 'organization_owner', 'category', 'user', 'addressee_user', 'addressee_organization', 'addressee_circle', 'event'] as $relation) {
                $model->setRelation($relation, null);
            }
        }
        // Use a real request to provide the HTTP host used by existing URL helpers.
        $this->getJson('/api/file')->assertOk();
        foreach ([new WorkResource($work), new MessageResource($message)] as $resource) {
            $data = $resource->toArray(Request::create('/'));
            $this->assertSame([4, 5, 3, 1], $data['images']->collection->pluck('id')->all());
            $this->assertSame([2], $data['documents']->collection->pluck('id')->all());
            $this->assertSame([7], $data['audios']->collection->pluck('id')->all());
        }
        $data = (new WorkResource($work))->toArray(Request::create('/'));
        $this->assertSame('https://example.test/file-4.png', $data['photo_url']);
        $this->assertSame('https://example.test/file-2.png', $data['document_url']);
    }
}
