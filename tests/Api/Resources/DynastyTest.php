<?php

namespace Tests\Api\Resources;

use App\Models\Dynasty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Api\Traits\AuthenticatesApiRequests;
use Tests\Api\Traits\TestsApiCrud;
use Tests\TestCase;

class DynastyTest extends TestCase
{
    use AuthenticatesApiRequests;
    use RefreshDatabase;
    use TestsApiCrud;

    protected function getResourceName(): string
    {
        return 'dynasty';
    }

    protected function getModelClass(): string
    {
        return Dynasty::class;
    }
}
