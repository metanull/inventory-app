<?php

namespace Tests\Feature\Api\Detail;

use App\Models\Detail;
use App\Models\Item;
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

    public function test_destroy_allows_authenticated_users(): void
    {
        $detail = Detail::factory()->for(Item::factory())->create();
        $response_authenticated = $this->deleteJson(route('detail.destroy', $detail->id));
        $response_authenticated->assertNoContent();
    }

    public function test_destroy_returns_not_found_response_when_not_found(): void
    {
        $response = $this->deleteJson(route('detail.destroy', 'nonexistent'));

        $response->assertNotFound();
    }

    public function test_destroy_deletes_a_row(): void
    {
        $detail = Detail::factory()->for(Item::factory())->create();

        $this->deleteJson(route('detail.destroy', $detail->id));

        $this->assertDatabaseMissing('details', ['id' => $detail->id]);
    }

    public function test_destroy_returns_no_content_on_success(): void
    {
        $detail = Detail::factory()->for(Item::factory())->create();

        $response = $this->deleteJson(route('detail.destroy', $detail->id));

        $response->assertNoContent();
    }
}
