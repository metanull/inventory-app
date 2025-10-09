<?php

namespace Tests\Feature\Api;

use App\Enums\Permission;
use App\Models\Collection;
use App\Models\Context;
use App\Models\Country;
use App\Models\Item;
use App\Models\Language;
use App\Models\Partner;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Test that API routes are properly protected with permission checks.
 * This ensures API security matches the web interface authorization.
 */
class ApiPermissionControlTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user WITHOUT auto-granting permissions for these tests
        // We'll use Sanctum::actingAs() instead of $this->actingAs() to bypass auto-permission-granting
        $this->user = User::factory()->create();
    }

    /**
     * Helper to authenticate as a user WITHOUT auto-granting permissions.
     * Uses Sanctum::actingAs instead of $this->actingAs to bypass the TestCase override.
     */
    private function authenticateWithoutAutoPermissions(User $user): void
    {
        Sanctum::actingAs($user);
    }

    /**
     * Test that GET requests require VIEW_DATA permission
     */
    public function test_api_get_requests_require_view_data_permission(): void
    {
        // Create test data
        $context = Context::factory()->create();
        $country = Country::factory()->create();
        $language = Language::factory()->create();
        $project = Project::factory()->create();
        $partner = Partner::factory()->create(['country_id' => $country->id]);
        $tag = Tag::factory()->create();
        $item = Item::factory()->create([
            'partner_id' => $partner->id,
            'country_id' => $country->id,
            'project_id' => $project->id,
        ]);
        $collection = Collection::factory()->create();

        // User without permission
        $this->authenticateWithoutAutoPermissions($this->user);

        $response = $this->getJson(route('context.index'));
        $response->assertStatus(403);

        $response = $this->getJson(route('context.show', $context));
        $response->assertStatus(403);

        $response = $this->getJson(route('country.index'));
        $response->assertStatus(403);

        $response = $this->getJson(route('language.index'));
        $response->assertStatus(403);

        $response = $this->getJson(route('project.index'));
        $response->assertStatus(403);

        $response = $this->getJson(route('partner.index'));
        $response->assertStatus(403);

        $response = $this->getJson(route('tag.index'));
        $response->assertStatus(403);

        $response = $this->getJson(route('item.index'));
        $response->assertStatus(403);

        $response = $this->getJson(route('collection.index'));
        $response->assertStatus(403);

        // Give VIEW_DATA permission
        $this->user->givePermissionTo(Permission::VIEW_DATA->value);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Now should succeed
        $response = $this->getJson(route('context.index'));
        $response->assertStatus(200);

        $response = $this->getJson(route('context.show', $context));
        $response->assertStatus(200);

        $response = $this->getJson(route('country.index'));
        $response->assertStatus(200);

        $response = $this->getJson(route('item.index'));
        $response->assertStatus(200);
    }

    /**
     * Test that POST requests require CREATE_DATA permission
     */
    public function test_api_post_requests_require_create_data_permission(): void
    {
        // User without permission
        $this->authenticateWithoutAutoPermissions($this->user);

        $response = $this->postJson(route('context.store'), [
            'internal_name' => 'Test Context',
        ]);
        $response->assertStatus(403);

        $response = $this->postJson(route('tag.store'), [
            'internal_name' => 'Test Tag',
        ]);
        $response->assertStatus(403);

        // Give CREATE_DATA permission
        $this->user->givePermissionTo(Permission::CREATE_DATA->value);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Now should succeed
        $response = $this->postJson(route('context.store'), [
            'internal_name' => 'Test Context',
        ]);
        $response->assertStatus(201);

        $response = $this->postJson(route('tag.store'), [
            'internal_name' => 'Test Tag',
            'description' => 'Test tag description',
        ]);
        $response->assertStatus(201);
    }

    /**
     * Test that PATCH/PUT requests require UPDATE_DATA permission
     */
    public function test_api_patch_put_requests_require_update_data_permission(): void
    {
        $context = Context::factory()->create();
        $tag = Tag::factory()->create();

        // User without permission
        $this->authenticateWithoutAutoPermissions($this->user);

        $response = $this->patchJson(route('context.update', $context), [
            'internal_name' => 'Updated Context',
        ]);
        $response->assertStatus(403);

        $response = $this->putJson(route('tag.update', $tag), [
            'internal_name' => 'Updated Tag',
        ]);
        $response->assertStatus(403);

        // Give UPDATE_DATA permission
        $this->user->givePermissionTo(Permission::UPDATE_DATA->value);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Now should succeed
        $response = $this->patchJson(route('context.update', $context), [
            'internal_name' => 'Updated Context',
        ]);
        $response->assertStatus(200);

        $response = $this->putJson(route('tag.update', $tag), [
            'internal_name' => $tag->internal_name,
            'description' => 'Updated Tag Description',
        ]);
        $response->assertStatus(200);
    }

    /**
     * Test that DELETE requests require DELETE_DATA permission
     */
    public function test_api_delete_requests_require_delete_data_permission(): void
    {
        $context = Context::factory()->create();
        $tag = Tag::factory()->create();

        // User without permission
        $this->authenticateWithoutAutoPermissions($this->user);

        $response = $this->deleteJson(route('context.destroy', $context));
        $response->assertStatus(403);

        // Give DELETE_DATA permission
        $this->user->givePermissionTo(Permission::DELETE_DATA->value);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Now should succeed
        $response = $this->deleteJson(route('context.destroy', $context));
        $response->assertStatus(204);

        // Create another for second test
        $anotherTag = Tag::factory()->create();
        $response = $this->deleteJson(route('tag.destroy', $anotherTag));
        $response->assertStatus(204);
    }

    /**
     * Test that API returns proper 403 JSON response (not HTML redirect)
     */
    public function test_api_returns_json_403_not_html_redirect(): void
    {
        // User without VIEW_DATA permission
        $this->authenticateWithoutAutoPermissions($this->user);

        $response = $this->getJson(route('context.index'));

        $response->assertStatus(403);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'message',
            'reason',
        ]);
        $response->assertJson([
            'message' => 'Forbidden.',
        ]);

        // Verify it does NOT redirect
        $this->assertNull($response->headers->get('Location'));
    }

    /**
     * Test that unauthenticated API requests return 401
     */
    public function test_api_unauthenticated_requests_return_401(): void
    {
        $response = $this->getJson(route('context.index'));
        $response->assertStatus(401);

        $response = $this->getJson(route('item.index'));
        $response->assertStatus(401);
    }

    /**
     * Test that user with all permissions can perform all operations
     */
    public function test_user_with_all_permissions_can_perform_all_operations(): void
    {
        // Give all data permissions
        $this->user->givePermissionTo([
            Permission::VIEW_DATA->value,
            Permission::CREATE_DATA->value,
            Permission::UPDATE_DATA->value,
            Permission::DELETE_DATA->value,
        ]);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Test all operations
        $response = $this->actingAs($this->user, 'sanctum')->getJson(route('context.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user, 'sanctum')->postJson(route('context.store'), [
            'internal_name' => 'Test Context',
        ]);
        $response->assertStatus(201);
        $context = Context::where('internal_name', 'Test Context')->first();

        $response = $this->actingAs($this->user, 'sanctum')->patchJson(route('context.update', $context), [
            'internal_name' => 'Updated Context',
        ]);
        $response->assertStatus(200);

        $response = $this->actingAs($this->user, 'sanctum')->deleteJson(route('context.destroy', $context));
        $response->assertStatus(204);
    }

    /**
     * Test that permission checks are enforced for special routes
     */
    public function test_special_routes_require_appropriate_permissions(): void
    {
        $context = Context::factory()->create();
        $project = Project::factory()->create();

        // User without UPDATE_DATA permission
        $this->authenticateWithoutAutoPermissions($this->user);

        $response = $this->patchJson(route('context.setDefault', $context), [
            'is_default' => true,
        ]);
        $response->assertStatus(403);

        $response = $this->patchJson(route('project.setLaunched', $project), [
            'launched' => true,
        ]);
        $response->assertStatus(403);

        // Give UPDATE_DATA permission
        $this->user->givePermissionTo(Permission::UPDATE_DATA->value);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Now should succeed
        $response = $this->patchJson(route('context.setDefault', $context), [
            'is_default' => true,
        ]);
        $response->assertStatus(200);
    }

    /**
     * Test that markdown utility routes don't require authentication
     * (they are stateless utility endpoints)
     */
    public function test_markdown_routes_do_not_require_authentication(): void
    {
        // Unauthenticated access should work
        $response = $this->postJson(route('markdown.toHtml'), [
            'markdown' => '# Test',
        ]);
        $response->assertStatus(200);

        $response = $this->getJson(route('markdown.allowedElements'));
        $response->assertStatus(200);
    }

    /**
     * Test that public info endpoints don't require authentication
     */
    public function test_public_info_endpoints_do_not_require_authentication(): void
    {
        $response = $this->getJson(route('info.index'));
        $response->assertStatus(200);

        $response = $this->getJson(route('info.health'));
        $response->assertStatus(200);

        $response = $this->getJson(route('info.version'));
        $response->assertStatus(200);
    }

    /**
     * Test that mobile auth routes are public (no auth required for login flow)
     */
    public function test_mobile_auth_routes_are_public(): void
    {
        // These should not require authentication
        $response = $this->postJson(route('token.acquire'), [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);
        // Will fail validation but should not be 401
        $this->assertNotEquals(401, $response->status());
    }
}
