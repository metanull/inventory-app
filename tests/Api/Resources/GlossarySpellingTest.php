<?php

namespace Tests\Api\Resources;

use App\Models\GlossarySpelling;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Api\Traits\AuthenticatesApiRequests;
use Tests\Api\Traits\TestsApiCrud;
use Tests\TestCase;

class GlossarySpellingTest extends TestCase
{
    use AuthenticatesApiRequests;
    use RefreshDatabase;
    use TestsApiCrud;

    protected function getResourceName(): string
    {
        return 'glossary-spelling';
    }

    protected function getModelClass(): string
    {
        return GlossarySpelling::class;
    }
}
