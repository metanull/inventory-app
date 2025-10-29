<?php

namespace Tests\Web\Pages;

use App\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebCrud;

class CollectionTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebCrud;

    protected function getRouteName(): string
    {
        return 'collections';
    }

    protected function getModelClass(): string
    {
        return Collection::class;
    }

    protected function getFormData(): array
    {
        return Collection::factory()->make()->toArray();
    }
}
