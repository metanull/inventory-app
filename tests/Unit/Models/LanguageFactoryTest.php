<?php

namespace Tests\Unit\Models;

use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for Language factory.
 */
class LanguageFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_valid_language(): void
    {
        $language = Language::factory()->create();

        $this->assertInstanceOf(Language::class, $language);
        $this->assertNotEmpty($language->id);
        $this->assertNotEmpty($language->internal_name);
        $this->assertFalse($language->is_default);
    }

    public function test_factory_with_is_default_creates_default_language(): void
    {
        $language = Language::factory()->withIsDefault()->create();

        $this->assertTrue($language->is_default);
    }
}
