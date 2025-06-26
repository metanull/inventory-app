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

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_index_forbids_anonymous_access(): void
    {
        $response = $this->getJson(route('picture.index'));
        $response->assertUnauthorized();
    }

    public function test_show_forbids_anonymous_access(): void
    {
        $picture = Picture::factory()->create();
        $response = $this->getJson(route('picture.show', $picture));
        $response->assertUnauthorized();
    }

    public function test_store_forbids_anonymous_access(): void
    {
        $data = Picture::factory()->make()->except(['id', 'path', 'upload_name', 'upload_extension', 'upload_mime_type', 'upload_size']);
        $response = $this->postJson(route('picture.store'), $data);
        $response->assertUnauthorized();
    }

    public function test_update_forbids_anonymous_access(): void
    {
        $picture = Picture::factory()->create();
        $data = ['internal_name' => 'Updated Name'];
        $response = $this->putJson(route('picture.update', $picture), $data);
        $response->assertUnauthorized();
    }

    public function test_destroy_forbids_anonymous_access(): void
    {
        $picture = Picture::factory()->create();
        $response = $this->deleteJson(route('picture.destroy', $picture));
        $response->assertUnauthorized();
    }
}
