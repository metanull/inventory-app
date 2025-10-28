<?php

namespace Tests\Feature\Api\Partner;

use App\Enums\Permission;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class DestroyTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUserWith(Permission::dataOperations());
        $this->actingAs($this->user);
    }

    public function test_destroy_allows_authenticated_users(): void
    {
        $partner = Partner::factory()->create();

        $response = $this->deleteJson(route('partner.destroy', $partner));

        $response->assertNoContent();
    }

    public function test_destroy_deletes_a_row(): void
    {
        $partner = Partner::factory()->create();

        $response = $this->deleteJson(route('partner.destroy', $partner));

        $response->assertNoContent();
        $this->assertDatabaseMissing('partners', ['id' => $partner->id]);
    }

    public function test_destroy_returns_no_content_on_success(): void
    {
        $partner = Partner::factory()->create();

        $response = $this->deleteJson(route('partner.destroy', $partner));

        $response->assertNoContent();
    }

    public function test_destroy_returns_not_found_when_record_does_not_exist(): void
    {
        $response = $this->deleteJson(route('partner.destroy', 999));

        $response->assertNotFound();
    }

    public function test_destroy_returns_the_expected_structure(): void
    {
        $partner = Partner::factory()->create();

        $response = $this->deleteJson(route('partner.destroy', $partner));

        $response->assertNoContent();
        $this->assertEmpty($response->getContent());
    }

    public function test_destroy_returns_the_expected_data(): void
    {
        $partner = Partner::factory()->create();

        $response = $this->deleteJson(route('partner.destroy', $partner));

        $response->assertNoContent();
        $this->assertEmpty($response->getContent());
        $this->assertDatabaseMissing('partners', ['id' => $partner->id]);
    }
}
