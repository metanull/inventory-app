<?php

namespace Tests\Web;

use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_the_application_redirects_to_web_route_by_default(): void
    {
        $response = $this->get(route('root'));
        $response->assertRedirect(route('web.welcome'));
    }

    public function test_the_application_web_route_is_accessible(): void
    {
        $response = $this->get(route('web.welcome'));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/html; charset=utf-8');
        $response->assertViewIs('home');
    }
}
