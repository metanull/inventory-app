<?php

declare(strict_types=1);

namespace Tests\Feature\Web\ItemTranslation;

use App\Models\Context;
use App\Models\Item;
use App\Models\ItemTranslation;
use App\Models\Language;
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

    public function test_show_displays_item_translation_details(): void
    {
        $item = Item::factory()->create(['internal_name' => 'Test Item']);
        $language = Language::factory()->create(['internal_name' => 'English']);
        $context = Context::factory()->create(['internal_name' => 'Default Context']);

        $translation = ItemTranslation::factory()->create([
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Translation Name',
            'description' => 'Translation description',
            'alternate_name' => 'Alternative Name',
            'type' => 'painting',
        ]);

        $response = $this->get(route('item-translations.show', $translation));
        $response->assertOk();
        $response->assertSee('Translation Name');
        $response->assertSee('Translation description');
        $response->assertSee('Alternative Name');
        $response->assertSee('Test Item');
        $response->assertSee('English');
        $response->assertSee('Default Context');
    }
}
