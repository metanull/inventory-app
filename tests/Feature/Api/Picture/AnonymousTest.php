<?php

namespace Tests\Feature\Api\Picture;

use App\Models\Picture;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AnonymousTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson(route('picture.index'));

        $response->assertUnauthorized();
    }

    public function test_show_requires_authentication(): void
    {
        $picture = Picture::factory()->forItem()->create();

        $response = $this->getJson(route('picture.show', $picture));

        $response->assertUnauthorized();
    }

    public function test_update_requires_authentication(): void
    {
        $picture = Picture::factory()->forItem()->create();

        $response = $this->putJson(route('picture.update', $picture), [
            'internal_name' => 'Updated Picture',
        ]);

        $response->assertUnauthorized();
    }

    public function test_destroy_requires_authentication(): void
    {
        $picture = Picture::factory()->forItem()->create();

        $response = $this->deleteJson(route('picture.destroy', $picture));

        $response->assertUnauthorized();
    }

    public function test_attach_to_item_requires_authentication(): void
    {
        $item = \App\Models\Item::factory()->create();

        $response = $this->postJson(route('picture.attachToItem', $item), [
            'available_image_id' => fake()->uuid(),
            'internal_name' => 'Test Picture',
        ]);

        $response->assertUnauthorized();
    }

    public function test_attach_to_detail_requires_authentication(): void
    {
        $detail = \App\Models\Detail::factory()->create();

        $response = $this->postJson(route('picture.attachToDetail', $detail), [
            'available_image_id' => fake()->uuid(),
            'internal_name' => 'Test Picture',
        ]);

        $response->assertUnauthorized();
    }

    public function test_attach_to_partner_requires_authentication(): void
    {
        $partner = \App\Models\Partner::factory()->create();

        $response = $this->postJson(route('picture.attachToPartner', $partner), [
            'available_image_id' => fake()->uuid(),
            'internal_name' => 'Test Picture',
        ]);

        $response->assertUnauthorized();
    }

    public function test_detach_from_item_requires_authentication(): void
    {
        $item = \App\Models\Item::factory()->create();
        $picture = Picture::factory()->forItem()->create([
            'pictureable_id' => $item->id,
            'pictureable_type' => get_class($item),
        ]);

        $response = $this->deleteJson(route('picture.detachFromItem', [$item, $picture]));

        $response->assertUnauthorized();
    }

    public function test_detach_from_detail_requires_authentication(): void
    {
        $detail = \App\Models\Detail::factory()->create();
        $picture = Picture::factory()->forDetail()->create([
            'pictureable_id' => $detail->id,
            'pictureable_type' => get_class($detail),
        ]);

        $response = $this->deleteJson(route('picture.detachFromDetail', [$detail, $picture]));

        $response->assertUnauthorized();
    }

    public function test_detach_from_partner_requires_authentication(): void
    {
        $partner = \App\Models\Partner::factory()->create();
        $picture = Picture::factory()->forPartner()->create([
            'pictureable_id' => $partner->id,
            'pictureable_type' => get_class($partner),
        ]);

        $response = $this->deleteJson(route('picture.detachFromPartner', [$partner, $picture]));

        $response->assertUnauthorized();
    }

    public function test_download_requires_authentication(): void
    {
        $picture = Picture::factory()->forItem()->create();

        $response = $this->getJson(route('picture.download', $picture));

        $response->assertUnauthorized();
    }

    public function test_view_requires_authentication(): void
    {
        $picture = Picture::factory()->forItem()->create();

        $response = $this->getJson(route('picture.view', $picture));

        $response->assertUnauthorized();
    }
}
