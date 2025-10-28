<?php

declare(strict_types=1);

namespace Tests\Feature\Web\AvailableImage;

use App\Models\AvailableImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class IndexTest extends TestCase
{
    use RefreshDatabase;
    use CreatesUsersWithPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsDataUser();
    }

    public function test_index_lists_available_images(): void
    {
        AvailableImage::factory()->count(5)->create();
        $response = $this->get(route('available-images.index'));
        $response->assertOk();
        $response->assertSee('Available Images');
        $firstImage = AvailableImage::query()->orderByDesc('created_at')->first();
        if ($firstImage->comment) {
            $response->assertSee(e($firstImage->comment));
        }
    }

    public function test_index_search_filters_by_comment(): void
    {
        AvailableImage::factory()->count(5)->create(['comment' => 'Regular image']);
        $target = AvailableImage::factory()->create(['comment' => 'SPECIAL_IMAGE_SEARCH']);
        $response = $this->get(route('available-images.index', ['q' => 'SPECIAL_IMAGE_SEARCH']));
        $response->assertOk();
        $response->assertSee('SPECIAL_IMAGE_SEARCH');
        $response->assertDontSee('Regular image');
    }

    public function test_index_displays_empty_state_when_no_images(): void
    {
        $response = $this->get(route('available-images.index'));
        $response->assertOk();
        $response->assertSee('No images found');
    }

    public function test_index_shows_upload_button_with_create_permission(): void
    {
        $response = $this->get(route('available-images.index'));
        $response->assertOk();
        $response->assertSee('Upload Images');
    }

    public function test_index_respects_per_page_query(): void
    {
        AvailableImage::factory()->count(12)->create();
        $response = $this->get(route('available-images.index', ['per_page' => 5]));
        $response->assertOk();
        // Check that pagination controls are present
        $response->assertSee('Rows per page');
    }
}
