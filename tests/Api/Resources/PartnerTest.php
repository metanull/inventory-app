<?php

namespace Tests\Api\Resources;

use App\Models\Partner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Api\Traits\AuthenticatesApiRequests;
use Tests\Api\Traits\TestsApiCrud;
use Tests\TestCase;

class PartnerTest extends TestCase
{
    use AuthenticatesApiRequests;
    use RefreshDatabase;
    use TestsApiCrud;

    protected function getResourceName(): string
    {
        return 'partner';
    }

    protected function getModelClass(): string
    {
        return Partner::class;
    }
}
