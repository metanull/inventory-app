<?php

namespace Tests\Web\Components;

use App\Livewire\Tables\CollectionsTable;
use App\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebLivewire;

class CollectionsTableTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebLivewire;

    protected function getComponentClass(): string
    {
        return CollectionsTable::class;
    }

    protected function getModelClass(): string
    {
        return Collection::class;
    }

    protected function getIdentifier($model): string
    {
        return $model->internal_name;
    }
}
