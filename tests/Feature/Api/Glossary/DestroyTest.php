<?php

namespace Tests\Feature\Api\Glossary;

use App\Enums\Permission;
use App\Models\Glossary;
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
        $this->actingAs($this->user, 'sanctum');
    }

    /**
     * Authentication: destroy allows authenticated users.
     */
    public function test_destroy_allows_authenticated_users()
    {
        $glossary = Glossary::factory()->create();

        $response = $this->deleteJson(route('glossary.destroy', $glossary));
        $response->assertNoContent();
    }

    /**
     * Process: destroy deletes the glossary.
     */
    public function test_destroy_deletes_the_glossary()
    {
        $glossary = Glossary::factory()->create();
        $glossaryId = $glossary->id;

        $response = $this->deleteJson(route('glossary.destroy', $glossary));
        $response->assertNoContent();

        $this->assertDatabaseMissing('glossaries', ['id' => $glossaryId]);
    }

    /**
     * Response: destroy returns no content on success.
     */
    public function test_destroy_returns_no_content_on_success()
    {
        $glossary = Glossary::factory()->create();

        $response = $this->deleteJson(route('glossary.destroy', $glossary));
        $response->assertNoContent();
    }
}
