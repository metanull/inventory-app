<?php

namespace Tests\Feature\Api\AddressTranslation;

use App\Models\AddressTranslation;
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

    public function test_can_destroy_address_translation(): void
    {
        $address = \App\Models\Address::factory()->withoutTranslations()->create();
        $language = \App\Models\Language::factory()->create();
        $addressTranslation = AddressTranslation::factory()->create([
            'address_id' => $address->id,
            'language_id' => $language->id,
        ]);

        $response = $this->deleteJson(route('address-translation.destroy', ['address_translation' => $addressTranslation->id]));

        $response->assertNoContent();

        $this->assertDatabaseMissing('address_translations', [
            'id' => $addressTranslation->id,
        ]);
    }

    public function test_destroy_returns_not_found_for_non_existent_address_translation(): void
    {
        $response = $this->deleteJson(route('address-translation.destroy', ['address_translation' => 'non-existent-id']));

        $response->assertNotFound();
    }
}
