<?php

namespace Tests\Pub;

use App\Models\CollectionImage;
use App\Models\Item;
use App\Models\ItemImage;
use App\Models\PartnerImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PictureControllerTest extends TestCase
{
    use RefreshDatabase;

    private const MINIMAL_JPEG = '/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCwABIA/9k=';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');

        config([
            'localstorage.pictures.disk' => 'public',
            'localstorage.pictures.directory' => 'pictures',
        ]);
    }

    private function putTestImage(string $filename): void
    {
        Storage::disk('public')->put(
            'pictures/'.$filename,
            base64_decode(self::MINIMAL_JPEG)
        );
    }

    // ── Happy path ────────────────────────────────────────────────────────────

    public function test_serves_jpeg_with_caching_headers(): void
    {
        $item = Item::factory()->create();
        $image = ItemImage::factory()->create([
            'item_id' => $item->id,
            'path' => $item->id.'.jpg',
            'mime_type' => 'image/jpeg',
        ]);
        $this->putTestImage($image->path);

        $response = $this->get(route('pub.picture', [
            'type' => 'item-picture',
            'filename' => $image->id.'.jpg',
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/jpeg');
        $this->assertNotEmpty($response->headers->get('ETag'));
        $this->assertNotEmpty($response->headers->get('Last-Modified'));
        $this->assertStringContainsString('public', $response->headers->get('Cache-Control'));
    }

    // ── Conditional GET: ETag ─────────────────────────────────────────────────

    public function test_returns_304_when_etag_matches(): void
    {
        $item = Item::factory()->create();
        $image = ItemImage::factory()->create([
            'item_id' => $item->id,
            'path' => $item->id.'.jpg',
            'mime_type' => 'image/jpeg',
        ]);
        $this->putTestImage($image->path);

        // First request to obtain the ETag
        $first = $this->get(route('pub.picture', [
            'type' => 'item-picture',
            'filename' => $image->id.'.jpg',
        ]));
        $etag = $first->headers->get('ETag');

        // Conditional request with matching ETag
        $response = $this->withHeaders(['If-None-Match' => $etag])
            ->get(route('pub.picture', [
                'type' => 'item-picture',
                'filename' => $image->id.'.jpg',
            ]));

        $response->assertStatus(304);
    }

    // ── Conditional GET: Last-Modified ────────────────────────────────────────

    public function test_returns_304_when_not_modified_since(): void
    {
        $item = Item::factory()->create();
        $image = ItemImage::factory()->create([
            'item_id' => $item->id,
            'path' => $item->id.'.jpg',
            'mime_type' => 'image/jpeg',
        ]);
        $this->putTestImage($image->path);

        $first = $this->get(route('pub.picture', [
            'type' => 'item-picture',
            'filename' => $image->id.'.jpg',
        ]));
        $lastModified = $first->headers->get('Last-Modified');

        $response = $this->withHeaders(['If-Modified-Since' => $lastModified])
            ->get(route('pub.picture', [
                'type' => 'item-picture',
                'filename' => $image->id.'.jpg',
            ]));

        $response->assertStatus(304);
    }

    // ── 404 paths ─────────────────────────────────────────────────────────────

    public function test_returns_404_for_unknown_id(): void
    {
        $response = $this->get('/pub/item-picture/00000000-0000-0000-0000-000000000000.jpg');

        $response->assertNotFound();
    }

    public function test_returns_404_for_unknown_model_type(): void
    {
        $response = $this->get('/pub/unknown-type/00000000-0000-0000-0000-000000000000.jpg');

        $response->assertNotFound();
    }

    public function test_returns_404_when_file_missing_from_disk(): void
    {
        $item = Item::factory()->create();
        $image = ItemImage::factory()->create([
            'item_id' => $item->id,
            'path' => 'missing-file.jpg',
        ]);
        // intentionally not putting the file in storage

        $response = $this->get(route('pub.picture', [
            'type' => 'item-picture',
            'filename' => $image->id.'.jpg',
        ]));

        $response->assertNotFound();
    }

    // ── 400 paths ─────────────────────────────────────────────────────────────

    public function test_returns_400_when_query_string_is_present(): void
    {
        $item = Item::factory()->create();
        $image = ItemImage::factory()->create([
            'item_id' => $item->id,
            'path' => $item->id.'.jpg',
            'mime_type' => 'image/jpeg',
        ]);
        $this->putTestImage($image->path);

        $response = $this->get(route('pub.picture', [
            'type' => 'item-picture',
            'filename' => $image->id.'.jpg',
        ]).'?w=200');

        $response->assertStatus(400);
    }

    // ── Non-JPEG filename is rejected by the route (no match → 404) ───────────

    public function test_non_jpg_extension_does_not_match_route(): void
    {
        $response = $this->get('/pub/item-picture/00000000-0000-0000-0000-000000000000.png');

        $response->assertNotFound();
    }

    // ── Different model types resolve correctly ───────────────────────────────

    public function test_serves_collection_picture(): void
    {
        $image = CollectionImage::factory()->create([
            'path' => 'col-test.jpg',
            'mime_type' => 'image/jpeg',
        ]);
        $this->putTestImage($image->path);

        $response = $this->get(route('pub.picture', [
            'type' => 'collection-picture',
            'filename' => $image->id.'.jpg',
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/jpeg');
    }

    public function test_serves_partner_picture(): void
    {
        $image = PartnerImage::factory()->create([
            'path' => 'partner-test.jpg',
            'mime_type' => 'image/jpeg',
        ]);
        $this->putTestImage($image->path);

        $response = $this->get(route('pub.picture', [
            'type' => 'partner-picture',
            'filename' => $image->id.'.jpg',
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/jpeg');
    }
}
