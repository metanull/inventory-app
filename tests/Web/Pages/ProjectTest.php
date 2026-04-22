<?php

namespace Tests\Web\Pages;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebCrud;

class ProjectTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebCrud;

    protected function getRouteName(): string
    {
        return 'projects';
    }

    protected function getModelClass(): string
    {
        return Project::class;
    }

    protected function getFormData(): array
    {
        return Project::factory()->make()->toArray();
    }
}
