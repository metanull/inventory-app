<?php

namespace Tests\Feature\Api\Picture;

use App\Models\Picture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase, WithFaker;

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

    public function test_update_allows_authenticated_users(): void
    {
        $picture = Picture::factory()->create();
        $data = Picture::factory()->make()->only(['internal_name', 'backward_compatibility', 'copyright_text', 'copyright_url']);
        $response = $this->putJson(route('picture.update', $picture), $data);
        $response->assertOk();
    }

    public function test_update_updates_a_row(): void
    {
        $picture = Picture::factory()->create();
        $data = Picture::factory()->make()->only(['internal_name', 'backward_compatibility', 'copyright_text', 'copyright_url']);
        $response = $this->putJson(route('picture.update', $picture), $data);
        $response->assertOk();
        $this->assertDatabaseHas('pictures', array_merge(['id' => $picture->id], $data));
    }

    public function test_update_returns_ok_on_success(): void
    {
        $picture = Picture::factory()->create();
        $data = Picture::factory()->make()->only(['internal_name', 'backward_compatibility', 'copyright_text', 'copyright_url']);
        $response = $this->putJson(route('picture.update', $picture), $data);
        $response->assertOk();
    }

    public function test_update_returns_not_found_when_record_does_not_exist(): void
    {
        $data = Picture::factory()->make()->only(['internal_name', 'backward_compatibility', 'copyright_text', 'copyright_url']);
        $response = $this->putJson(route('picture.update', 'non-existent-id'), $data);
        $response->assertNotFound();
    }

    public function test_update_returns_unprocessable_entity_when_input_is_invalid(): void
    {
        $picture = Picture::factory()->create();
        $invalidData = Picture::factory()->make()->except(['internal_name']);
        $response = $this->putJson(route('picture.update', $picture), $invalidData);
        $response->assertUnprocessable();
    }

    public function test_update_returns_the_expected_structure(): void
    {
        $picture = Picture::factory()->create();
        $data = Picture::factory()->make()->only(['internal_name', 'backward_compatibility', 'copyright_text', 'copyright_url']);
        $response = $this->putJson(route('picture.update', $picture), $data);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'path',
                'internal_name',
                'backward_compatibility',
                'copyright_text',
                'copyright_url',
                'upload_name',
                'upload_extension',
                'upload_mime_type',
                'upload_size',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    public function test_update_returns_the_expected_content(): void
    {
        $picture = Picture::factory()->create();
        $data = Picture::factory()->make()->only(['internal_name', 'backward_compatibility', 'copyright_text', 'copyright_url']);
        $response = $this->putJson(route('picture.update', $picture), $data);
        $response->assertJsonPath('data.id', $picture->id);
        $response->assertJsonPath('data.internal_name', $data['internal_name']);
        $response->assertJsonPath('data.copyright_text', $data['copyright_text']);
    }

}
