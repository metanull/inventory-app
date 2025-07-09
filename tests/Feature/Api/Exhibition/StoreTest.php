<?php

namespace Tests\Feature\Api\Exhibition;

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

    public function it_creates_an_exhibition(): void
    {
        $data = [
            'internal_name' => 'unique-exhibition',
        ];
        $response = $this->postJson(route('exhibition.store'), $data);
        $response->assertCreated()->assertJsonPath('data.internal_name', 'unique-exhibition');
        $this->assertDatabaseHas('exhibitions', ['internal_name' => 'unique-exhibition']);
    }
}
