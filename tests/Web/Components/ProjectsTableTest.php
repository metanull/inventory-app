<?php

namespace Tests\Web\Components;

use App\Livewire\Tables\ProjectsTable;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebLivewire;

class ProjectsTableTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebLivewire;

    protected function getComponentClass(): string
    {
        return ProjectsTable::class;
    }

    protected function getModelClass(): string
    {
        return Project::class;
    }

    protected function getIdentifier($model): string
    {
        return $model->internal_name;
    }
}
