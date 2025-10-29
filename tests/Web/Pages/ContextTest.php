<?php

namespace Tests\Web\Pages;

use App\Models\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebCrud;

class ContextTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebCrud;

    protected function getRouteName(): string
    {
        return 'contexts';
    }

    protected function getModelClass(): string
    {
        return Context::class;
    }

    protected function getFormData(): array
    {
        return Context::factory()->make()->toArray();
    }
}
