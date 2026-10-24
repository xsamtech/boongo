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

        $work = $this->resolveSchema($spec['paths']['/work']['post']['requestBody']['content']['multipart/form-data']['schema'], $spec);
        $this->assertSame('number', $work['properties']['consultation_price']['type']);
        $this->assertSame('integer', $work['properties']['categories_ids']['items']['type']);
        $this->assertSame('binary', $work['properties']['files_urls']['items']['format']);
        $this->assertSame('binary', $work['properties']['part_covers']['items']['format']);
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

        $list = $spec['paths']['/work']['get']['responses']['200']['content']['application/json']['schema'];
        $this->assertSame(['success', 'message', 'data', 'ad', 'lastPage', 'count'], array_keys($list['properties']));
        $this->assertSame('array', $list['properties']['data']['type']);
        $this->assertSame('#/components/schemas/Work', $list['properties']['data']['items']['$ref']);

        $single = $spec['paths']['/work/{id}']['get']['responses']['200']['content']['application/json']['schema'];
        $this->assertSame('#/components/schemas/Work', $single['properties']['data']['$ref']);
        $this->assertArrayNotHasKey('lastPage', $single['properties']);
        $this->assertArrayHasKey('work_title', $spec['components']['schemas']['Work']['properties']);
        $this->assertArrayHasKey('part_cover_url', $spec['components']['schemas']['File']['properties']);

        $notFound = $spec['paths']['/work/{id}']['get']['responses']['404']['content']['application/json']['schema'];
        $this->assertArrayHasKey('success', $notFound['properties']);
        $this->assertArrayHasKey('message', $notFound['properties']);
        $this->assertArrayNotHasKey('data', $notFound['properties']);

        $upload = $spec['paths']['/work/upload_files']['post'];
        $uploadBody = $this->resolveSchema($upload['requestBody']['content']['multipart/form-data']['schema'], $spec);
        $this->assertSame('binary', $uploadBody['properties']['part_covers']['items']['format']);
        $this->assertArrayHasKey('422', $upload['responses']);

        $fileGroups = $spec['paths']['/file']['get']['responses']['200']['content']['application/json']['schema']['properties']['data'];
        $this->assertSame('object', $fileGroups['type']);
        $this->assertSame('#/components/schemas/File', $fileGroups['additionalProperties']['items']['$ref']);
        $programGroups = $spec['components']['schemas']['Program']['properties']['files'];
        $this->assertSame('object', $programGroups['type']);
        $this->assertSame('#/components/schemas/File', $programGroups['additionalProperties']['items']['$ref']);

        foreach ($spec['paths'] as $path => $operations) {
            // Broadcasting is provided by Laravel, outside the API controllers.
            if (str_starts_with($path, '/broadcasting/')) {
                continue;
            }
            foreach ($operations as $method => $operation) {
                $schema = $operation['responses']['200']['content']['application/json']['schema'] ?? null;
                if ($schema !== null) {
                    $this->assertNotSame(['type' => 'object'], $schema, strtoupper($method).' '.$path);
                }
            }
        }
    }

    private function resolveSchema(array $schema, array $spec): array
    {
        if (isset($schema['$ref'])) {
            $schema = $spec['components']['schemas'][basename($schema['$ref'])];
        }
        if (isset($schema['allOf'])) {
            $resolved = ['properties' => [], 'required' => []];
            foreach ($schema['allOf'] as $part) {
                $part = $this->resolveSchema($part, $spec);
                $resolved['properties'] = array_replace($resolved['properties'], $part['properties'] ?? []);
                $resolved['required'] = array_values(array_unique(array_merge($resolved['required'], $part['required'] ?? [])));
            }

            return $resolved;
        }

        return $schema;
    }
}
