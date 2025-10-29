<?php

namespace Tests\Api\Resources;

use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Api\Traits\AuthenticatesApiRequests;
use Tests\Api\Traits\TestsApiCrud;
use Tests\Api\Traits\TestsApiDefaultSelection;
use Tests\TestCase;

class LanguageTest extends TestCase
{
    use AuthenticatesApiRequests;
    use RefreshDatabase;
    use TestsApiCrud;
    use TestsApiDefaultSelection;

    protected function getResourceName(): string
    {
        return 'language';
    }

    protected function getModelClass(): string
    {
        return Language::class;
    }

    /**
     * Override because Language requires 'id' field (it's the primary key, not auto-generated)
     */
    public function test_can_create_resource(): void
    {
        $modelClass = $this->getModelClass();
        $data = $modelClass::factory()->make($this->getFactoryData())->toArray();

        // Remove only timestamps and system-managed fields (keep 'id'!)
        $data = array_diff_key($data, array_flip(['created_at', 'updated_at', 'deleted_at', 'is_default']));

        $response = $this->postJson(route($this->getResourceName().'.store'), $data);
        $response->assertCreated()
            ->assertJsonStructure(['data' => ['id']]);

        $this->assertDatabaseHas($modelClass::make()->getTable(),
            array_intersect_key($data, array_flip($modelClass::make()->getFillable()))
        );
    }
}
