<?php

namespace Tests\Feature\Api\Internationalization;

use App\Models\Internationalization;
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

    public function test_authenticated_user_can_delete_internationalization(): void
    {
        $internationalization = Internationalization::factory()->create();

        $response = $this->deleteJson(route('internationalization.destroy', $internationalization->id));

        $response->assertNoContent();
        $this->assertDatabaseMissing('internationalizations', [
            'id' => $internationalization->id,
        ]);
    }

    public function test_destroy_returns_404_for_nonexistent_internationalization(): void
    {
        $nonExistentId = $this->faker->uuid();

        $response = $this->deleteJson(route('internationalization.destroy', $nonExistentId));

        $response->assertNotFound();
    }

    public function test_destroy_returns_404_for_invalid_uuid(): void
    {
        $response = $this->deleteJson(route('internationalization.destroy', 'invalid-uuid'));

        $response->assertNotFound();
    }

    public function test_destroy_removes_internationalization_but_preserves_relationships(): void
    {
        $internationalization = Internationalization::factory()->create();
        $contextualizationId = $internationalization->contextualization_id;
        $languageId = $internationalization->language_id;

        $response = $this->deleteJson(route('internationalization.destroy', $internationalization->id));

        $response->assertNoContent();

        // Internationalization should be deleted
        $this->assertDatabaseMissing('internationalizations', [
            'id' => $internationalization->id,
        ]);

        // Related entities should still exist
        $this->assertDatabaseHas('contextualizations', [
            'id' => $contextualizationId,
        ]);
        $this->assertDatabaseHas('languages', [
            'id' => $languageId,
        ]);
    }

    public function test_multiple_internationalizations_can_be_deleted(): void
    {
        $internationalizations = Internationalization::factory(3)->create();

        foreach ($internationalizations as $internationalization) {
            $response = $this->deleteJson(route('internationalization.destroy', $internationalization->id));
            $response->assertNoContent();
        }

        foreach ($internationalizations as $internationalization) {
            $this->assertDatabaseMissing('internationalizations', [
                'id' => $internationalization->id,
            ]);
        }
    }
}
