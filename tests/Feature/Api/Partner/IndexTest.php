<?php

namespace Tests\Feature\Api\Partner;

use App\Models\Partner;
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

    public function test_index_allows_authenticated_users(): void
    {
        Partner::factory()->count(3)->create();

        $response = $this->getJson(route('partner.index'));

        $response->assertOk();
    }

    public function test_index_forbids_anonymous_access(): void
    {
        Partner::factory()->count(3)->create();

        $response = $this->withHeaders(['Authorization' => ''])
            ->getJson(route('partner.index'));

        $response->assertUnauthorized();
    }

    public function test_index_returns_all_rows(): void
    {
        $partners = Partner::factory()->count(3)->create();

        $response = $this->getJson(route('partner.index'));

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }

    public function test_index_returns_ok_on_success(): void
    {
        Partner::factory()->count(3)->create();

        $response = $this->getJson(route('partner.index'));

        $response->assertOk();
    }

    public function test_index_returns_the_expected_structure(): void
    {
        Partner::factory()->count(3)->create();

        $response = $this->getJson(route('partner.index'));

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'created_at',
                    'updated_at',
                ]
            ]
        ]);
        $response->assertJsonCount(3, 'data');
    }

    public function test_index_returns_the_expected_data(): void
    {
        $partners = Partner::factory()->count(3)->create();

        $response = $this->getJson(route('partner.index'));

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
        
        // Check that all partners are present in the response
        foreach ($partners as $partner) {
            $response->assertJsonPath("data.{$partners->search($partner)}.id", $partner->id);
            $response->assertJsonPath("data.{$partners->search($partner)}.name", $partner->name);
        }
    }
}
