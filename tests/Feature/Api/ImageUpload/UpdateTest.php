<?php

namespace Tests\Feature\Api\ImageUpload;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        Storage::fake('local');
        Event::fake();
        Http::fake();
    }

    public function test_update_route_is_not_found(): void
    {
        $this->expectException(RouteNotFoundException::class);

        $this->putJson(route('image-upload.update', 'non-existent-id'), [
            'file' => UploadedFile::fake()->image('updated.jpg'),
        ]);
    }

    public function test_update_method_is_not_allowed(): void
    {
        $response = $this->putJson('/api/image-upload/non-existent-id', [
            'file' => UploadedFile::fake()->image('updated.jpg'),
        ]);

        $response->assertMethodNotAllowed();
    }
}
