<?php

namespace Tests\Web\Components;

use App\Livewire\Tables\PartnerTranslationsTable;
use App\Models\PartnerTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebLivewire;

class PartnerTranslationsTableTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebLivewire;

    protected function getComponentClass(): string
    {
        return PartnerTranslationsTable::class;
    }

    protected function getModelClass(): string
    {
        return PartnerTranslation::class;
    }

    protected function getIdentifier($model): string
    {
        return $model->name;
    }
}
