<?php

namespace Tests\Feature;

use Dedoc\Scramble\Generator;
use Dedoc\Scramble\Scramble;
use Tests\TestCase;

class ApiDocumentationTest extends TestCase
{
    public function test_generated_bodies_include_sql_types_translations_and_uploads(): void
    {
        $config = Scramble::getGeneratorConfig('default');
        $spec = app(Generator::class)->generate($config)->spec($config->openApiVersion());
        $spec = json_decode(json_encode($spec, JSON_THROW_ON_ERROR), true, flags: JSON_THROW_ON_ERROR);

        $work = $spec['paths']['/work']['post']['requestBody']['content']['multipart/form-data']['schema'];
        $this->assertSame('number', $work['properties']['consultation_price']['type']);
        $this->assertSame('integer', $work['properties']['categories_ids']['items']['type']);
        $this->assertSame('binary', $work['properties']['files_urls']['items']['format']);
        $this->assertSame(['work_title', 'type_id'], $work['required']);

        $category = $spec['paths']['/category']['post']['requestBody']['content']['application/json']['schema'];
        foreach (['en', 'fr', 'ln'] as $locale) {
            $this->assertSame('string', $category['properties']['category_name_'.$locale]['type']);
        }

        $file = $spec['paths']['/file']['post']['requestBody']['content']['multipart/form-data']['schema'];
        $this->assertSame('binary', $file['properties']['file_content']['format']);
        $this->assertNotContains('work_id', $file['required'] ?? []);

        $cover = $spec['paths']['/event/update_cover/{id}']['put']['requestBody']['content']['application/json']['schema'];
        $this->assertArrayHasKey('image_64', $cover['properties']);

        $notification = $spec['paths']['/notification/{notification}']['put']['requestBody']['content']['application/json']['schema'];
        $this->assertSame(['type_id', 'status_id'], $notification['required']);
    }
}
