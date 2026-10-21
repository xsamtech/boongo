<?php

namespace Tests\Unit;

use App\Http\Controllers\API\ActivationCodeController;
use App\Http\Controllers\API\WorkController;
use App\Models\ActivationCode;
use App\Models\Work;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ApiUpdateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Container::setInstance(new Container);
        Container::getInstance()->instance('translator', new Translator(new ArrayLoader, 'fr'));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        Container::setInstance(null);

        parent::tearDown();
    }

    public static function updateValues(): array
    {
        return [
            'absent' => [[], []],
            'null and empty values' => [['code' => '', 'is_active' => null, 'user_id' => 0], []],
            'boolean false' => [['is_active' => false], []],
            'string zero' => [['is_active' => '0'], ['is_active' => '0']],
            'ordered partial update' => [
                ['user_id' => 8, 'code' => 'PROMO', 'unexpected' => 'ignored'],
                ['code' => 'PROMO', 'user_id' => 8],
            ],
        ];
    }

    #[DataProvider('updateValues')]
    public function test_updates_preserve_empty_value_rules_and_write_order(array $input, array $expected): void
    {
        $writes = [];
        $model = Mockery::mock(ActivationCode::class);
        $model->shouldReceive('update')->andReturnUsing(function (array $attributes) use (&$writes) {
            $this->assertArrayHasKey('updated_at', $attributes);
            unset($attributes['updated_at']);
            $writes = array_merge($writes, $attributes);

            return true;
        });

        $controller = Mockery::mock(ActivationCodeController::class)->makePartial();
        $controller->shouldReceive('handleResponse')->once()->andReturn('success');

        $this->assertSame('success', $controller->update(new Request($input), $model));
        $this->assertSame($expected, $writes);
    }

    public static function categoryUpdates(): array
    {
        return [
            'absent categories detach' => [[], 'detach', []],
            'null categories detach' => [['categories_ids' => null], 'detach', []],
            'provided categories sync' => [['categories_ids' => [2, 4]], 'sync', [[2, 4]]],
        ];
    }

    #[DataProvider('categoryUpdates')]
    public function test_work_category_changes_are_preserved(array $input, string $operation, array $arguments): void
    {
        $relation = Mockery::mock(BelongsToMany::class);
        $relation->shouldReceive($operation)->once()->withArgs($arguments);

        $work = Mockery::mock(Work::class);
        $work->shouldReceive('getAttribute')->with('categories')->andReturn(collect([1]));
        $work->shouldReceive('categories')->once()->andReturn($relation);
        $work->shouldNotReceive('update');

        $controller = Mockery::mock(WorkController::class)->makePartial();
        $controller->shouldReceive('handleResponse')->once()->andReturn('success');

        $this->assertSame('success', $controller->update(new Request($input), $work));
    }
}
