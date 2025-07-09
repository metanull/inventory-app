<?php

namespace Tests\Feature\Api\Exhibition;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnonymousTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // No user authentication
    }

    public function cannot_access_exhibition_routes_when_unauthenticated(): void
    {
        $this->getJson(route('exhibition.index'))->assertUnauthorized();
        $this->postJson(route('exhibition.store'))->assertUnauthorized();
    }
}
