<?php

namespace Tests\Feature\Api\Collection;

use App\Models\Collection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class DestroyTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createDataUser();
        $this->actingAs($this->user);
    }

    public function test_can_delete_collection(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->deleteJson(route('collection.destroy', $collection));

        $response->assertNoContent();
        $this->assertDatabaseMissing('collections', ['id' => $collection->id]);
    }

    public function test_returns_404_when_collection_not_found(): void
    {
        $response = $this->deleteJson(route('collection.destroy', 'non-existent-id'));

        $response->assertNotFound();
    }

    public function test_can_delete_collection_with_translations(): void
    {
        $collection = Collection::factory()
            ->hasTranslations(2)
            ->create();

        $response = $this->deleteJson(route('collection.destroy', $collection));

        $response->assertNoContent();
        $this->assertDatabaseMissing('collections', ['id' => $collection->id]);

        // Verify translations are also deleted
        foreach ($collection->translations as $translation) {
            $this->assertDatabaseMissing('collection_translations', ['id' => $translation->id]);
        }
    }

    public function test_can_delete_collection_with_partners(): void
    {
        $collection = Collection::factory()
            ->hasAttached(
                \App\Models\Partner::factory()->count(2),
                ['level' => \App\Enums\PartnerLevel::PARTNER]
            )
            ->create();

        $response = $this->deleteJson(route('collection.destroy', $collection));

        $response->assertNoContent();
        $this->assertDatabaseMissing('collections', ['id' => $collection->id]);

        // Verify pivot relationships are removed
        $this->assertDatabaseMissing('collection_partner', [
            'collection_id' => $collection->id,
        ]);
    }

    public function test_can_delete_collection_with_items(): void
    {
        $collection = Collection::factory()
            ->hasItems(3)
            ->create();

        $response = $this->deleteJson(route('collection.destroy', $collection));

        $response->assertNoContent();
        $this->assertDatabaseMissing('collections', ['id' => $collection->id]);

        // Verify items are updated to remove collection reference
        foreach ($collection->items as $item) {
            $this->assertDatabaseHas('items', [
                'id' => $item->id,
                'collection_id' => null,
            ]);
        }
    }

    public function test_deleting_already_deleted_collection_returns_404(): void
    {
        $collection = Collection::factory()->create();
        $collection->delete();

        $response = $this->deleteJson(route('collection.destroy', $collection));

        $response->assertNotFound();
    }
}
