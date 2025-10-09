<?php

namespace Tests\Feature\Api\AddressTranslation;

use App\Models\AddressTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class ShowTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createVisitorUser();
        $this->actingAs($this->user);
    }

    public function test_can_show_address_translation(): void
    {
        $address = \App\Models\Address::factory()->withoutTranslations()->create();
        $language = \App\Models\Language::factory()->create();
        $addressTranslation = AddressTranslation::factory()->create([
            'address_id' => $address->id,
            'language_id' => $language->id,
        ]);

        $response = $this->getJson(route('address-translation.show', ['address_translation' => $addressTranslation->id]));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'address_id',
                    'language_id',
                    'address',
                    'description',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.id', $addressTranslation->id)
            ->assertJsonPath('data.address_id', $addressTranslation->address_id)
            ->assertJsonPath('data.language_id', $addressTranslation->language_id);
    }

    public function test_show_returns_not_found_for_non_existent_address_translation(): void
    {
        $response = $this->getJson(route('address-translation.show', ['address_translation' => 'non-existent-id']));

        $response->assertNotFound();
    }
}
