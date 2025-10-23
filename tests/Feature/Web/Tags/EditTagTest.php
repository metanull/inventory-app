<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Tags;

use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class EditTagTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    public function test_edit_displays_form(): void
    {
        $user = $this->createAuthenticatedUserWithDataPermissions();
        $this->actingAs($user);

        $tag = Tag::factory()->create();

        $response = $this->get(route('tags.edit', $tag));

        $response->assertOk();
        $response->assertViewIs('tags.edit');
        $response->assertViewHas('tag');
        $response->assertSee($tag->internal_name);
    }

    public function test_edit_requires_authentication(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->get(route('tags.edit', $tag));

        $response->assertRedirect(route('login'));
    }

    public function test_edit_requires_update_data_permission(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $tag = Tag::factory()->create();

        $response = $this->get(route('tags.edit', $tag));

        $response->assertForbidden();
    }
}
