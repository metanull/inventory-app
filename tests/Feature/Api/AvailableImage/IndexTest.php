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
use Tests\Traits\CreatesUsersWithPermissions;

class IndexTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase, WithFaker;

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

    public function test_index_allows_authenticated_users(): void
    {
        $response = $this->getJson(route('available-image.index'));
        $response->assertOk();
    }

    public function test_index_returns_ok_when_no_data(): void
    {
        $response = $this->getJson(route('available-image.index'));
        $response->assertOk();
    }

    public function test_index_returns_an_empty_array_when_no_data()
    {
        $response = $this->getJson(route('available-image.index'));
        $response->assertJsonCount(0, 'data');
    }

    public function test_index_returns_the_expected_structure(): void
    {
        $response = $this->getJson(route('available-image.index'));
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'path',
                    'comment',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    public function test_index_returns_the_expected_data(): void
    {
        $availableImage1 = AvailableImage::factory()->create();
        $availableImage2 = AvailableImage::factory()->create();

        $response = $this->getJson(route('available-image.index'));
        $response->assertJsonPath('data.0.id', $availableImage1->id)
            ->assertJsonPath('data.0.path', $availableImage1->path)
            ->assertJsonPath('data.0.comment', $availableImage1->comment)
            ->assertJsonPath('data.1.id', $availableImage2->id)
            ->assertJsonPath('data.1.path', $availableImage2->path)
            ->assertJsonPath('data.1.comment', $availableImage2->comment);
    }
}
