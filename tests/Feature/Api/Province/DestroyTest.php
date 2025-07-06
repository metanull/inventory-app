<?php

namespace Tests\Feature\Api\Province;

use App\Models\Country;
use App\Models\Language;
use App\Models\Province;
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

    public function test_can_delete_province(): void
    {
        Language::factory(3)->create();
        $country = Country::factory()->create();
        $province = Province::factory()->create(['country_id' => $country->id]);

        $response = $this->deleteJson(route('province.destroy', $province));

        $response->assertNoContent();

        $this->assertDatabaseMissing('provinces', [
            'id' => $province->id,
        ]);

        // Check that related translation entries are also deleted (cascade)
        $this->assertDatabaseMissing('province_translations', [
            'province_id' => $province->id,
        ]);
    }

    public function test_shows_404_for_nonexistent_province(): void
    {
        $response = $this->deleteJson(route('province.destroy', 'non-existent-id'));

        $response->assertNotFound();
    }

    public function test_deleting_province_does_not_delete_related_countries_or_languages(): void
    {
        $languages = Language::factory(2)->create();
        $country = Country::factory()->create();
        $province = Province::factory()->create(['country_id' => $country->id]);

        $response = $this->deleteJson(route('province.destroy', $province));

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
