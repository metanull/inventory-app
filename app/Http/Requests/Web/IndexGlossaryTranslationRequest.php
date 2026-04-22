<?php

namespace App\Http\Requests\Web;

use App\Models\Glossary;
use App\Support\Web\Lists\GlossaryTranslationListDefinition;
use App\Support\Web\Lists\ListDefinition;

class IndexGlossaryTranslationRequest extends IndexListRequest
{
    protected function createDefinition(): ListDefinition
    {
        return new GlossaryTranslationListDefinition;
    }

    protected function prepareForValidation(): void
    {
        $glossary = $this->route('glossary');
        if ($glossary instanceof Glossary) {
            $this->merge(['glossary_id' => $glossary->id]);
        }
        parent::prepareForValidation();
    }
}
