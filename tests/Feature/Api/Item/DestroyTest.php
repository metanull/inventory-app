<?php

namespace Tests\Feature\Api\Item;

use App\Enums\Permission;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class DestroyTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUserWith(Permission::dataOperations());
        $this->actingAs($this->user);
    }

    public function test_destroy_allows_authenticated_users(): void
    {
        $item = Item::factory()->create();
        $response = $this->deleteJson(route('item.destroy', $item->id));
        $response->assertNoContent();
    }

    public function test_destroy_returns_not_found_response_when_not_found(): void
    {
        $response = $this->deleteJson(route('item.destroy', 'nonexistent'));
        $response->assertNotFound();
    }

    public function test_destroy_deletes_a_row(): void
    {
        $item = Item::factory()->create();

        $this->deleteJson(route('item.destroy', $item->id));

        $this->assertDatabaseMissing('items', ['id' => $item->id]);
    }

    public function test_destroy_returns_no_content_on_success(): void
    {
        $item = Item::factory()->create();

        $response = $this->deleteJson(route('item.destroy', $item->id));

        $response->assertNoContent();
    }
}
