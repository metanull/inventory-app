<?php

namespace Tests\Feature\Api\ProvinceTranslation;

use App\Models\ProvinceTranslation;
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

    public function test_can_destroy_province_translation(): void
    {
        $provinceTranslation = ProvinceTranslation::factory()->create();

        $response = $this->deleteJson(route('province-translation.destroy', ['province_translation' => $provinceTranslation->id]));

        $response->assertNoContent();

        $this->assertDatabaseMissing('province_translations', [
            'id' => $provinceTranslation->id,
        ]);
    }

    public function test_destroy_returns_not_found_for_non_existent_province_translation(): void
    {
        $response = $this->deleteJson(route('province-translation.destroy', ['province_translation' => 'non-existent-id']));

        $response->assertNotFound();
    }
}
