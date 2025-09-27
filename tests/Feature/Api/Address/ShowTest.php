<?php

namespace Tests\Feature\Api\Address;

use App\Models\Address;
use App\Models\Country;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_show_returns_the_default_structure_without_relations(): void
    {
        Language::factory(3)->create();
        Country::factory(2)->create();
        $address = Address::factory()->create();

        $response = $this->getJson(route('address.show', $address));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'internal_name',
                    'country_id',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.id', $address->id)
            ->assertJsonPath('data.internal_name', $address->internal_name)
            ->assertJsonPath('data.country_id', $address->country_id);
    }

    public function test_shows_404_for_nonexistent_address(): void
    {
        $response = $this->getJson(route('address.show', 'nonexistent-id'));

        $response->assertNotFound();
    }

    public function test_show_returns_the_expected_structure_with_all_relations_loaded(): void
    {
        Language::factory(3)->create();
        Country::factory(2)->create();
        $address = Address::factory()->create();

        $response = $this->getJson(route('address.show', [$address, 'include' => 'translations']));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'translations' => [
                        '*' => [
                            'id',
                            'address_id',
                            'language_id',
                            'address',
                            'description',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                ],
            ]);
    }

    public function test_address_response_includes_all_required_fields(): void
    {
        Language::factory(3)->create();
        Country::factory(2)->create();
        $address = Address::factory()->create();

        $response = $this->getJson(route('address.show', [$address, 'include' => 'translations']));

        $response->assertOk();

        $data = $response->json('data');
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('internal_name', $data);
        $this->assertArrayHasKey('country_id', $data);
        $this->assertArrayHasKey('translations', $data);
        $this->assertArrayHasKey('created_at', $data);
        $this->assertArrayHasKey('updated_at', $data);
    }

    public function test_show_accepts_include_parameter_for_translations(): void
    {
        Language::factory(3)->create();
        Country::factory(2)->create();
        $address = Address::factory()->create();

        $response = $this->getJson(route('address.show', [$address, 'include' => 'translations']));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'internal_name',
                    'country_id',
                    'translations' => [
                        '*' => [
                            'id',
                            'address_id',
                            'language_id',
                            'address',
                            'description',
                        ],
                    ],
                    'created_at',
                    'updated_at',
                ],
            ]);
    }
}
