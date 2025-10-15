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

class EditTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_edit_displays_form_with_existing_data(): void
    {
        $item = Item::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $translation = ItemTranslation::factory()->create([
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Existing Translation',
            'description' => 'Existing description',
        ]);

        $response = $this->get(route('item-translations.edit', $translation));
        $response->assertOk();
        $response->assertSee('Edit Item Translation');
        $response->assertSee('Existing Translation');
        $response->assertSee('Existing description');
    }
}
