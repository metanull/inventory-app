<?php

namespace Tests\Web\Components;

use App\Livewire\Tables\CountriesTable;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebLivewire;

class CountriesTableTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebLivewire;

    protected function getComponentClass(): string
    {
        return CountriesTable::class;
    }

    protected function getModelClass(): string
    {
        return Country::class;
    }

    protected function getIdentifier($model): string
    {
        return $model->id ?? $model->internal_name;
    }
}
