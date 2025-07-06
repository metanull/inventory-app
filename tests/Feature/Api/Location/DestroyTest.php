<?php

namespace Tests\Feature\Api\Location;

use App\Models\Country;
use App\Models\Language;
use App\Models\Location;
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

    public function test_can_delete_location(): void
    {
        Language::factory(3)->create();
        $country = Country::factory()->create();
        $Location = Location::factory()->create(['country_id' => $country->id]);

        $response = $this->deleteJson(route('location.destroy', $Location));

        $response->assertNoContent();

        $this->assertDatabaseMissing('Locations', [
            'id' => $Location->id,
        ]);

        // Check that related language entries are also deleted (cascade)
        $this->assertDatabaseMissing('Location_language', [
            'Location_id' => $Location->id,
        ]);
    }

    public function test_shows_404_for_nonexistent_location(): void
    {
        $response = $this->deleteJson(route('location.destroy', 'non-existent-id'));

        $response->assertNotFound();
    }

    public function test_deleting_location_does_not_delete_related_countries_or_languages(): void
    {
        $languages = Language::factory(2)->create();
        $country = Country::factory()->create();
        $Location = Location::factory()->create(['country_id' => $country->id]);

        $response = $this->deleteJson(route('location.destroy', $Location));

        $response->assertNoContent();

        // Verify country still exists
        $this->assertDatabaseHas('countries', [
            'id' => $country->id,
        ]);

        // Verify languages still exist
        foreach ($languages as $language) {
            $this->assertDatabaseHas('languages', [
                'id' => $language->id,
            ]);
        }
    }
}
