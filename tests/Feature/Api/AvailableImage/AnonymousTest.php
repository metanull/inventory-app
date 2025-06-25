<?php

namespace Tests\Feature\Api\AvailableImage;

use App\Models\AvailableImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Tests\TestCase;

class AnonymousTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
        Event::fake();
    }

    public function test_index_forbids_anonymous_access(): void
    {
        $response = $this->getJson(route('available-image.index'));
        $response->assertUnauthorized();
    }

    public function test_show_forbids_anonymous_access(): void
    {
        $availableImage = AvailableImage::factory()->create();

        $response = $this->getJson(route('available-image.show', $availableImage));
        $response->assertUnauthorized();
    }

    public function test_store_is_not_found(): void
    {
        $availableImage = AvailableImage::factory()->make()->toArray();
        $this->expectException(RouteNotFoundException::class);

        $response = $this->postJson(route('available-image.store'), $availableImage);
    }

    public function test_update_forbids_anonymous_access(): void
    {
        $availableImage = AvailableImage::factory()->create();

        $response = $this->putJson(route('available-image.update', $availableImage), [
            'comment' => fake()->sentence(),
        ]);
        $response->assertUnauthorized();
    }

    public function test_destroy_forbids_anonymous_access(): void
    {
        $availableImage = AvailableImage::factory()->create();

        $response = $this->deleteJson(route('available-image.destroy', $availableImage));
        $response->assertUnauthorized();
    }

    public function test_download_forbids_anonymous_access(): void
    {
        $availableImage = AvailableImage::factory()->create();

        $response = $this->getJson(route('available-image.download', $availableImage));
        $response->assertUnauthorized();
    }

}
