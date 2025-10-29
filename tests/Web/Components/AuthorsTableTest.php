<?php

namespace Tests\Web\Components;

use App\Livewire\Tables\AuthorsTable;
use App\Models\Author;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebLivewire;

class AuthorsTableTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebLivewire;

    protected function getComponentClass(): string
    {
        return AuthorsTable::class;
    }

    protected function getModelClass(): string
    {
        return Author::class;
    }

    protected function getIdentifier($model): string
    {
        return $model->name;
    }
}
