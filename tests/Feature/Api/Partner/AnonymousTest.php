<?php

namespace Tests\Feature\Api\Partner;

use App\Models\Country;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AnonymousTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_index_forbids_anonymous_access(): void
    {
        $response = $this->getJson(route('partner.index'));
        $response->assertUnauthorized();
    }

    public function test_show_forbids_anonymous_access(): void
    {
        $partner = Partner::factory()->withCountry()->create();

        $response = $this->getJson(route('partner.show', $partner->id));
        $response->assertUnauthorized();
    }

    public function test_store_forbids_anonymous_access(): void
    {
        $response = $this->postJson(route('partner.store'), [
            'internal_name' => 'Test Partner',
            'backward_compatibility' => 'TP',
            'country_id' => Country::factory()->create()->id,
            'type' => 'museum',
        ]);
        $response->assertUnauthorized();
    }

    public function test_update_forbids_anonymous_access(): void
    {
        $partner = Partner::factory()->withCountry()->create();

        $response = $this->putJson(route('partner.update', $partner->id), [
            'internal_name' => 'Updated Partner',
            'backward_compatibility' => 'UP',
            'country_id' => Country::factory()->create()->id,
            'type' => 'museum',
        ]);
        $response->assertUnauthorized();
    }

    public function test_destroy_forbids_anonymous_access(): void
    {
        $partner = Partner::factory()->withCountry()->create();

        $response = $this->deleteJson(route('partner.destroy', $partner->id));
        $response->assertUnauthorized();
    }
}
