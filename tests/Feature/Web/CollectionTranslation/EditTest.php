<?php

declare(strict_types=1);

namespace Tests\Feature\Web\CollectionTranslation;

use App\Models\CollectionTranslation;
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
        $translation = CollectionTranslation::factory()->create([
            'title' => 'Existing Title',
            'description' => 'Existing Description',
        ]);

        $response = $this->get(route('collection-translations.edit', $translation));
        $response->assertOk();
        $response->assertSee('Edit Collection Translation');
        $response->assertSee('Existing Title');
        $response->assertSee('Existing Description');
    }

    public function test_edit_loads_necessary_reference_data(): void
    {
        $translation = CollectionTranslation::factory()->create();
        $translation->load(['collection', 'language', 'context']);

        $response = $this->get(route('collection-translations.edit', $translation));
        $response->assertOk();
        $response->assertSee(e($translation->collection->internal_name));
        $response->assertSee(e($translation->language->internal_name));
        $response->assertSee(e($translation->context->internal_name));
    }
}
