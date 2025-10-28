<?php

namespace Tests\Feature\Api\Partner;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class IndexTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createVisitorUser();
        $this->actingAs($this->user);
    }

    public function test_index_allows_authenticated_users(): void
    {
        Partner::factory()->count(3)->create();

        $response = $this->getJson(route('partner.index'));

        $response->assertOk();
    }

    public function test_index_returns_all_rows(): void
    {
        $partners = Partner::factory()->count(3)->create();

        $response = $this->getJson(route('partner.index'));

        $response->assertOk();
        $response->assertJsonPath('meta.total', 3);
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
                    'internal_name',
                    'backward_compatibility',
                    'created_at',
                    'updated_at',
                ],
            ],
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta' => [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);
    }

    public function test_index_returns_the_expected_data(): void
    {
        $partners = Partner::factory()->count(3)->create();

        $response = $this->getJson(route('partner.index'));

        $response->assertOk();
        $response->assertJsonPath('meta.total', 3);

        // Check that all partners are present in the response
        foreach ($partners as $partner) {
            $response->assertJsonPath("data.{$partners->search($partner)}.id", $partner->id);
            $response->assertJsonPath("data.{$partners->search($partner)}.internal_name", $partner->internal_name);
        }
    }
}
