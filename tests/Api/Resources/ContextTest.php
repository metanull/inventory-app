<?php

namespace Tests\Api\Resources;

use App\Models\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Api\Traits\AuthenticatesApiRequests;
use Tests\Api\Traits\TestsApiCrud;
use Tests\Api\Traits\TestsApiDefaultSelection;
use Tests\TestCase;

class ContextTest extends TestCase
{
    use AuthenticatesApiRequests;
    use RefreshDatabase;
    use TestsApiCrud;
    use TestsApiDefaultSelection;

    protected function getResourceName(): string
    {
        return 'context';
    }

    protected function getModelClass(): string
    {
        return Context::class;
    }
}
