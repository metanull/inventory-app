<?php

namespace Tests\Feature\Api\Address;

use App\Models\Address;
use App\Models\Country;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_get_empty_address_list(): void
    {
        $response = $this->getJson(route('address.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'data',
            ])
            ->assertJsonPath('data', []);
    }

    public function test_can_get_address_list_with_data(): void
    {
        Language::factory(3)->create();
        Country::factory(2)->create();
        $addresses = Address::factory(3)->create();

        $response = $this->getJson(route('address.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
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
                                'created_at',
                                'updated_at',
                            ],
                        ],
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_index_returns_paginated_structure(): void
    {
        Language::factory(3)->create();
        Country::factory(2)->create();
        Address::factory(5)->create();

        $response = $this->getJson(route('address.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'links' => [
                    'first', 'last', 'prev', 'next',
                ],
                'meta' => [
                    'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
                ],
            ]);
    }

    public function test_index_accepts_pagination_parameters(): void
    {
        Language::factory(3)->create();
        Country::factory(2)->create();
        Address::factory(5)->create();

        $response = $this->getJson(route('address.index', ['per_page' => 2, 'page' => 1]));

        $response->assertOk()
            ->assertJsonStructure([
                'meta' => [
                    'per_page',
                    'current_page',
                    'total',
                    'last_page',
                ],
                'data' => [],
            ])
            ->assertJsonPath('meta.current_page', 1);

        $this->assertCount(2, $response->json('data'));
        $this->assertIsInt($response->json('meta.per_page'));
        $this->assertGreaterThan(0, $response->json('meta.per_page'));
    }

    public function test_index_accepts_include_parameter(): void
    {
        Language::factory(3)->create();
        Country::factory(2)->create();
        Address::factory(2)->create();

        $response = $this->getJson(route('address.index', ['include' => 'translations']));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
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
                ],
            ]);
    }

    public function test_index_validates_pagination_parameters(): void
    {
        $response = $this->getJson(route('address.index', ['per_page' => 0]));
        $response->assertUnprocessable();

        $response = $this->getJson(route('address.index', ['per_page' => 101]));
        $response->assertUnprocessable();

        $response = $this->getJson(route('address.index', ['page' => 0]));
        $response->assertUnprocessable();
    }
}
