<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Tags;

use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class UpdateTagTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    public function test_update_modifies_tag_with_valid_data(): void
    {
        $user = $this->createAuthenticatedUserWithDataPermissions();
        $this->actingAs($user);

        $tag = Tag::factory()->create([
            'internal_name' => 'original-tag',
            'description' => 'Original description',
        ]);

        $data = [
            'internal_name' => 'updated-tag',
            'description' => 'Updated description',
            'backward_compatibility' => 'updated-legacy-id',
        ];

        $response = $this->put(route('tags.update', $tag), $data);

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'internal_name' => 'updated-tag',
            'description' => 'Updated description',
            'backward_compatibility' => 'updated-legacy-id',
        ]);

        $response->assertRedirect(route('tags.show', $tag));
        $response->assertSessionHas('success');
    }

    public function test_update_validates_required_internal_name(): void
    {
        $user = $this->createAuthenticatedUserWithDataPermissions();
        $this->actingAs($user);

        $tag = Tag::factory()->create();

        $data = [
            'description' => 'Updated description',
        ];

        $response = $this->put(route('tags.update', $tag), $data);

        $response->assertSessionHasErrors('internal_name');
    }

    public function test_update_validates_required_description(): void
    {
        $user = $this->createAuthenticatedUserWithDataPermissions();
        $this->actingAs($user);

        $tag = Tag::factory()->create();

        $data = [
            'internal_name' => 'updated-tag',
        ];

        $response = $this->put(route('tags.update', $tag), $data);

        $response->assertSessionHasErrors('description');
    }

    public function test_update_validates_unique_internal_name(): void
    {
        $user = $this->createAuthenticatedUserWithDataPermissions();
        $this->actingAs($user);

        Tag::factory()->create(['internal_name' => 'existing-tag']);
        $tag = Tag::factory()->create(['internal_name' => 'original-tag']);

        $data = [
            'internal_name' => 'existing-tag',
            'description' => 'Updated description',
        ];

        $response = $this->put(route('tags.update', $tag), $data);

        $response->assertSessionHasErrors('internal_name');
    }

    public function test_update_allows_same_internal_name(): void
    {
        $user = $this->createAuthenticatedUserWithDataPermissions();
        $this->actingAs($user);

        $tag = Tag::factory()->create(['internal_name' => 'same-tag']);

        $data = [
            'internal_name' => 'same-tag',
            'description' => 'Updated description',
        ];

        $response = $this->put(route('tags.update', $tag), $data);

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'internal_name' => 'same-tag',
            'description' => 'Updated description',
        ]);
    }

    public function test_update_requires_authentication(): void
    {
        $tag = Tag::factory()->create();

        $data = [
            'internal_name' => 'updated-tag',
            'description' => 'Updated description',
        ];

        $response = $this->put(route('tags.update', $tag), $data);

        $response->assertRedirect(route('login'));
    }

    public function test_update_requires_update_data_permission(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $tag = Tag::factory()->create();

        $data = [
            'internal_name' => 'updated-tag',
            'description' => 'Updated description',
        ];

        $response = $this->put(route('tags.update', $tag), $data);

        $response->assertForbidden();
    }
}
