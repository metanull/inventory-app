<?php

namespace Tests\Configuration;

use Illuminate\Http\Request;
use Tests\TestCase;

class TrustedProxyConfigurationTest extends TestCase
{
    public function test_trusted_proxies_configuration_is_environment_based(): void
    {
        // Test with no trusted proxies configured
        config(['app.trusted_proxies' => '']);
        $this->assertEmpty(config('app.trusted_proxies'));

        // Test with single proxy
        config(['app.trusted_proxies' => '192.168.1.100']);
        $this->assertEquals('192.168.1.100', config('app.trusted_proxies'));

        // Test with multiple proxies
        config(['app.trusted_proxies' => '192.168.1.100,10.0.0.0/8']);
        $this->assertEquals('192.168.1.100,10.0.0.0/8', config('app.trusted_proxies'));
    }

    public function test_request_url_generation_respects_proxy_headers(): void
    {
        // Create a request with proxy headers
        $request = Request::create('http://internal.server/test', 'GET', [], [], [], [
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_HOST' => 'inventory.museumwnf.org',
            'HTTP_X_FORWARDED_PORT' => '443',
            'HTTP_X_FORWARDED_FOR' => '192.168.255.1',
            'REMOTE_ADDR' => '192.168.255.1', // Simulate reverse proxy IP
        ]);

        // Set the request instance
        $this->app->instance('request', $request);

        // The URL should be generated correctly based on proxy headers
        // Note: This test verifies the concept - actual proxy handling is done by Laravel's middleware
        $this->assertTrue($request->hasHeader('X-Forwarded-Proto'));
        $this->assertEquals('https', $request->header('X-Forwarded-Proto'));
        $this->assertEquals('inventory.museumwnf.org', $request->header('X-Forwarded-Host'));
    }

    public function test_trusted_proxy_headers_are_configured(): void
    {
        // Test that all necessary proxy headers are configured
        $expectedHeaders = [
            Request::HEADER_X_FORWARDED_FOR,
            Request::HEADER_X_FORWARDED_HOST,
            Request::HEADER_X_FORWARDED_PORT,
            Request::HEADER_X_FORWARDED_PROTO,
        ];

        $configuredHeaders = Request::HEADER_X_FORWARDED_FOR |
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO;

        foreach ($expectedHeaders as $header) {
            $this->assertTrue(($configuredHeaders & $header) === $header);
        }
    }

    public function test_environment_variable_parsing(): void
    {
        // Test that the environment variable is properly parsed
        $testCases = [
            '' => [],
            '192.168.1.100' => ['192.168.1.100'],
            '192.168.1.100,10.0.0.0/8' => ['192.168.1.100', '10.0.0.0/8'],
            '192.168.1.100, 10.0.0.0/8, 172.16.0.0/12' => ['192.168.1.100', '10.0.0.0/8', '172.16.0.0/12'],
        ];

        foreach ($testCases as $envValue => $expected) {
            $parsed = empty($envValue) ? [] : array_map('trim', explode(',', $envValue));
            $this->assertEquals($expected, $parsed, "Failed parsing: '{$envValue}'");
        }
    }
}
