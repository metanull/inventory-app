<?php

namespace Tests\Feature\Api\Theme;

use App\Models\Exhibition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function it_creates_a_theme(): void
    {
        $exhibition = Exhibition::factory()->create();
        $data = [
            'exhibition_id' => $exhibition->id,
            'internal_name' => 'unique-theme',
        ];
        $response = $this->postJson(route('theme.store'), $data);
        $response->assertCreated()->assertJsonPath('data.internal_name', 'unique-theme');
        $this->assertDatabaseHas('themes', ['internal_name' => 'unique-theme']);
    }
}
