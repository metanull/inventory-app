<?php

namespace Tests\Web\Pages;

use App\Models\Glossary;
use App\Models\GlossarySpelling;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebNestedCrud;

class GlossarySpellingTest extends TestCase
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
        return GlossarySpelling::class;
    }

    protected function getRouteName(): string
    {
        return 'glossaries.spellings';
    }

    protected function getFormData(): array
    {
        return [
            'language_id' => Language::factory()->create()->id,
            'spelling' => 'test-spelling-'.uniqid(),
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
        return 'glossary-spelling.index';
    }

    protected function getShowView(): string
    {
        return 'glossary-spelling.show';
    }

    protected function getCreateView(): string
    {
        return 'glossary-spelling.create';
    }

    protected function getEditView(): string
    {
        return 'glossary-spelling.edit';
    }

    /**
     * Test that duplicate spellings are prevented.
     */
    public function test_duplicate_spellings_prevented(): void
    {
        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create();
        $spelling = 'test-spelling';

        // Create first spelling
        GlossarySpelling::factory()
            ->for($glossary)
            ->for($language)
            ->create(['spelling' => $spelling]);

        // Try to create duplicate
        $data = [
            'language_id' => $language->id,
            'spelling' => $spelling,
        ];

        $response = $this->post(route('glossaries.spellings.store', $glossary), $data);

        $response->assertSessionHasErrors('spelling');
    }
}
