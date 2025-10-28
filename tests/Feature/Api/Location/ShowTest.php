<?php

namespace Tests\Feature\Api\Location;

use App\Enums\Permission;
use App\Models\Country;
use App\Models\Language;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class ShowTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUserWith([Permission::VIEW_DATA->value]);
        $this->actingAs($this->user);
    }

    public function test_show_returns_the_default_structure_without_relations(): void
    {
        Language::factory(3)->create();
        Country::factory(2)->create();
        $Location = Location::factory()->create();

        $response = $this->getJson(route('location.show', $Location));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'internal_name',
                    'country_id',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.id', $Location->id)
            ->assertJsonPath('data.internal_name', $Location->internal_name)
            ->assertJsonPath('data.country_id', $Location->country_id);
    }

    public function test_show_returns_the_expected_structure_with_all_relations_loaded(): void
    {
        Language::factory(3)->create();
        Country::factory(2)->create();
        $Location = Location::factory()->create();

        $response = $this->getJson(route('location.show', [$Location, 'include' => 'translations']));

        $response->assertOk();

        $LocationData = $response->json('data');
        $this->assertArrayHasKey('translations', $LocationData);

        foreach ($LocationData['translations'] as $translation) {
            $this->assertArrayHasKey('id', $translation);
            $this->assertArrayHasKey('location_id', $translation);
            $this->assertArrayHasKey('language_id', $translation);
            $this->assertArrayHasKey('name', $translation);
            $this->assertArrayHasKey('description', $translation);
        }
    }

    public function test_shows_404_for_nonexistent_location(): void
    {
        $response = $this->getJson(route('location.show', 'non-existent-id'));

        $response->assertNotFound();
    }
}
