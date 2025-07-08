<?php

namespace Tests\Unit\Picture;

use App\Models\Detail;
use App\Models\Item;
use App\Models\Partner;
use App\Models\Picture;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_picture_factory(): void
    {
        $picture = Picture::factory()->make();

        $this->assertInstanceOf(Picture::class, $picture);
        $this->assertNotEmpty($picture->internal_name);
        $this->assertNotEmpty($picture->path);
        $this->assertNotEmpty($picture->upload_name);
        $this->assertNotEmpty($picture->upload_extension);
        $this->assertNotEmpty($picture->upload_mime_type);
        $this->assertNotEmpty($picture->upload_size);
    }

    public function test_picture_factory_creates_a_row_in_database(): void
    {
        $picture = Picture::factory()->forItem()->create();

        $this->assertDatabaseHas('pictures', [
            'id' => $picture->id,
            'internal_name' => $picture->internal_name,
            'path' => $picture->path,
            'upload_name' => $picture->upload_name,
            'upload_extension' => $picture->upload_extension,
            'upload_mime_type' => $picture->upload_mime_type,
            'upload_size' => $picture->upload_size,
            'pictureable_type' => 'App\\Models\\Item',
            'pictureable_id' => $picture->pictureable_id,
        ]);
    }

    public function test_picture_factory_creates_a_file(): void
    {
        $picture = Picture::factory()->forItem()->create();

        $this->assertFileExists(Storage::disk('public')->path($picture->path));
    }

    public function test_picture_factory_for_item(): void
    {
        $picture = Picture::factory()->forItem()->create();

        $this->assertEquals('App\\Models\\Item', $picture->pictureable_type);
        $this->assertInstanceOf(Item::class, $picture->pictureable);
    }

    public function test_picture_factory_for_detail(): void
    {
        $picture = Picture::factory()->forDetail()->create();

        $this->assertEquals('App\\Models\\Detail', $picture->pictureable_type);
        $this->assertInstanceOf(Detail::class, $picture->pictureable);
    }

    public function test_picture_factory_for_partner(): void
    {
        $picture = Picture::factory()->forPartner()->create();

        $this->assertEquals('App\\Models\\Partner', $picture->pictureable_type);
        $this->assertInstanceOf(Partner::class, $picture->pictureable);
    }

    public function test_picture_relationships(): void
    {
        $item = Item::factory()->create();
        $picture = Picture::factory()->for($item, 'pictureable')->create();

        $this->assertEquals($item->id, $picture->pictureable_id);
        $this->assertEquals(get_class($item), $picture->pictureable_type);
        $this->assertTrue($picture->pictureable->is($item));
    }
}
