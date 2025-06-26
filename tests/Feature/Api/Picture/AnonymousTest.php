<?php

namespace Tests\Feature\Api\Picture;

use App\Models\Picture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AnonymousTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Authentication: index forbids anonymous access.
     */
    public function test_index_forbids_anonymous_access()
    {
        $response = $this->withHeaders(['Authorization' => ''])
            ->getJson(route('picture.index'));
        $response->assertUnauthorized();
    }

    /**
     * Authentication: show forbids anonymous access.
     */
    public function test_show_forbids_anonymous_access()
    {
        $picture = Picture::factory()->create();
        $response = $this->withHeaders(['Authorization' => ''])
            ->getJson(route('picture.show', $picture));
        $response->assertUnauthorized();
    }

    /**
     * Authentication: store forbids anonymous access.
     */
    public function test_store_forbids_anonymous_access()
    {
        $data = Picture::factory()->make()->except(['id', 'path', 'upload_name', 'upload_extension', 'upload_mime_type', 'upload_size']);
        $response = $this->withHeaders(['Authorization' => ''])
            ->postJson(route('picture.store'), $data);
        $response->assertUnauthorized();
    }

    /**
     * Authentication: update forbids anonymous access.
     */
    public function test_update_forbids_anonymous_access()
    {
        $picture = Picture::factory()->create();
        $data = ['internal_name' => 'Updated Name'];
        $response = $this->withHeaders(['Authorization' => ''])
            ->putJson(route('picture.update', $picture), $data);
        $response->assertUnauthorized();
    }

    /**
     * Authentication: destroy forbids anonymous access.
     */
    public function test_destroy_forbids_anonymous_access()
    {
        $picture = Picture::factory()->create();
        $response = $this->withHeaders(['Authorization' => ''])
            ->deleteJson(route('picture.destroy', $picture));
        $response->assertUnauthorized();
    }
}
