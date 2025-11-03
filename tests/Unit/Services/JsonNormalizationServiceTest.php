<?php

namespace Tests\Unit\Services;

use App\Services\JsonNormalizationService;
use Tests\TestCase;

/**
 * Unit tests for JsonNormalizationService
 *
 * Tests the core functionality of JSON normalization to ensure consistent
 * handling of various data formats (string, object, array, null).
 */
class JsonNormalizationServiceTest extends TestCase
{
    private JsonNormalizationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(JsonNormalizationService::class);
    }

    public function test_normalize_null_returns_empty_array_by_default(): void
    {
        $result = $this->service->normalize(null);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_normalize_null_returns_null_when_empty_as_array_false(): void
    {
        $result = $this->service->normalize(null, false);

        $this->assertNull($result);
    }

    public function test_normalize_array_returns_same_array(): void
    {
        $input = ['key' => 'value', 'nested' => ['a' => 'b']];
        $result = $this->service->normalize($input);

        $this->assertSame($input, $result);
    }

    public function test_normalize_json_string_returns_decoded_array(): void
    {
        $jsonString = '{"author":"John Doe","version":"2.0"}';
        $result = $this->service->normalize($jsonString);

        $this->assertIsArray($result);
        $this->assertEquals('John Doe', $result['author']);
        $this->assertEquals('2.0', $result['version']);
    }

    public function test_normalize_json_string_with_nested_array(): void
    {
        $jsonString = '{"tags":["architecture","medieval"]}';
        $result = $this->service->normalize($jsonString);

        $this->assertIsArray($result);
        $this->assertIsArray($result['tags']);
        $this->assertContains('architecture', $result['tags']);
        $this->assertContains('medieval', $result['tags']);
    }

    public function test_normalize_object_returns_array(): void
    {
        $object = (object) ['key' => 'value', 'number' => 42];
        $result = $this->service->normalize($object);

        $this->assertIsArray($result);
        $this->assertEquals('value', $result['key']);
        $this->assertEquals(42, $result['number']);
    }

    public function test_normalize_nested_object_returns_nested_array(): void
    {
        $object = (object) [
            'meta' => (object) ['author' => 'John'],
            'tags' => ['tag1', 'tag2'],
        ];
        $result = $this->service->normalize($object);

        $this->assertIsArray($result);
        $this->assertIsArray($result['meta']);
        $this->assertEquals('John', $result['meta']['author']);
        $this->assertIsArray($result['tags']);
    }

    public function test_normalize_invalid_json_string_returns_empty_array(): void
    {
        $invalidJson = '{invalid json}';
        $result = $this->service->normalize($invalidJson);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_normalize_empty_string_returns_empty_array(): void
    {
        $result = $this->service->normalize('');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_normalize_numeric_value_returns_empty_array(): void
    {
        $result = $this->service->normalize(42);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_normalize_boolean_value_returns_empty_array(): void
    {
        $result = $this->service->normalize(true);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}
