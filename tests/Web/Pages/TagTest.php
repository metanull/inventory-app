<?php

namespace Tests\Web\Pages;

use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebCrud;

class TagTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebCrud;

    protected function getRouteName(): string
    {
        return 'tags';
    }

    protected function getModelClass(): string
    {
        return Tag::class;
    }

    protected function getFormData(): array
    {
        return Tag::factory()->make()->toArray();
    }
}
