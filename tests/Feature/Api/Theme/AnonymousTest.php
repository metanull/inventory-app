<?php

namespace Tests\Feature\Api\Theme;

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

    public function cannot_access_theme_routes_when_unauthenticated(): void
    {
        $this->getJson(route('theme.index'))->assertUnauthorized();
        $this->postJson(route('theme.store'))->assertUnauthorized();
    }
}
