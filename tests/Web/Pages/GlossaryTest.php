<?php

namespace Tests\Web\Pages;

use App\Models\Glossary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebCrud;

class GlossaryTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebCrud;

    protected function getRouteName(): string
    {
        return 'glossaries';
    }

    protected function getModelClass(): string
    {
        return Glossary::class;
    }

    protected function getFormData(): array
    {
        return Glossary::factory()->make()->toArray();
    }
}
