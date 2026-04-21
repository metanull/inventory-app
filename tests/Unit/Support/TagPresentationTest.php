<?php

namespace Tests\Unit\Support;

use App\Models\Tag;
use App\Support\Web\TagPresentation;
use Tests\TestCase;

class TagPresentationTest extends TestCase
{
    public function test_label_prefers_description_with_internal_name_fallback(): void
    {
        $describedTag = Tag::factory()->make([
            'description' => 'Meaningful Label',
            'internal_name' => 'internal-tag',
        ]);

        $fallbackTag = Tag::factory()->make([
            'description' => null,
            'internal_name' => 'internal-tag',
        ]);

        $this->assertSame('Meaningful Label', TagPresentation::label($describedTag));
        $this->assertSame('internal-tag', TagPresentation::label($fallbackTag));
    }

    public function test_badge_color_covers_known_tag_categories(): void
    {
        $this->assertSame('blue', TagPresentation::badgeColor('keyword'));
        $this->assertSame('teal', TagPresentation::badgeColor('material'));
        $this->assertSame('purple', TagPresentation::badgeColor('artist'));
        $this->assertSame('yellow', TagPresentation::badgeColor('dynasty'));
        $this->assertSame('indigo', TagPresentation::badgeColor('subject'));
        $this->assertSame('gray', TagPresentation::badgeColor('type'));
        $this->assertSame('red', TagPresentation::badgeColor('filter'));
        $this->assertSame('green', TagPresentation::badgeColor('image-type'));
        $this->assertSame('gray', TagPresentation::badgeColor(null));
    }
}
