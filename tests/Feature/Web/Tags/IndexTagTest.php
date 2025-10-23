<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Tags;

use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class IndexTagTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    public function test_index_displays_tags(): void
    {
        $user = $this->createAuthenticatedUserWithDataPermissions();
        $this->actingAs($user);

        $tags = Tag::factory()->count(3)->create();

        $response = $this->get(route('tags.index'));

        $response->assertOk();
        $response->assertViewIs('tags.index');
        $response->assertViewHas('tags');

        foreach ($tags as $tag) {
            $response->assertSee($tag->internal_name);
        }
    }

    public function test_index_search_filters_tags(): void
    {
        $user = $this->createAuthenticatedUserWithDataPermissions();
        $this->actingAs($user);

        $matching = Tag::factory()->create(['internal_name' => 'specific-tag']);
        $other = Tag::factory()->create(['internal_name' => 'other-tag']);

        $response = $this->get(route('tags.index', ['q' => 'specific']));

        $response->assertOk();
        $response->assertSee($matching->internal_name);
        $response->assertDontSee($other->internal_name);
    }

    public function test_index_pagination_works(): void
    {
        $user = $this->createAuthenticatedUserWithDataPermissions();
        $this->actingAs($user);

        Tag::factory()->count(30)->create();

        $response = $this->get(route('tags.index'));

        $response->assertOk();
        $response->assertViewHas('tags');

        $tags = $response->viewData('tags');
        $this->assertLessThanOrEqual(25, $tags->count());
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->get(route('tags.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_index_requires_view_data_permission(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('tags.index'));

        $response->assertForbidden();
    }
}
