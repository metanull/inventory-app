<?php

namespace Tests\Web\Pages;

use App\Models\Glossary;
use App\Models\GlossaryTranslation;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebNestedCrud;

class GlossaryTranslationTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebNestedCrud;

    protected function getParentModelClass()
    {
        return Glossary::class;
    }

    protected function getModelClass(): string
    {
        return GlossaryTranslation::class;
    }

    protected function getRouteName(): string
    {
        return 'glossaries.translations';
    }

    protected function getFormData(): array
    {
        return [
            'language_id' => Language::factory()->create()->id,
            'definition' => 'Test Definition',
        ];
    }

    protected function getParentRouteParam()
    {
        return Glossary::factory()->create();
    }

    protected function getParentForeignKeyName(): string
    {
        return 'glossary_id';
    }

    protected function getIndexView(): string
    {
        return 'glossary-translation.index';
    }

    protected function getShowView(): string
    {
        return 'glossary-translation.show';
    }

    protected function getCreateView(): string
    {
        return 'glossary-translation.create';
    }

    protected function getEditView(): string
    {
        return 'glossary-translation.edit';
    }

    /**
     * Test that duplicate translations for same language are prevented.
     */
    public function test_duplicate_translations_same_language_prevented(): void
    {
        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create();

        // Create first translation
        GlossaryTranslation::factory()
            ->for($glossary)
            ->for($language)
            ->create();

        // Try to create duplicate
        $data = [
            'language_id' => $language->id,
            'definition' => 'Another Definition',
        ];

        $response = $this->post(route('glossaries.translations.store', $glossary), $data);

        $response->assertSessionHasErrors('language_id');
    }
}
