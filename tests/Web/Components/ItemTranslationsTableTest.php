<?php

namespace Tests\Web\Components;

use App\Livewire\Tables\ItemTranslationsTable;
use App\Models\ItemTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebLivewire;

class ItemTranslationsTableTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebLivewire;

    protected function getComponentClass(): string
    {
        return ItemTranslationsTable::class;
    }

    protected function getModelClass(): string
    {
        return ItemTranslation::class;
    }

    protected function getIdentifier($model): string
    {
        return $model->name;
    }
}
