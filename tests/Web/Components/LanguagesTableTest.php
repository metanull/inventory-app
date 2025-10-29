<?php

namespace Tests\Web\Components;

use App\Livewire\Tables\LanguagesTable;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebLivewire;

class LanguagesTableTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebLivewire;

    protected function getComponentClass(): string
    {
        return LanguagesTable::class;
    }

    protected function getModelClass(): string
    {
        return Language::class;
    }

    protected function getIdentifier($model): string
    {
        return $model->id ?? $model->internal_name;
    }
}
