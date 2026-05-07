<?php

namespace Tests\Unit\Support\LegacyLinks;

use App\Support\LegacyLinks\LegacyReference;
use Tests\TestCase;

class LegacyReferenceTest extends TestCase
{
    public function test_parse_splits_schema_table_and_parts(): void
    {
        $reference = LegacyReference::parse('mwnf3:objects:ISL:eg:Mus01:1');

        $this->assertNotNull($reference);
        $this->assertSame('mwnf3', $reference->schema);
        $this->assertSame('objects', $reference->table);
        $this->assertSame(['ISL', 'eg', 'Mus01', '1'], $reference->parts);
        $this->assertSame('Mus01', $reference->part(2));
        $this->assertTrue($reference->is('mwnf3', 'objects'));
    }

    public function test_parse_rejects_empty_or_malformed_values(): void
    {
        $this->assertNull(LegacyReference::parse(null));
        $this->assertNull(LegacyReference::parse(''));
        $this->assertNull(LegacyReference::parse('mwnf3'));
        $this->assertNull(LegacyReference::parse(':objects:1'));
    }
}
