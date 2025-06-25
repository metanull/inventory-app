<?php

namespace Tests\Unit;

use App\Models\AvailableImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AvailableImageTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Event::fake();
    }

    public function test_available_image_factory(): void
    {
        $availableImage = AvailableImage::factory()->make();

        $this->assertInstanceOf(AvailableImage::class, $availableImage);
        $this->assertNotEmpty($availableImage->path);
        $this->assertNotEmpty($availableImage->comment);
    }

    public function test_available_image_factory_creates_a_row_in_database(): void
    {
        $availableImage = AvailableImage::factory()->create();

        $this->assertDatabaseHas('available_images', [
            'id' => $availableImage->id,
            'path' => $availableImage->path,
            'comment' => $availableImage->comment,
        ]);
    }

    public function test_available_image_factory_creates_a_file(): void
    {
        $availableImage = AvailableImage::factory()->create();

        $this->assertFileExists(Storage::disk('public')->path($availableImage->path), 'The path of the available image should exist.');
    }

}
