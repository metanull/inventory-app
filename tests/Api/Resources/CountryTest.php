<?php

namespace Tests\Api\Resources;

use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Api\Traits\AuthenticatesApiRequests;
use Tests\Api\Traits\TestsApiCrud;
use Tests\TestCase;

class CountryTest extends TestCase
{
    use AuthenticatesApiRequests;
    use RefreshDatabase;
    use TestsApiCrud;

    protected function getResourceName(): string
    {
        return 'country';
    }

    protected function getModelClass(): string
    {
        return Country::class;
    }

    /**
     * Override because Country requires 'id' field (it's the primary key, not auto-generated)
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
