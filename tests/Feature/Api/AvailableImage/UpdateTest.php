<?php

namespace Tests\Feature\Api\AvailableImage;

use App\Models\AvailableImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
        Event::fake();
        Http::fake();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_update_allows_authenticated_users(): void
    {
        $availableImage = AvailableImage::factory()->create();

        $response = $this->putJson(route('available-image.update', $availableImage->id), [
            'comment' => $this->faker->sentence(),
        ]);
        $response->assertOk();
    }

    public function test_update_validates_its_input(): void
    {
        $availableImage = AvailableImage::factory()->create();

        $response = $this->putJson(route('available-image.update', $availableImage->id), [
            'path' => 'prohibited field',   // This field should not be allowed
            'comment' => null,              // This field allows null
        ]);
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['path']);
    }

    public function test_update_updates_a_row(): void
    {
        $availableImage = AvailableImage::factory()->create();

        $this->putJson(route('available-image.update', $availableImage->id), [
            'comment' => 'Updated comment',
        ]);
        $this->assertDatabaseHas('available_images', [
            'id' => $availableImage->id,
            'comment' => 'Updated comment',
        ]);
    }

    public function test_update_returns_ok_on_success(): void
    {
        $availableImage = AvailableImage::factory()->create();

        $response = $this->putJson(route('available-image.update', $availableImage->id), [
            'comment' => 'Updated comment',
        ]);
        $response->assertOk();
    }

    public function test_update_returns_unprocessable_when_input_is_invalid(): void
    {
        $availableImage = AvailableImage::factory()->create();

        $response = $this->putJson(route('available-image.update', $availableImage->id), [
            'path' => 'invalid path', // This should not be allowed
            'comment' => null,        // This field allows null
        ]);
        $response->assertUnprocessable();
    }

    public function test_update_returns_not_found_response_when_not_found(): void
    {
        $response = $this->putJson(route('available-image.update', 'non-existent-id'), [
            'comment' => $this->faker->sentence(),
        ]);
        $response->assertNotFound();
    }

    public function test_update_returns_the_expected_structure(): void
    {
        $availableImage = AvailableImage::factory()->create();

        $response = $this->putJson(route('available-image.update', $availableImage->id), [
            'comment' => 'Updated comment',
        ]);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'path',
                'comment',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    public function test_update_returns_the_expected_data(): void
    {
        $availableImage = AvailableImage::factory()->create();

        $response = $this->putJson(route('available-image.update', $availableImage->id), [
            'comment' => 'Updated comment',
        ]);
        $response->assertJsonPath('data.id', $availableImage->id)
            ->assertJsonPath('data.path', $availableImage->path)
            ->assertJsonPath('data.comment', 'Updated comment');
    }
}
