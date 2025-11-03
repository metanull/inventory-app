<?php

namespace Tests\Web\Pages;

use App\Models\ItemTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebCrud;

class ItemTranslationTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebCrud;

    protected function getRouteName(): string
    {
        return 'item-translations';
    }

    protected function getModelClass(): string
    {
        return ItemTranslation::class;
    }

    protected function getFormData(): array
    {
        return ItemTranslation::factory()->make()->toArray();
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
        $itemTranslation = ItemTranslation::factory()->create([
            'extra' => json_encode([
                'author' => 'John Doe',
                'version' => '2.0',
                'tags' => ['architecture', 'medieval'],
            ]),
        ]);

        $response = $this->get(route('item-translations.show', $itemTranslation));

        $response->assertStatus(200);
        $response->assertSee('Metadata');
        $response->assertSee('Author');
        $response->assertSee('John Doe');
        $response->assertSee('Version');
        $response->assertSee('2.0');
        $response->assertSee('Tags');
        $response->assertSee('architecture');
        $response->assertSee('medieval');
    }

    /**
     * Test that show page handles empty metadata gracefully
     */
    public function test_show_page_handles_empty_metadata(): void
    {
        $itemTranslation = ItemTranslation::factory()->create([
            'extra' => null,
        ]);

        $response = $this->get(route('item-translations.show', $itemTranslation));

        $response->assertStatus(200);
        $response->assertDontSee('Metadata');
    }

    /**
     * Test that show page displays metadata with special values
     */
    public function test_show_page_displays_metadata_with_special_values(): void
    {
        $itemTranslation = ItemTranslation::factory()->create([
            'extra' => json_encode([
                'note' => 'Test note',
                'empty_field' => '',
                'empty_array' => [],
            ]),
        ]);

        $response = $this->get(route('item-translations.show', $itemTranslation));

        $response->assertStatus(200);
        $response->assertSee('Metadata');
        $response->assertSee('Note');
        $response->assertSee('Test note');
        $response->assertSee('empty');
    }
}
