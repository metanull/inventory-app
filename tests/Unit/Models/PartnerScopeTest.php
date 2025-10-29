<?php

namespace Tests\Unit\Models;

use App\Models\Partner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for Partner model scopes.
 */
class PartnerScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_visible_returns_only_visible_partners(): void
    {
        $visible1 = Partner::factory()->visible()->create();
        $visible2 = Partner::factory()->visible()->create();
        $hidden1 = Partner::factory()->hidden()->create();
        $hidden2 = Partner::factory()->hidden()->create();

        $results = Partner::visible()->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $visible1->id));
        $this->assertTrue($results->contains('id', $visible2->id));
        $this->assertFalse($results->contains('id', $hidden1->id));
        $this->assertFalse($results->contains('id', $hidden2->id));
    }
}
