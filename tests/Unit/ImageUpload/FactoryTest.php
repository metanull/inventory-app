<?php

namespace Tests\Unit\ImageUpload;

use App\Models\ImageUpload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Event::fake();
    }

    public function test_image_upload_factory(): void
    {
        $imageUpload = ImageUpload::factory()->make();

        $this->assertInstanceOf(ImageUpload::class, $imageUpload);
        $this->assertNotEmpty($imageUpload->path);
        $this->assertNotEmpty($imageUpload->name);
        $this->assertNotEmpty($imageUpload->extension);
        $this->assertNotEmpty($imageUpload->mime_type);
        $this->assertNotEmpty($imageUpload->size);
    }

    public function test_image_upload_factory_creates_a_row_in_database(): void
    {
        $imageUpload = ImageUpload::factory()->create();

        $this->assertDatabaseHas('image_uploads', [
            'id' => $imageUpload->id,
            'path' => $imageUpload->path,
            'name' => $imageUpload->name,
            'extension' => $imageUpload->extension,
            'mime_type' => $imageUpload->mime_type,
            'size' => $imageUpload->size,
        ]);
    }

    public function test_image_upload_factory_create_a_file(): void
    {
        $imageUpload = ImageUpload::factory()->create();

        $this->assertFileExists(Storage::disk('local')->path($imageUpload->path.'/'.$imageUpload->name));
    }
}
