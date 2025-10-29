<?php

namespace Tests\Web\Pages;

use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebCrud;

class LanguageTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebCrud;

    protected function getRouteName(): string
    {
        return 'languages';
    }

    protected function getModelClass(): string
    {
        return Language::class;
    }

    protected function getFormData(): array
    {
        return Language::factory()->make()->toArray();
    }
}
