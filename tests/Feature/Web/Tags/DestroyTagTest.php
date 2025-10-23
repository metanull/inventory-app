<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Tags;

use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class DestroyTagTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    public function test_destroy_deletes_tag(): void
    {
        $user = $this->createAuthenticatedUserWithDataPermissions();
        $this->actingAs($user);

        $tag = Tag::factory()->create();

        $response = $this->delete(route('tags.destroy', $tag));

        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
        ]);

        $response->assertRedirect(route('tags.index'));
        $response->assertSessionHas('success');
    }

    public function test_destroy_requires_authentication(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->delete(route('tags.destroy', $tag));

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
        ]);
    }

    public function test_destroy_requires_delete_data_permission(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $tag = Tag::factory()->create();

        $response = $this->delete(route('tags.destroy', $tag));

        $response->assertForbidden();
        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
        ]);
    }
}
