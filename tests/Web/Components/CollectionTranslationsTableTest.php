<?php

namespace Tests\Web\Components;

use App\Livewire\Tables\CollectionTranslationsTable;
use App\Models\CollectionTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebLivewire;

class CollectionTranslationsTableTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebLivewire;

    protected function getComponentClass(): string
    {
        return CollectionTranslationsTable::class;
    }

    protected function getModelClass(): string
    {
        return CollectionTranslation::class;
    }

    protected function getIdentifier($model): string
    {
        return $model->title;
    }
}
