<?php

namespace Tests\Unit\Picture;

use App\Models\Picture;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_factory(): void
    {
        $picture = Picture::factory()->make();
        $this->assertInstanceOf(Picture::class, $picture);
        $this->assertNotNull($picture->internal_name);
        $this->assertNotNull($picture->backward_compatibility);
        $this->assertNotNull($picture->path);
        $this->assertNotNull($picture->copyright_text);
        $this->assertNotNull($picture->copyright_url);
        $this->assertNotNull($picture->upload_name);
        $this->assertNotNull($picture->upload_extension);
        $this->assertNotNull($picture->upload_mime_type);
        $this->assertNotNull($picture->upload_size);
    }

    public function test_factory_creates_a_row_in_database(): void
    {
        $picture = Picture::factory()->create();
        $this->assertDatabaseHas('pictures', [
            'id' => $picture->id,
            'internal_name' => $picture->internal_name,
            'backward_compatibility' => $picture->backward_compatibility,
            'path' => $picture->path,
            'copyright_text' => $picture->copyright_text,
            'copyright_url' => $picture->copyright_url,
            'upload_name' => $picture->upload_name,
            'upload_extension' => $picture->upload_extension,
            'upload_mime_type' => $picture->upload_mime_type,
            'upload_size' => $picture->upload_size,
        ]);
        $this->assertDatabaseCount('pictures', 1);
    }
}
