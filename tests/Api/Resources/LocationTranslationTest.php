<?php

namespace Tests\Api\Resources;

use App\Models\LocationTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Api\Traits\AuthenticatesApiRequests;
use Tests\Api\Traits\TestsApiCrud;
use Tests\TestCase;

class LocationTranslationTest extends TestCase
{
    use AuthenticatesApiRequests;
    use RefreshDatabase;
    use TestsApiCrud;

    protected function getResourceName(): string
    {
        return 'location-translation';
    }

    protected function getModelClass(): string
    {
        return LocationTranslation::class;
    }

    /**
     * Override - LocationTranslation index is global (not scoped), so we can't control count
     */
    public function test_can_list_resources(): void
    {
        $modelClass = $this->getModelClass();
        $modelClass::factory()->count(3)->create($this->getFactoryData());

        $response = $this->getJson(route($this->getResourceName().'.index'));

        // Just check that we get OK and data array (don't check count - it's global)
        $response->assertOk()
            ->assertJsonStructure(['data']);

        // Verify we have at least 3 items
        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(3, count($data));
    }
}
