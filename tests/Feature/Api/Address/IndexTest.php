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
                        'languages' => [
                            '*' => [
                                'id',
                                'name',
                                'address',
                                'description',
                            ],
                        ],
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);

        $this->assertCount(3, $response->json('data'));
    }
}
