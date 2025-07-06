<?php

namespace Tests\Feature\Api\LocationTranslation;

use App\Models\LocationTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_destroy_location_translation(): void
    {
        $locationTranslation = LocationTranslation::factory()->create();

        $response = $this->deleteJson(route('location-translation.destroy', ['location_translation' => $locationTranslation->id]));

        $response->assertNoContent();

        $this->assertDatabaseMissing('location_translations', [
            'id' => $locationTranslation->id,
        ]);
    }

    public function test_destroy_returns_not_found_for_non_existent_location_translation(): void
    {
        $response = $this->deleteJson(route('location-translation.destroy', ['location_translation' => 'non-existent-id']));

        $response->assertNotFound();
    }
}
