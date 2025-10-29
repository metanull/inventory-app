<?php

namespace Tests\Api\Resources;

use App\Models\ThemeTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Api\Traits\AuthenticatesApiRequests;
use Tests\Api\Traits\TestsApiCrud;
use Tests\TestCase;

class ThemeTranslationTest extends TestCase
{
    use AuthenticatesApiRequests;
    use RefreshDatabase;
    use TestsApiCrud;

    protected function getResourceName(): string
    {
        return 'theme-translation';
    }

    protected function getModelClass(): string
    {
        return ThemeTranslation::class;
    }
}
