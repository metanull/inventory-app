<?php

namespace Tests\Web\Components;

use App\Livewire\Tables\TagsTable;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebLivewire;

class TagsTableTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebLivewire;

    protected function getComponentClass(): string
    {
        return TagsTable::class;
    }

    protected function getModelClass(): string
    {
        return Tag::class;
    }

    protected function getIdentifier($model): string
    {
        return $model->internal_name;
    }
}
