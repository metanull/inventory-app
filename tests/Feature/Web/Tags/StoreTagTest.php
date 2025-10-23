<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Tags;

use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class StoreTagTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    public function test_store_creates_tag_with_valid_data(): void
    {
        $user = $this->createAuthenticatedUserWithDataPermissions();
        $this->actingAs($user);

        $data = [
            'internal_name' => 'test-tag',
            'description' => 'Test description',
            'backward_compatibility' => 'legacy-id',
        ];

        $response = $this->post(route('tags.store'), $data);

        $this->assertDatabaseHas('tags', [
            'internal_name' => 'test-tag',
            'description' => 'Test description',
            'backward_compatibility' => 'legacy-id',
        ]);

        $tag = Tag::where('internal_name', 'test-tag')->first();
        $response->assertRedirect(route('tags.show', $tag));
        $response->assertSessionHas('success');
    }

    public function test_store_validates_required_internal_name(): void
    {
        $user = $this->createAuthenticatedUserWithDataPermissions();
        $this->actingAs($user);

        $data = [
            'description' => 'Test description',
        ];

        $response = $this->post(route('tags.store'), $data);

        $response->assertSessionHasErrors('internal_name');
        $this->assertDatabaseCount('tags', 0);
    }

    public function test_store_validates_required_description(): void
    {
        $user = $this->createAuthenticatedUserWithDataPermissions();
        $this->actingAs($user);

        $data = [
            'internal_name' => 'test-tag',
        ];

        $response = $this->post(route('tags.store'), $data);

        $response->assertSessionHasErrors('description');
        $this->assertDatabaseCount('tags', 0);
    }

    public function test_store_allows_optional_backward_compatibility(): void
    {
        $user = $this->createAuthenticatedUserWithDataPermissions();
        $this->actingAs($user);

        $data = [
            'internal_name' => 'test-tag',
            'description' => 'Test description',
        ];

        $response = $this->post(route('tags.store'), $data);

        $this->assertDatabaseHas('tags', [
            'internal_name' => 'test-tag',
            'description' => 'Test description',
        ]);
    }

    public function test_store_validates_unique_internal_name(): void
    {
        $user = $this->createAuthenticatedUserWithDataPermissions();
        $this->actingAs($user);

        Tag::factory()->create(['internal_name' => 'duplicate-tag']);

        $data = [
            'internal_name' => 'duplicate-tag',
            'description' => 'Test description',
        ];

        $response = $this->post(route('tags.store'), $data);

        $response->assertSessionHasErrors('internal_name');
        $this->assertEquals(1, Tag::where('internal_name', 'duplicate-tag')->count());
    }

    public function test_store_requires_authentication(): void
    {
        $data = [
            'internal_name' => 'test-tag',
            'description' => 'Test description',
        ];

        $response = $this->post(route('tags.store'), $data);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('tags', 0);
    }

    public function test_store_requires_create_data_permission(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $data = [
            'internal_name' => 'test-tag',
            'description' => 'Test description',
        ];

        $response = $this->post(route('tags.store'), $data);

        $response->assertForbidden();
        $this->assertDatabaseCount('tags', 0);
    }
}
