<?php

namespace Tests\Feature\Api\Partner;

use App\Models\Partner;
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

    public function test_show_allows_authenticated_users(): void
    {
        $partner = Partner::factory()->create();

        $response = $this->getJson(route('partner.show', $partner));

        $response->assertOk();
    }

    public function test_show_returns_one_row(): void
    {
        $partner = Partner::factory()->create();

        $response = $this->getJson(route('partner.show', $partner));

        $response->assertOk();
        $response->assertJsonPath('data.id', $partner->id);
    }

    public function test_show_returns_ok_on_success(): void
    {
        $partner = Partner::factory()->create();

        $response = $this->getJson(route('partner.show', $partner));

        $response->assertOk();
    }

    public function test_show_returns_not_found_when_record_does_not_exist(): void
    {
        $response = $this->getJson(route('partner.show', 999));

        $response->assertNotFound();
    }

    public function test_show_returns_the_default_structure_without_relations(): void
    {
        $partner = Partner::factory()->create();

        $response = $this->getJson(route('partner.show', $partner));

        $response->assertJsonStructure([
            'data' => [
                'id',
                'internal_name',
                'backward_compatibility',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    public function test_show_returns_the_expected_structure_with_all_relations_loaded(): void
    {
        $partner = Partner::factory()->create();

        $response = $this->getJson(route('partner.show', [$partner, 'include' => 'country']));

        $response->assertJsonStructure([
            'data' => [
                'id',
                'internal_name',
                'backward_compatibility',
                'type',
                'country',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    public function test_show_returns_the_expected_data(): void
    {
        $partner = Partner::factory()->create();

        $response = $this->getJson(route('partner.show', $partner));

        $response->assertOk();
        $response->assertJsonPath('data.id', $partner->id);
        $response->assertJsonPath('data.name', $partner->name);
        $response->assertJsonPath('data.created_at', $partner->created_at->toISOString());
        $response->assertJsonPath('data.updated_at', $partner->updated_at->toISOString());
    }
}
