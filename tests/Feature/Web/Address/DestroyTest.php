<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Address;

use App\Models\Address;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class DestroyTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_destroy_deletes_address_and_redirects(): void
    {
        $address = Address::factory()->create();

        $response = $this->delete(route('addresses.destroy', $address));
        $response->assertRedirect(route('addresses.index'));
        $this->assertDatabaseMissing('addresses', [
            'id' => $address->id,
        ]);
    }
}
