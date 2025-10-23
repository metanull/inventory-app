<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Tags;

use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class ShowTagTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    public function test_show_displays_tag(): void
    {
        $user = $this->createAuthenticatedUserWithDataPermissions();
        $this->actingAs($user);

        $tag = Tag::factory()->create();

        $response = $this->get(route('tags.show', $tag));

        $response->assertOk();
        $response->assertViewIs('tags.show');
        $response->assertViewHas('tag');
        $response->assertSee($tag->internal_name);
        $response->assertSee($tag->description);
    }

    public function test_show_requires_authentication(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->get(route('tags.show', $tag));

        $response->assertRedirect(route('login'));
    }

    public function test_show_requires_view_data_permission(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $tag = Tag::factory()->create();

        $response = $this->get(route('tags.show', $tag));

        $response->assertForbidden();
    }
}
