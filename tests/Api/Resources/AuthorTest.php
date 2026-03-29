<?php

namespace Tests\Api\Resources;

use App\Models\Author;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Api\Traits\AuthenticatesApiRequests;
use Tests\Api\Traits\TestsApiCrud;
use Tests\TestCase;

class AuthorTest extends TestCase
{
    use AuthenticatesApiRequests;
    use RefreshDatabase;
    use TestsApiCrud;

    protected function getResourceName(): string
    {
        return 'author';
    }

    protected function getModelClass(): string
    {
        return Author::class;
    }
}
