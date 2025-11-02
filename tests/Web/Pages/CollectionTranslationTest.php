<?php

namespace Tests\Web\Pages;

use App\Models\CollectionTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebCrud;

class CollectionTranslationTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebCrud;

    protected function getRouteName(): string
    {
        return 'collection-translations';
    }

    protected function getModelClass(): string
    {
        return CollectionTranslation::class;
    }

    protected function getFormData(): array
    {
        return CollectionTranslation::factory()->make()->toArray();
    }

    /**
     * Override to exclude JSON fields that get double-encoded
     */
    protected function getDatabaseAssertions(array $data): array
    {
        return array_diff_key($data, array_flip(['extra', '_token', '_method']));
    }

    /**
     * Test that extra metadata is displayed as key-value pairs on show page
     */
    public function test_show_page_displays_metadata_as_key_value_pairs(): void
    {
        $collectionTranslation = CollectionTranslation::factory()->create([
            'extra' => json_encode([
                'curator' => 'Jane Smith',
                'year' => '2023',
                'categories' => ['art', 'history'],
            ]),
        ]);

        $response = $this->get(route('collection-translations.show', $collectionTranslation));

        $response->assertStatus(200);
        $response->assertSee('Metadata');
        $response->assertSee('Curator');
        $response->assertSee('Jane Smith');
        $response->assertSee('Year');
        $response->assertSee('2023');
        $response->assertSee('Categories');
        $response->assertSee('art');
        $response->assertSee('history');
    }

    /**
     * Test that show page handles empty metadata gracefully
     */
    public function test_show_page_handles_empty_metadata(): void
    {
        $collectionTranslation = CollectionTranslation::factory()->create([
            'extra' => null,
        ]);

        $response = $this->get(route('collection-translations.show', $collectionTranslation));

        $response->assertStatus(200);
        $response->assertDontSee('Metadata');
    }
}
