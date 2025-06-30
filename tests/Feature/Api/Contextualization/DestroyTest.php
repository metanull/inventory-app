<?php

namespace Tests\Feature\Api\Contextualization;

use App\Models\Contextualization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_authenticated_user_can_delete_contextualization(): void
    {
        $contextualization = Contextualization::factory()->create();

        $response = $this->deleteJson(route('contextualization.destroy', $contextualization));

        $response->assertNoContent();

        $this->assertDatabaseMissing('contextualizations', [
            'id' => $contextualization->id,
        ]);
    }

    public function test_delete_returns_not_found_for_nonexistent_contextualization(): void
    {
        $response = $this->deleteJson(route('contextualization.destroy', '99999999-9999-9999-9999-999999999999'));

        $response->assertNotFound();
    }

    public function test_delete_removes_contextualization_completely(): void
    {
        $contextualization = Contextualization::factory()->create();
        $contextualizeId = $contextualization->id;

        $this->assertDatabaseHas('contextualizations', [
            'id' => $contextualizeId,
        ]);

        $response = $this->deleteJson(route('contextualization.destroy', $contextualization));

        $response->assertNoContent();

        $this->assertDatabaseMissing('contextualizations', [
            'id' => $contextualizeId,
        ]);
    }

    public function test_delete_does_not_affect_related_models(): void
    {
        $contextualization = Contextualization::factory()->forItem()->create();
        $contextId = $contextualization->context_id;
        $itemId = $contextualization->item_id;

        $response = $this->deleteJson(route('contextualization.destroy', $contextualization));

        $response->assertNoContent();

        // Related models should still exist
        $this->assertDatabaseHas('contexts', [
            'id' => $contextId,
        ]);

        $this->assertDatabaseHas('items', [
            'id' => $itemId,
        ]);
    }

    public function test_delete_contextualization_with_detail(): void
    {
        $contextualization = Contextualization::factory()->forDetail()->create();
        $contextId = $contextualization->context_id;
        $detailId = $contextualization->detail_id;

        $response = $this->deleteJson(route('contextualization.destroy', $contextualization));

        $response->assertNoContent();

        // Related models should still exist
        $this->assertDatabaseHas('contexts', [
            'id' => $contextId,
        ]);

        $this->assertDatabaseHas('details', [
            'id' => $detailId,
        ]);
    }

    public function test_delete_contextualization_with_extra_data(): void
    {
        $contextualization = Contextualization::factory()->create([
            'extra' => ['key1' => 'value1', 'key2' => 'value2'],
        ]);

        $response = $this->deleteJson(route('contextualization.destroy', $contextualization));

        $response->assertNoContent();

        $this->assertDatabaseMissing('contextualizations', [
            'id' => $contextualization->id,
        ]);
    }
}
