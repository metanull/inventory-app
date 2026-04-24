<?php

namespace Tests\Unit\Http\Responses\Image;

use App\Contracts\StreamableImageFile;
use App\Http\Responses\Image\DownloadImageResponse;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DownloadImageResponseTest extends TestCase
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

    public function test_returns_attachment_response_with_correct_filename(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('pictures/photo.jpg', 'fake-image-content');

        $image = $this->makeImageFile('public', 'pictures/photo.jpg', 'image/jpeg', 'my-photo.jpg');
        $response = (new DownloadImageResponse($image))->toResponse(request());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('my-photo.jpg', $response->headers->get('Content-Disposition'));
    }

    public function test_download_filename_from_imageDownloadFilename_is_used(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('pictures/uuid-filename.jpg', 'fake-image-content');

        $downloadFilename = 'original-upload-name.jpg';
        $image = $this->makeImageFile('public', 'pictures/uuid-filename.jpg', 'image/jpeg', $downloadFilename);
        $response = (new DownloadImageResponse($image))->toResponse(request());

        $this->assertStringContainsString($downloadFilename, $response->headers->get('Content-Disposition'));
    }

    public function test_returns_404_when_file_does_not_exist(): void
    {
        Storage::fake('public');

        $image = $this->makeImageFile('public', 'pictures/missing.jpg', 'image/jpeg', 'missing.jpg');

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        (new DownloadImageResponse($image))->toResponse(request());
    }
}
