<?php

namespace Tests\Api\Resources;

use App\Models\ProvinceTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Api\Traits\AuthenticatesApiRequests;
use Tests\Api\Traits\TestsApiCrud;
use Tests\TestCase;

class ProvinceTranslationTest extends TestCase
{
    use AuthenticatesApiRequests;
    use RefreshDatabase;
    use TestsApiCrud;

    protected function getResourceName(): string
    {
        return 'province-translation';
    }

    protected function getModelClass(): string
    {
        return ProvinceTranslation::class;
    }

    /**
     * Override - ProvinceTranslation index is global (not scoped), so we can't control count
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
