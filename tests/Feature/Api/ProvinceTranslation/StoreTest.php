<?php

namespace Tests\Feature\Api\ProvinceTranslation;

use App\Models\Language;
use App\Models\Province;
use App\Models\ProvinceTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class StoreTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createDataUser();
        $this->actingAs($this->user);
    }

    public function test_can_store_province_translation(): void
    {
        $province = Province::factory()->withoutTranslations()->create();
        $language = Language::factory()->create();
        $data = ProvinceTranslation::factory()->make([
            'province_id' => $province->id,
            'language_id' => $language->id,
        ])->toArray();

        $response = $this->postJson(route('province-translation.store'), $data);

        $response->assertCreated()
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
            ]);

        $this->assertDatabaseHas('province_translations', [
            'province_id' => $data['province_id'],
            'language_id' => $data['language_id'],
            'name' => $data['name'],
        ]);
    }

    public function test_store_requires_province_id(): void
    {
        $language = Language::factory()->create();
        $data = ProvinceTranslation::factory()->make([
            'language_id' => $language->id,
        ])->except(['province_id']);

        $response = $this->postJson(route('province-translation.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['province_id']);
    }

    public function test_store_requires_language_id(): void
    {
        $province = Province::factory()->withoutTranslations()->create();
        $data = ProvinceTranslation::factory()->make([
            'province_id' => $province->id,
        ])->except(['language_id']);

        $response = $this->postJson(route('province-translation.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['language_id']);
    }

    public function test_store_requires_name(): void
    {
        $province = Province::factory()->withoutTranslations()->create();
        $language = Language::factory()->create();
        $data = ProvinceTranslation::factory()->make([
            'province_id' => $province->id,
            'language_id' => $language->id,
        ])->except(['name']);

        $response = $this->postJson(route('province-translation.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_allows_null_description(): void
    {
        $province = Province::factory()->withoutTranslations()->create();
        $language = Language::factory()->create();
        $data = ProvinceTranslation::factory()->make([
            'province_id' => $province->id,
            'language_id' => $language->id,
            'description' => null,
        ])->toArray();

        $response = $this->postJson(route('province-translation.store'), $data);

        $response->assertCreated();
        $this->assertDatabaseHas('province_translations', [
            'province_id' => $data['province_id'],
            'language_id' => $data['language_id'],
            'description' => null,
        ]);
    }
}
