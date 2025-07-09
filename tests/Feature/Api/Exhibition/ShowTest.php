<?php

namespace Tests\Feature\Api\Exhibition;

use App\Models\Exhibition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function it_shows_an_exhibition(): void
    {
        $exhibition = Exhibition::factory()->create();
        $response = $this->getJson(route('exhibition.show', $exhibition));
        $response->assertOk()->assertJsonPath('data.id', $exhibition->id);
    }
}
