<?php

namespace Tests\Feature\Api\LocationTranslation;

use App\Enums\Permission;
use App\Models\LocationTranslation;
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

    public function test_can_show_location_translation(): void
    {
        $location = \App\Models\Location::factory()->withoutTranslations()->create();
        $language = \App\Models\Language::factory()->create();
        $locationTranslation = LocationTranslation::factory()->create([
            'location_id' => $location->id,
            'language_id' => $language->id,
        ]);

        $response = $this->getJson(route('location-translation.show', ['location_translation' => $locationTranslation->id]));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'location_id',
                    'language_id',
                    'name',
                    'description',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.id', $locationTranslation->id)
            ->assertJsonPath('data.location_id', $locationTranslation->location_id)
            ->assertJsonPath('data.language_id', $locationTranslation->language_id);
    }

    public function test_show_returns_not_found_for_non_existent_location_translation(): void
    {
        $response = $this->getJson(route('location-translation.show', ['location_translation' => 'non-existent-id']));

        $response->assertNotFound();
    }
}
