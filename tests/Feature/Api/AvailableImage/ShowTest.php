<?php

namespace Tests\Feature\Api\AvailableImage;

use App\Models\AvailableImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class ShowTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
        Event::fake();
        Http::fake();
        $this->user = $this->createVisitorUser();
        $this->actingAs($this->user);
    }

    public function test_show_allows_authenticated_users(): void
    {
        $availableImage = AvailableImage::factory()->create();

        $response = $this->getJson(route('available-image.show', $availableImage->id));
        $response->assertOk();
    }

    public function test_show_returns_not_found_when_not_found(): void
    {
        $response = $this->getJson(route('available-image.show', 'non-existent-id'));
        $response->assertNotFound();
    }

    public function test_show_returns_the_default_structure_without_relations(): void
    {
        $availableImage = AvailableImage::factory()->create();

        $response = $this->getJson(route('available-image.show', $availableImage->id));
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

    public function test_show_returns_the_expected_data(): void
    {
        $availableImage = AvailableImage::factory()->create();

        $response = $this->getJson(route('available-image.show', $availableImage->id));
        $response->assertJsonPath('data.id', $availableImage->id)
            ->assertJsonPath('data.path', $availableImage->path)
            ->assertJsonPath('data.comment', $availableImage->comment);
    }
}
