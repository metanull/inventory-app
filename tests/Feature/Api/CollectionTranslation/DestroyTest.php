<?php

namespace Tests\Feature\Api\CollectionTranslation;

use App\Models\CollectionTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class DestroyTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createDataUser();
        $this->actingAs($this->user);
    }

    public function test_can_delete_collection_translation(): void
    {
        $translation = CollectionTranslation::factory()->create();

        $response = $this->deleteJson(route('collection-translation.destroy', ['collection_translation' => $translation->id]));

        $response->assertNoContent();

        $this->assertDatabaseMissing('collection_translations', [
            'id' => $translation->id,
        ]);
    }

    public function test_delete_returns_not_found_for_non_existent_collection_translation(): void
    {
        $response = $this->deleteJson(route('collection-translation.destroy', ['collection_translation' => 'non-existent-id']));

        $response->assertNotFound();
    }

    public function test_delete_removes_only_specified_translation(): void
    {
        $translation1 = CollectionTranslation::factory()->create();
        $translation2 = CollectionTranslation::factory()->create();

        $response = $this->deleteJson(route('collection-translation.destroy', ['collection_translation' => $translation1->id]));

        $response->assertNoContent();

        $this->assertDatabaseMissing('collection_translations', [
            'id' => $translation1->id,
        ]);

        $this->assertDatabaseHas('collection_translations', [
            'id' => $translation2->id,
        ]);
    }
}
