<?php

namespace Tests\Feature\Api\GlossaryTranslation;

use App\Models\GlossaryTranslation;
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
        $translation = GlossaryTranslation::factory()->create();

        $response = $this->deleteJson(route('glossary-translation.destroy', $translation));
        $response->assertNoContent();
    }

    /**
     * Process: destroy deletes the translation.
     */
    public function test_destroy_deletes_the_translation()
    {
        $translation = GlossaryTranslation::factory()->create();
        $translationId = $translation->id;

        $response = $this->deleteJson(route('glossary-translation.destroy', $translation));
        $response->assertNoContent();

        $this->assertDatabaseMissing('glossary_translations', ['id' => $translationId]);
    }

    /**
     * Response: destroy returns no content on success.
     */
    public function test_destroy_returns_no_content_on_success()
    {
        $translation = GlossaryTranslation::factory()->create();

        $response = $this->deleteJson(route('glossary-translation.destroy', $translation));
        $response->assertNoContent();
    }
}
