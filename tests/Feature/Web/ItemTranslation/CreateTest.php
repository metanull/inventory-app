<?php

declare(strict_types=1);

namespace Tests\Feature\Web\ItemTranslation;

use App\Models\Context;
use App\Models\Item;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class CreateTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_create_displays_form(): void
    {
        $item = Item::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $response = $this->get(route('item-translations.create'));
        $response->assertOk();
        $response->assertSee('Create Item Translation');
        $response->assertSee('Item');
        $response->assertSee('Language');
        $response->assertSee('Context');
        $response->assertSee('Name');
        $response->assertSee('Description');
    }
}
