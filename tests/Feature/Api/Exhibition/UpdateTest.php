<?php

namespace Tests\Feature\Api\Exhibition;

use App\Models\Exhibition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
    }

    public function it_updates_an_exhibition(): void
    {
        $exhibition = Exhibition::factory()->create(['internal_name' => 'old-name']);
        $response = $this->patchJson(route('exhibition.update', $exhibition), [
            'internal_name' => 'new-name',
        ]);
        $response->assertOk()->assertJsonPath('data.internal_name', 'new-name');
        $this->assertDatabaseHas('exhibitions', ['id' => $exhibition->id, 'internal_name' => 'new-name']);
    }
}
