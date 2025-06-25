<?php

namespace Tests\Feature\Api\AvailableImage;

use App\Models\AvailableImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
        Event::fake();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_store_is_not_found(): void
    {
        $availableImage = AvailableImage::factory()->make()->toArray();
        $this->expectException(RouteNotFoundException::class);

        $response = $this->postJson(route('available-image.store'), $availableImage);
    }
}
