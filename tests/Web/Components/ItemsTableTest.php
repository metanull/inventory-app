<?php

namespace Tests\Web\Components;

use App\Livewire\Tables\ItemsTable;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebLivewire;

class ItemsTableTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebLivewire;

    protected function getComponentClass(): string
    {
        return ItemsTable::class;
    }

    protected function getModelClass(): string
    {
        return Item::class;
    }

    protected function getIdentifier($model): string
    {
        return $model->internal_name;
    }
}
