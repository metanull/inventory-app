<?php

namespace Tests\Feature\Api\ProvinceTranslation;

use App\Models\ProvinceTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AnonymousTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_anonymous_user_cannot_access_index(): void
    {
        $response = $this->getJson(route('province-translation.index'));

        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_access_show(): void
    {
        $province = \App\Models\Province::factory()->withoutTranslations()->create();
        $language = \App\Models\Language::factory()->create();
        $provinceTranslation = ProvinceTranslation::factory()->create([
            'province_id' => $province->id,
            'language_id' => $language->id,
        ]);

        $response = $this->getJson(route('province-translation.show', ['province_translation' => $provinceTranslation->id]));

        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_store(): void
    {
        $province = \App\Models\Province::factory()->withoutTranslations()->create();
        $language = \App\Models\Language::factory()->create();
        $data = ProvinceTranslation::factory()->make([
            'province_id' => $province->id,
            'language_id' => $language->id,
        ])->toArray();

        $response = $this->postJson(route('province-translation.store'), $data);

        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_update(): void
    {
        $province = \App\Models\Province::factory()->withoutTranslations()->create();
        $language = \App\Models\Language::factory()->create();
        $provinceTranslation = ProvinceTranslation::factory()->create([
            'province_id' => $province->id,
            'language_id' => $language->id,
        ]);
        $data = ['name' => 'Updated Province'];

        $response = $this->putJson(route('province-translation.update', ['province_translation' => $provinceTranslation->id]), $data);

        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_destroy(): void
    {
        $province = \App\Models\Province::factory()->withoutTranslations()->create();
        $language = \App\Models\Language::factory()->create();
        $provinceTranslation = ProvinceTranslation::factory()->create([
            'province_id' => $province->id,
            'language_id' => $language->id,
        ]);

        $response = $this->deleteJson(route('province-translation.destroy', ['province_translation' => $provinceTranslation->id]));

        $response->assertUnauthorized();
    }
}
