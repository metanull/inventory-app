<?php

namespace Tests\Feature\Api\Context;

use App\Models\Context;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class DestroyTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createDataUser();
        $this->actingAs($this->user);
    }

    /**
     * Authentication: destroy allows authenticated users.
     */
    public function test_destroy_allows_authenticated_users()
    {
        $context = Context::factory()->create();

        $response = $this->deleteJson(route('context.destroy', $context));
        $response->assertNoContent();
    }

    /**
     * Process: destroy deletes a row.
     */
    public function test_destroy_deletes_a_row()
    {
        $context = Context::factory()->create();

        $response = $this->deleteJson(route('context.destroy', $context));
        $response->assertNoContent();
        $this->assertDatabaseMissing('contexts', ['id' => $context->id]);
    }

    /**
     * Response: destroy returns no content on success.
     */
    public function test_destroy_returns_no_content_on_success()
    {
        $context = Context::factory()->create();

        $response = $this->deleteJson(route('context.destroy', $context));
        $response->assertNoContent();
    }

    /**
     * Response: destroy returns not found when record does not exist.
     */
    public function test_destroy_returns_not_found_when_record_does_not_exist()
    {
        $response = $this->deleteJson(route('context.destroy', 'non-existent-id'));
        $response->assertNotFound();
    }

    /**
     * Response: destroy returns the expected structure (empty).
     */
    public function test_destroy_returns_the_expected_structure()
    {
        $context = Context::factory()->create();

        $response = $this->deleteJson(route('context.destroy', $context));
        $response->assertNoContent();
    }

    /**
     * Response: destroy returns the expected data (none).
     */
    public function test_destroy_returns_the_expected_data()
    {
        $context = Context::factory()->create();

        $response = $this->deleteJson(route('context.destroy', $context));
        $response->assertNoContent();
    }
}
