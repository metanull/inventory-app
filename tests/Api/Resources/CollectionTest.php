<?php

namespace Tests\Api\Resources;

use App\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Api\Traits\AuthenticatesApiRequests;
use Tests\Api\Traits\TestsApiCrud;
use Tests\TestCase;

class CollectionTest extends TestCase
{
    use AuthenticatesApiRequests;
    use RefreshDatabase;
    use TestsApiCrud;

    protected function getResourceName(): string
    {
        return 'collection';
    }

    protected function getModelClass(): string
    {
        return Collection::class;
    }
}
