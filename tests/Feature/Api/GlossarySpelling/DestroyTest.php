<?php

namespace Tests\Feature\Api\GlossarySpelling;

use App\Models\GlossarySpelling;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class DestroyTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createDataUser();
        $this->actingAs($this->user, 'sanctum');
    }

    /**
     * Authentication: destroy allows authenticated users.
     */
    public function test_destroy_allows_authenticated_users()
    {
        $spelling = GlossarySpelling::factory()->create();

        $response = $this->deleteJson(route('glossary-spelling.destroy', $spelling));
        $response->assertNoContent();
    }

    /**
     * Process: destroy deletes the spelling.
     */
    public function test_destroy_deletes_the_spelling()
    {
        $spelling = GlossarySpelling::factory()->create();
        $spellingId = $spelling->id;

        $response = $this->deleteJson(route('glossary-spelling.destroy', $spelling));
        $response->assertNoContent();

        $this->assertDatabaseMissing('glossary_spellings', ['id' => $spellingId]);
    }

    /**
     * Response: destroy returns no content on success.
     */
    public function test_destroy_returns_no_content_on_success()
    {
        $spelling = GlossarySpelling::factory()->create();

        $response = $this->deleteJson(route('glossary-spelling.destroy', $spelling));
        $response->assertNoContent();
    }
}
