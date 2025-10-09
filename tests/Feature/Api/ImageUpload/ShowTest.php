<?php

namespace Tests\Feature\Api\ImageUpload;

use App\Models\ImageUpload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class ShowTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;
    use WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createVisitorUser();
        $this->actingAs($this->user);

        Storage::fake('local');
        Event::fake();
        Http::fake();
    }

    public function test_show_allows_authenticated_users(): void
    {
        $imageUpload = ImageUpload::factory()->create();

        $response = $this->getJson(route('image-upload.show', $imageUpload->id));
        $response->assertOk();
    }

    public function test_show_returns_not_found_when_not_found(): void
    {
        $response = $this->getJson(route('image-upload.show', 'non-existent-id'));
        $response->assertNotFound();
    }

    public function test_show_returns_the_default_structure_without_relations(): void
    {
        $imageUpload = ImageUpload::factory()->create();

        $response = $this->getJson(route('image-upload.show', $imageUpload->id));
        $response->assertJsonStructure([
            'data' => [
                'id',
                'path',
                'name',
                'extension',
                'mime_type',
                'size',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    public function test_show_returns_the_expected_data(): void
    {
        $imageUpload = ImageUpload::factory()->create();

        $response = $this->getJson(route('image-upload.show', $imageUpload->id));
        $response->assertJson([
            'data' => [
                'id' => $imageUpload->id,
                'path' => $imageUpload->path,
                'name' => $imageUpload->name,
                'extension' => $imageUpload->extension,
                'mime_type' => $imageUpload->mime_type,
                'size' => $imageUpload->size,
            ],
        ]);
    }
}
