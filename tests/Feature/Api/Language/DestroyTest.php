<?php

namespace Tests\Feature\Api\Language;

use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class DestroyTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;
    use WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createDataUser();
        $this->actingAs($this->user);
    }

    public function test_destroy_allows_authenticated_users(): void
    {
        $language = Language::factory()->create();
        $response = $this->deleteJson(route('language.destroy', $language->id));
        $response->assertNoContent();
    }

    public function test_destroy_returns_not_found_response_when_not_found(): void
    {
        $response = $this->deleteJson(route('language.destroy', 'NON_EXISTENT'));
        $response->assertNotFound();
    }

    public function test_destroy_deletes_a_row(): void
    {
        $language = Language::factory()->create();
        $response = $this->deleteJson(route('language.destroy', $language->id));
        $this->assertDatabaseMissing('languages', ['id' => 'TST']);
    }

    public function test_destroy_returns_no_content_on_success(): void
    {
        $language = Language::factory()->create();
        $response = $this->deleteJson(route('language.destroy', $language->id));
        $response->assertNoContent();
    }
}
