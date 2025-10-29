<?php

namespace Tests\Web\Components;

use App\Livewire\Tables\ContextsTable;
use App\Models\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebLivewire;

class ContextsTableTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebLivewire;

    protected function getComponentClass(): string
    {
        return ContextsTable::class;
    }

    protected function getModelClass(): string
    {
        return Context::class;
    }

    protected function getIdentifier($model): string
    {
        return $model->internal_name;
    }
}
