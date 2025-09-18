<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Item;

use App\Models\Item;
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

    public function test_destroy_deletes_record_and_redirects(): void
    {
        $item = Item::factory()->create();

        $response = $this->delete(route('items.destroy', $item));
        $response->assertRedirect(route('items.index'));
        $this->assertDatabaseMissing('items', ['id' => $item->id]);
    }
}
