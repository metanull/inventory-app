<?php

namespace Tests\Web\Pages;

use App\Models\AvailableImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

class AvailableImageTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_show_requires_authentication(): void
    {
        auth()->logout();
        $availableImage = AvailableImage::factory()->create();

        $response = $this->get(route('available-images.show', $availableImage));

        $response->assertRedirect(route('login'));
    }

    public function test_show_page_displays(): void
    {
        $availableImage = AvailableImage::factory()->create([
            'path' => 'test/image.jpg',
            'comment' => 'Test image comment',
        ]);

        $response = $this->get(route('available-images.show', $availableImage));

        $response->assertOk()
            ->assertViewIs('available-images.show')
            ->assertViewHas('availableImage', $availableImage);
    }

    public function test_show_page_renders_image_data(): void
    {
        $availableImage = AvailableImage::factory()->create([
            'path' => 'artifacts/museum-piece.jpg',
            'comment' => 'Unique museum artifact',
        ]);

        $response = $this->get(route('available-images.show', $availableImage));

        $response->assertOk()
            ->assertSee('Unique museum artifact');
    }

    public function test_edit_requires_authentication(): void
    {
        auth()->logout();
        $availableImage = AvailableImage::factory()->create();

        $response = $this->get(route('available-images.edit', $availableImage));

        $response->assertRedirect(route('login'));
    }

    public function test_edit_page_displays(): void
    {
        $availableImage = AvailableImage::factory()->create([
            'path' => 'test/image.jpg',
            'comment' => 'Edit test image',
        ]);

        $response = $this->get(route('available-images.edit', $availableImage));

        $response->assertOk()
            ->assertViewIs('available-images.edit')
            ->assertViewHas('availableImage', $availableImage);
    }
}
