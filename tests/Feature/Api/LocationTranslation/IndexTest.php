<?php

namespace Tests\Feature\Api\LocationTranslation;

use App\Models\LocationTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class IndexTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createVisitorUser();
        $this->actingAs($this->user);
    }

    public function test_can_list_location_translations(): void
    {
        LocationTranslation::factory()->count(3)->create();

        $response = $this->getJson(route('location-translation.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'location_id',
                        'language_id',
                        'name',
                        'description',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);

        $this->assertGreaterThanOrEqual(3, count($response->json('data')));
    }

    public function test_index_returns_empty_when_no_location_translations(): void
    {
        $response = $this->getJson(route('location-translation.index'));

        $response->assertOk()
            ->assertJson([
                'data' => [],
            ]);
    }
}
