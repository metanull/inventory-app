<?php

namespace Tests\Api\Resources;

use App\Models\Glossary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Api\Traits\AuthenticatesApiRequests;
use Tests\Api\Traits\TestsApiCrud;
use Tests\TestCase;

class GlossaryTest extends TestCase
{
    use AuthenticatesApiRequests;
    use RefreshDatabase;
    use TestsApiCrud;

    protected function getResourceName(): string
    {
        return 'glossary';
    }

    protected function getModelClass(): string
    {
        return Glossary::class;
    }
}
