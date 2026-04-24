<?php

namespace Tests\Unit\Http\Responses\Image;

use App\Contracts\StreamableImageFile;
use App\Http\Responses\Image\InlineImageResponse;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InlineImageResponseTest extends TestCase
{
    private function makeImageFile(string $disk, string $storagePath, ?string $mimeType, string $downloadFilename): StreamableImageFile
    {
        return new class($disk, $storagePath, $mimeType, $downloadFilename) implements StreamableImageFile {
            public function __construct(
                private string $disk,
                private string $storagePath,
                private ?string $mimeType,
                private string $downloadFilename,
            ) {}

            public function imageDisk(): string
            {
                return $this->disk;
            }

            public function imageStoragePath(): string
            {
                return $this->storagePath;
            }

            public function imageMimeType(): ?string
            {
                return $this->mimeType;
            }

            public function imageDownloadFilename(): string
            {
                return $this->downloadFilename;
            }
        };
    }

    public function test_returns_inline_response_with_correct_disposition(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('images/test.jpg', 'fake-image-content');

        $image = $this->makeImageFile('public', 'images/test.jpg', 'image/jpeg', 'test.jpg');
        $response = (new InlineImageResponse($image))->toResponse(request());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('inline', $response->headers->get('Content-Disposition'));
    }

    public function test_returns_inline_response_with_null_mime_type(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('images/test.png', 'fake-image-content');

        $image = $this->makeImageFile('public', 'images/test.png', null, 'test.png');
        $response = (new InlineImageResponse($image))->toResponse(request());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('inline', $response->headers->get('Content-Disposition'));
    }

    public function test_returns_404_when_file_does_not_exist(): void
    {
        Storage::fake('public');

        $image = $this->makeImageFile('public', 'images/missing.jpg', 'image/jpeg', 'missing.jpg');

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        (new InlineImageResponse($image))->toResponse(request());
    }
}
