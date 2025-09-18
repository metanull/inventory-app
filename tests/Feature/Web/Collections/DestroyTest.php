<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Collections;

use App\Models\Collection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_destroy_deletes_and_redirects(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->delete(route('collections.destroy', $collection));
        $response->assertRedirect(route('collections.index'));
        $this->assertDatabaseMissing('collections', ['id' => $collection->id]);
    }
}
