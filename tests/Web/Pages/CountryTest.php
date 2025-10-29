<?php

namespace Tests\Web\Pages;

use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebCrud;

class CountryTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebCrud;

    protected function getRouteName(): string
    {
        return 'countries';
    }

    protected function getModelClass(): string
    {
        return Country::class;
    }

    protected function getFormData(): array
    {
        return Country::factory()->make()->toArray();
    }
}
