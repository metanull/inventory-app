<?php

namespace Tests\Web\Pages;

use App\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebCrud;

class CollectionTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebCrud;

    protected function getRouteName(): string
    {
        return 'collections';
    }

    protected function getModelClass(): string
    {
        return Collection::class;
    }

    protected function getFormData(): array
    {
        return Collection::factory()->make()->toArray();
    }

    public function test_move_up_requires_authentication(): void
    {
        $collection = Collection::factory()->create();

        auth()->logout();

        $response = $this->post(route('collections.move-up', $collection));

        $response->assertRedirect(route('login'));
    }

    public function test_move_down_requires_authentication(): void
    {
        $collection = Collection::factory()->create();

        auth()->logout();

        $response = $this->post(route('collections.move-down', $collection));

        $response->assertRedirect(route('login'));
    }

    public function test_can_move_collection_up(): void
    {
        $parent = Collection::factory()->create();
        $child1 = Collection::factory()->create(['parent_id' => $parent->id, 'display_order' => 1]);
        $child2 = Collection::factory()->create(['parent_id' => $parent->id, 'display_order' => 2]);

        $response = $this->post(route('collections.move-up', $child2));

        $response->assertRedirect(route('collections.show', $parent));
        $this->assertDatabaseHas('collections', ['id' => $child1->id, 'display_order' => 2]);
        $this->assertDatabaseHas('collections', ['id' => $child2->id, 'display_order' => 1]);
    }

    public function test_can_move_collection_down(): void
    {
        $parent = Collection::factory()->create();
        $child1 = Collection::factory()->create(['parent_id' => $parent->id, 'display_order' => 1]);
        $child2 = Collection::factory()->create(['parent_id' => $parent->id, 'display_order' => 2]);

        $response = $this->post(route('collections.move-down', $child1));

        $response->assertRedirect(route('collections.show', $parent));
        $this->assertDatabaseHas('collections', ['id' => $child1->id, 'display_order' => 2]);
        $this->assertDatabaseHas('collections', ['id' => $child2->id, 'display_order' => 1]);
    }

    public function test_move_up_at_top_has_no_effect(): void
    {
        $parent = Collection::factory()->create();
        $child1 = Collection::factory()->create(['parent_id' => $parent->id, 'display_order' => 1]);
        $child2 = Collection::factory()->create(['parent_id' => $parent->id, 'display_order' => 2]);

        $response = $this->post(route('collections.move-up', $child1));

        $response->assertRedirect(route('collections.show', $parent));
        $this->assertDatabaseHas('collections', ['id' => $child1->id, 'display_order' => 1]);
        $this->assertDatabaseHas('collections', ['id' => $child2->id, 'display_order' => 2]);
    }

    public function test_move_down_at_bottom_has_no_effect(): void
    {
        $parent = Collection::factory()->create();
        $child1 = Collection::factory()->create(['parent_id' => $parent->id, 'display_order' => 1]);
        $child2 = Collection::factory()->create(['parent_id' => $parent->id, 'display_order' => 2]);

        $response = $this->post(route('collections.move-down', $child2));

        $response->assertRedirect(route('collections.show', $parent));
        $this->assertDatabaseHas('collections', ['id' => $child1->id, 'display_order' => 1]);
        $this->assertDatabaseHas('collections', ['id' => $child2->id, 'display_order' => 2]);
    }

    public function test_move_without_parent_redirects_to_index(): void
    {
        $collection1 = Collection::factory()->create(['parent_id' => null, 'display_order' => 1]);
        Collection::factory()->create([
            'parent_id' => null,
            'display_order' => 2,
            'context_id' => $collection1->context_id,
            'language_id' => $collection1->language_id,
        ]);

        $response = $this->post(route('collections.move-down', $collection1));

        $response->assertRedirect(route('collections.index'));
    }
}
