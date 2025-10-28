<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Collections;

use App\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class DestroyTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsDataUser();
    }

    public function test_destroy_deletes_and_redirects(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->delete(route('collections.destroy', $collection));
        $response->assertRedirect(route('collections.index'));
        $this->assertDatabaseMissing('collections', ['id' => $collection->id]);
    }
}
