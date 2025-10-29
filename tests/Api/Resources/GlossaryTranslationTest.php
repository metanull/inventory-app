<?php

namespace Tests\Api\Resources;

use App\Models\GlossaryTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Api\Traits\AuthenticatesApiRequests;
use Tests\Api\Traits\TestsApiCrud;
use Tests\TestCase;

class GlossaryTranslationTest extends TestCase
{
    use AuthenticatesApiRequests;
    use RefreshDatabase;
    use TestsApiCrud;

    protected function getResourceName(): string
    {
        return 'glossary-translation';
    }

    protected function getModelClass(): string
    {
        return GlossaryTranslation::class;
    }
}
