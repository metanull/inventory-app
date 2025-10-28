<?php

declare(strict_types=1);

namespace Tests\Feature\Web\CollectionTranslation;

use App\Models\CollectionTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class ShowTest extends TestCase
{
    use RefreshDatabase;
    use CreatesUsersWithPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsDataUser();
    }

    public function test_show_displays_collection_translation_details(): void
    {
        $translation = CollectionTranslation::factory()->create([
            'title' => 'Unique Collection Title XYZ',
            'description' => 'Unique Description ABC',
        ]);

        $response = $this->get(route('collection-translations.show', $translation));
        $response->assertOk();
        $response->assertSee('Unique Collection Title XYZ');
        $response->assertSee('Unique Description ABC');
    }

    public function test_show_displays_related_collection(): void
    {
        $translation = CollectionTranslation::factory()->create();
        $translation->load('collection');

        $response = $this->get(route('collection-translations.show', $translation));
        $response->assertOk();
        $response->assertSee(e($translation->collection->internal_name));
    }

    public function test_show_displays_language_and_context(): void
    {
        $translation = CollectionTranslation::factory()->create();
        $translation->load(['language', 'context']);

        $response = $this->get(route('collection-translations.show', $translation));
        $response->assertOk();
        $response->assertSee(e($translation->language->internal_name));
        $response->assertSee(e($translation->context->internal_name));
    }

    public function test_show_displays_url_if_present(): void
    {
        $translation = CollectionTranslation::factory()->create([
            'url' => 'https://example.com/unique-url',
        ]);

        $response = $this->get(route('collection-translations.show', $translation));
        $response->assertOk();
        $response->assertSee('https://example.com/unique-url');
    }
}
