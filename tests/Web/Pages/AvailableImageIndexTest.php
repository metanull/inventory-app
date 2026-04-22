<?php

namespace Tests\Web\Pages;

use App\Models\AvailableImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

class AvailableImageIndexTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_index_renders_request_driven_page_without_livewire_markup(): void
    {
        AvailableImage::factory()->create(['path' => 'temple/artifact.jpg', 'comment' => 'Temple Artifact']);

        $response = $this->get(route('available-images.index'));

        $response
            ->assertOk()
            ->assertViewIs('available-images.index')
            ->assertSee('artifact.jpg');

        $this->assertStringNotContainsString('livewire:dynamic-component', $response->getContent());
        $this->assertStringNotContainsString('wire:click', $response->getContent());
    }

    public function test_index_can_search_by_comment(): void
    {
        AvailableImage::factory()->create(['path' => 'images/foo.jpg', 'comment' => 'Temple Artifact Photo']);
        AvailableImage::factory()->create(['path' => 'images/bar.jpg', 'comment' => 'Museum Collection']);

        $response = $this->get(route('available-images.index', ['q' => 'Temple']));

        $response
            ->assertOk()
            ->assertSee('foo.jpg')
            ->assertDontSee('bar.jpg');
    }

    public function test_index_can_search_by_filename(): void
    {
        AvailableImage::factory()->create(['path' => 'artifacts/temple-vase.jpg', 'comment' => 'First image']);
        AvailableImage::factory()->create(['path' => 'artifacts/museum-pot.jpg', 'comment' => 'Second image']);

        $response = $this->get(route('available-images.index', ['q' => 'temple-vase']));

        $response
            ->assertOk()
            ->assertSee('temple-vase.jpg')
            ->assertDontSee('museum-pot.jpg');
    }

    public function test_index_rejects_invalid_sort_field_gracefully(): void
    {
        $response = $this->get(route('available-images.index', ['sort' => 'invalid_field']));

        $response->assertOk();
    }

    public function test_index_can_sort_by_path(): void
    {
        AvailableImage::factory()->create(['path' => 'z-last.jpg', 'comment' => 'Last image']);
        AvailableImage::factory()->create(['path' => 'a-first.jpg', 'comment' => 'First image']);

        $response = $this->get(route('available-images.index', [
            'sort' => 'path',
            'direction' => 'asc',
        ]));

        $response
            ->assertOk()
            ->assertSeeInOrder(['a-first.jpg', 'z-last.jpg']);
    }

    public function test_index_can_sort_by_created_at(): void
    {
        AvailableImage::factory()->create(['path' => 'newer.jpg', 'created_at' => now()]);
        AvailableImage::factory()->create(['path' => 'older.jpg', 'created_at' => now()->subDay()]);

        $response = $this->get(route('available-images.index', [
            'sort' => 'created_at',
            'direction' => 'desc',
        ]));

        $response
            ->assertOk()
            ->assertSeeInOrder(['newer.jpg', 'older.jpg']);
    }

    public function test_index_requires_view_data_permission(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('available-images.index'));

        $response->assertForbidden();
    }

    public function test_index_preserves_query_strings_in_pagination_links(): void
    {
        foreach (range(1, 11) as $index) {
            AvailableImage::factory()->create([
                'path' => 'image-'.str_pad((string) $index, 2, '0', STR_PAD_LEFT).'.jpg',
                'comment' => 'Image '.$index,
            ]);
        }

        $response = $this->get(route('available-images.index', [
            'per_page' => 10,
            'sort' => 'path',
            'direction' => 'asc',
        ]));

        $response->assertOk();

        $paginator = $response->viewData('availableImages');
        $nextPageUrl = $paginator->nextPageUrl();

        $this->assertNotNull($nextPageUrl);
        $this->assertStringContainsString('per_page=10', $nextPageUrl);
    }
}
