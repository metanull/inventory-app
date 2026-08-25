<?php

namespace Tests\Unit\Models;

use App\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollectionExtraTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_collections_without_extra_by_default(): void
    {
        $collection = Collection::factory()->create();

        $this->assertNull($collection->extra);
    }

    public function test_factory_with_extra_state_sets_extra(): void
    {
        $collection = Collection::factory()
            ->withExtra(['thg_gallery' => ['slug' => 'carpets']])
            ->create();

        $this->assertEquals('carpets', $collection->extra->thg_gallery->slug);
    }

    public function test_extra_is_mass_assignable_and_round_trips_nested_structures(): void
    {
        $collection = Collection::factory()->create();

        $collection->update([
            'extra' => [
                'thg_gallery' => [
                    'mwnf3_project_id' => 'DCA',
                    'slug' => 'carpets',
                    'host' => 'https://carpets.museumwnf.org',
                    'i18n_group_id' => 18,
                ],
            ],
        ]);

        $fresh = $collection->fresh();

        $this->assertEquals('DCA', $fresh->extra->thg_gallery->mwnf3_project_id);
        $this->assertEquals(18, $fresh->extra->thg_gallery->i18n_group_id);
    }

    public function test_extra_decoded_returns_an_associative_array(): void
    {
        $collection = Collection::factory()
            ->withExtra(['thg_gallery' => ['slug' => 'carpets']])
            ->create();

        $decoded = $collection->fresh()->extra_decoded;

        $this->assertIsArray($decoded);
        $this->assertSame('carpets', $decoded['thg_gallery']['slug']);
    }

    public function test_extra_can_be_cleared(): void
    {
        $collection = Collection::factory()
            ->withExtra(['thg_gallery' => ['slug' => 'carpets']])
            ->create();

        $collection->update(['extra' => null]);

        $this->assertNull($collection->fresh()->extra);
    }
}
