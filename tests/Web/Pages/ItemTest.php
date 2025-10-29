<?php

namespace Tests\Web\Pages;

use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebCrud;

class ItemTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebCrud;

    protected function getRouteName(): string
    {
        return 'items';
    }

    protected function getModelClass(): string
    {
        return Item::class;
    }

    protected function getFormData(): array
    {
        return Item::factory()->make()->toArray();
    }
}
