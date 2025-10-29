<?php

namespace Tests\Web\Components;

use App\Livewire\Tables\GlossaryTable;
use App\Models\Glossary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebLivewire;

class GlossaryTableTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebLivewire;

    protected function getComponentClass(): string
    {
        return GlossaryTable::class;
    }

    protected function getModelClass(): string
    {
        return Glossary::class;
    }

    protected function getIdentifier($model): string
    {
        return $model->internal_name;
    }
}
