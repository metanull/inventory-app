<?php

namespace Tests\Feature\Api\ProvinceTranslation;

use App\Models\ProvinceTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class ShowTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createVisitorUser();
        $this->actingAs($this->user);
    }

    public function test_show_returns_the_default_structure_without_relations(): void
    {
        $province = \App\Models\Province::factory()->withoutTranslations()->create();
        $language = \App\Models\Language::factory()->create();
        $provinceTranslation = ProvinceTranslation::factory()->create([
            'province_id' => $province->id,
            'language_id' => $language->id,
        ]);

        $response = $this->getJson(route('province-translation.show', ['province_translation' => $provinceTranslation->id]));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'province_id',
                    'language_id',
                    'name',
                    'description',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.id', $provinceTranslation->id)
            ->assertJsonPath('data.province_id', $provinceTranslation->province_id)
            ->assertJsonPath('data.language_id', $provinceTranslation->language_id);
    }

    public function test_show_returns_not_found_for_non_existent_province_translation(): void
    {
        $response = $this->getJson(route('province-translation.show', ['province_translation' => 'non-existent-id']));

        $response->assertNotFound();
    }
}
