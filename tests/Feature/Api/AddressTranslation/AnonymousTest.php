<?php

namespace Tests\Feature\Api\AddressTranslation;

use App\Models\AddressTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnonymousTest extends TestCase
{
    use RefreshDatabase;

    public function test_anonymous_cannot_access_address_translations_index()
    {
        $response = $this->getJson(route('address-translation.index'));
        $response->assertUnauthorized();
    }

    public function test_anonymous_cannot_access_address_translation_show()
    {
        $address = \App\Models\Address::factory()->withoutTranslations()->create();
        $language = \App\Models\Language::factory()->create();
        $translation = AddressTranslation::factory()->create([
            'address_id' => $address->id,
            'language_id' => $language->id,
        ]);
        $response = $this->getJson(route('address-translation.show', $translation->id));
        $response->assertUnauthorized();
    }

    public function test_anonymous_cannot_create_address_translation()
    {
        $address = \App\Models\Address::factory()->withoutTranslations()->create();
        $language = \App\Models\Language::factory()->create();
        $data = AddressTranslation::factory()->make([
            'address_id' => $address->id,
            'language_id' => $language->id,
        ])->toArray();
        $response = $this->postJson(route('address-translation.store'), $data);
        $response->assertUnauthorized();
    }

    public function test_anonymous_cannot_update_address_translation()
    {
        $address = \App\Models\Address::factory()->withoutTranslations()->create();
        $language = \App\Models\Language::factory()->create();
        $translation = AddressTranslation::factory()->create([
            'address_id' => $address->id,
            'language_id' => $language->id,
        ]);

        $newAddress = \App\Models\Address::factory()->withoutTranslations()->create();
        $newLanguage = \App\Models\Language::factory()->create();
        $data = AddressTranslation::factory()->make([
            'address_id' => $newAddress->id,
            'language_id' => $newLanguage->id,
        ])->toArray();
        $response = $this->putJson(route('address-translation.update', $translation->id), $data);
        $response->assertUnauthorized();
    }

    public function test_anonymous_cannot_delete_address_translation()
    {
        $address = \App\Models\Address::factory()->withoutTranslations()->create();
        $language = \App\Models\Language::factory()->create();
        $translation = AddressTranslation::factory()->create([
            'address_id' => $address->id,
            'language_id' => $language->id,
        ]);
        $response = $this->deleteJson(route('address-translation.destroy', $translation->id));
        $response->assertUnauthorized();
    }
}
