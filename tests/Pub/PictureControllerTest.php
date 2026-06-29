<?php

namespace Tests\Pub;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class PictureControllerTest extends TestCase
{
    use RefreshDatabase;

    private const MINIMAL_JPEG = '/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCwABIA/9k=';

    protected function setUp(): void
    {
        parent::setUp();

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

    private function uuid(): string
    {
        return (string) Str::uuid();
    }

    // ── Happy path ────────────────────────────────────────────────────────────

    public function test_serves_jpeg_with_caching_headers(): void
    {
        $filename = $this->uuid().'.jpg';
        $this->putTestImage($filename);

        $response = $this->get(route('pub.picture', ['filename' => $filename]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/jpeg');
        $this->assertNotEmpty($response->headers->get('ETag'));
        $this->assertNotEmpty($response->headers->get('Last-Modified'));
        $this->assertStringContainsString('public', $response->headers->get('Cache-Control'));
    }

    // ── Conditional GET: ETag ─────────────────────────────────────────────────

    public function test_returns_304_when_etag_matches(): void
    {
        $filename = $this->uuid().'.jpg';
        $this->putTestImage($filename);

        $first = $this->get(route('pub.picture', ['filename' => $filename]));
        $etag = $first->headers->get('ETag');

        $response = $this->withHeaders(['If-None-Match' => $etag])
            ->get(route('pub.picture', ['filename' => $filename]));

        $response->assertStatus(304);
    }

    // ── Conditional GET: Last-Modified ────────────────────────────────────────

    public function test_returns_304_when_not_modified_since(): void
    {
        $filename = $this->uuid().'.jpg';
        $this->putTestImage($filename);

        $first = $this->get(route('pub.picture', ['filename' => $filename]));
        $lastModified = $first->headers->get('Last-Modified');

        $response = $this->withHeaders(['If-Modified-Since' => $lastModified])
            ->get(route('pub.picture', ['filename' => $filename]));

        $response->assertStatus(304);
    }

    // ── 404 paths ─────────────────────────────────────────────────────────────

    public function test_returns_404_for_unknown_file(): void
    {
        $response = $this->get('/pub/00000000-0000-0000-0000-000000000000.jpg');

        $response->assertNotFound();
    }

    // ── 400 paths ─────────────────────────────────────────────────────────────

    public function test_returns_400_when_query_string_is_present(): void
    {
        $filename = $this->uuid().'.jpg';
        $this->putTestImage($filename);

        $response = $this->get(route('pub.picture', ['filename' => $filename]).'?w=200');

        $response->assertStatus(400);
    }

    // ── Non-JPEG filename does not match the route ────────────────────────────

    public function test_non_jpg_extension_does_not_match_route(): void
    {
        $response = $this->get('/pub/00000000-0000-0000-0000-000000000000.png');

        $response->assertNotFound();
    }
}
