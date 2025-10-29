<?php

namespace Tests\Web\Components;

use App\Livewire\Tables\PartnersTable;
use App\Models\Partner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebLivewire;

class PartnersTableTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebLivewire;

    protected function getComponentClass(): string
    {
        return PartnersTable::class;
    }

    protected function getModelClass(): string
    {
        return Partner::class;
    }

    protected function getIdentifier($model): string
    {
        return $model->internal_name;
    }
}
