<?php

namespace Tests\Feature\Api\Country;

use App\Models\Country;
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
        $this->actingAs($this->user);
    }

    /**
     * Response: Assert destroy returns no content on success.
     */
    public function test_destroy_returns_no_content_on_success()
    {
        $country = Country::factory()->create();

        $response = $this->deleteJson(route('country.destroy', $country));

        $response->assertNoContent();
    }

    /**
     * Response: Assert destroy returns not found when record does not exist.
     */
    public function test_destroy_returns_not_found_when_record_does_not_exist()
    {
        $response = $this->deleteJson(route('country.destroy', ['country' => 'non-existent-id']));

        $response->assertNotFound();
    }

    /**
     * Authentication: Assert destroy allows authenticated users.
     */
    public function test_destroy_allows_authenticated_users()
    {
        $country = Country::factory()->create();

        $response = $this->deleteJson(route('country.destroy', $country));

        $response->assertNoContent();
    }

    /**
     * Process: Assert destroy deletes a row.
     */
    public function test_destroy_deletes_a_row()
    {
        $country = Country::factory()->create();

        $this->deleteJson(route('country.destroy', $country));

        $this->assertDatabaseMissing('countries', ['id' => $country->id]);
    }
}
