<?php

declare(strict_types=1);

namespace Tests\Feature\Web\AvailableImage;

use App\Models\AvailableImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class ShowTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_show_displays_image_details(): void
    {
        $image = AvailableImage::factory()->create([
            'comment' => 'Test Image Comment',
        ]);

        $response = $this->get(route('available-images.show', $image));
        $response->assertOk();
        $response->assertSee('Image Details');
        $response->assertSee('Test Image Comment');
        $response->assertSee($image->id);
    }

    public function test_show_displays_image_with_no_comment(): void
    {
        $image = AvailableImage::factory()->create([
            'comment' => null,
        ]);

        $response = $this->get(route('available-images.show', $image));
        $response->assertOk();
        $response->assertSee('Image Details');
    }

    public function test_show_contains_download_button(): void
    {
        $image = AvailableImage::factory()->create();
        $response = $this->get(route('available-images.show', $image));
        $response->assertOk();
        $response->assertSee('Download');
    }

    public function test_show_contains_back_link(): void
    {
        $image = AvailableImage::factory()->create();
        $response = $this->get(route('available-images.show', $image));
        $response->assertOk();
        $response->assertSee('Back to Images');
    }
}
