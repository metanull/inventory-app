<?php

namespace Tests\Feature\Api\Address;

use App\Models\Address;
use App\Models\Country;
use App\Models\Language;
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

    public function test_can_delete_address(): void
    {
        Language::factory(3)->create();
        Country::factory(2)->create();
        $address = Address::factory()->create();

        $response = $this->deleteJson(route('address.destroy', $address));

        $response->assertNoContent();

        $this->assertDatabaseMissing('addresses', [
            'id' => $address->id,
        ]);
    }

    public function test_delete_returns_404_for_nonexistent_address(): void
    {
        $response = $this->deleteJson(route('address.destroy', 'nonexistent-id'));

        $response->assertNotFound();
    }

    public function test_deleting_address_also_deletes_language_relations(): void
    {
        Language::factory(3)->create();
        Country::factory(2)->create();
        $address = Address::factory()->create();

        // Ensure the address has language relations
        $this->assertDatabaseHas('address_language', [
            'address_id' => $address->id,
        ]);

        $response = $this->deleteJson(route('address.destroy', $address));

        $response->assertNoContent();

        // Verify the address and its language relations are deleted
        $this->assertDatabaseMissing('addresses', [
            'id' => $address->id,
        ]);

        $this->assertDatabaseMissing('address_language', [
            'address_id' => $address->id,
        ]);
    }

    public function test_can_delete_multiple_addresses(): void
    {
        Language::factory(3)->create();
        Country::factory(2)->create();
        $address1 = Address::factory()->create();
        $address2 = Address::factory()->create();

        // Delete first address
        $response1 = $this->deleteJson(route('address.destroy', $address1));
        $response1->assertNoContent();

        // Delete second address
        $response2 = $this->deleteJson(route('address.destroy', $address2));
        $response2->assertNoContent();

        // Verify both are deleted
        $this->assertDatabaseMissing('addresses', ['id' => $address1->id]);
        $this->assertDatabaseMissing('addresses', ['id' => $address2->id]);
    }
}
