<?php

namespace Tests\Unit\Models;

use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for Language model scopes.
 */
class LanguageScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_english_returns_english_language(): void
    {
        $english = Language::factory()->create(['id' => 'eng']);
        $french = Language::factory()->create(['id' => 'fra']);
        $spanish = Language::factory()->create(['id' => 'spa']);

        $result = Language::english()->first();

        $this->assertNotNull($result);
        $this->assertEquals('eng', $result->id);
    }

    public function test_scope_default_returns_default_language(): void
    {
        $default = Language::factory()->withIsDefault()->create();
        $nonDefault1 = Language::factory()->create(['is_default' => false]);
        $nonDefault2 = Language::factory()->create(['is_default' => false]);

        $result = Language::default()->first();

        $this->assertNotNull($result);
        $this->assertEquals($default->id, $result->id);
        $this->assertTrue($result->is_default);
    }
}
