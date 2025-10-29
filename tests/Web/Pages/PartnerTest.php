<?php

namespace Tests\Web\Pages;

use App\Models\Partner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebCrud;

class PartnerTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebCrud;

    protected function getRouteName(): string
    {
        return 'partners';
    }

    protected function getModelClass(): string
    {
        return Partner::class;
    }

    protected function getFormData(): array
    {
        return Partner::factory()->make()->toArray();
    }
}
