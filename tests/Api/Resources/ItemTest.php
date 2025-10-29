<?php

namespace Tests\Api\Resources;

use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Api\Traits\AuthenticatesApiRequests;
use Tests\Api\Traits\TestsApiCrud;
use Tests\Api\Traits\TestsApiTagManagement;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use AuthenticatesApiRequests;
    use RefreshDatabase;
    use TestsApiCrud;
    use TestsApiTagManagement;

    protected function getResourceName(): string
    {
        return 'item';
    }

    protected function getModelClass(): string
    {
        return Item::class;
    }
}
