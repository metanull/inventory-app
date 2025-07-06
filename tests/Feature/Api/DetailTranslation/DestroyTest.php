<?php

namespace Tests\Feature\Api\DetailTranslation;

use App\Models\DetailTranslation;
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

    public function test_can_delete_detail_translation(): void
    {
        $translation = DetailTranslation::factory()->create();

        $response = $this->deleteJson(route('detail-translation.destroy', ['detail_translation' => $translation->id]));

        $response->assertNoContent();

        $this->assertDatabaseMissing('detail_translations', [
            'id' => $translation->id,
        ]);
    }

    public function test_delete_returns_not_found_for_non_existent_detail_translation(): void
    {
        $response = $this->deleteJson(route('detail-translation.destroy', ['detail_translation' => 'non-existent-id']));

        $response->assertNotFound();
    }

    public function test_delete_removes_only_specified_translation(): void
    {
        $translation1 = DetailTranslation::factory()->create();
        $translation2 = DetailTranslation::factory()->create();

        $response = $this->deleteJson(route('detail-translation.destroy', ['detail_translation' => $translation1->id]));

        $response->assertNoContent();

        $this->assertDatabaseMissing('detail_translations', [
            'id' => $translation1->id,
        ]);

        $this->assertDatabaseHas('detail_translations', [
            'id' => $translation2->id,
        ]);
    }
}
