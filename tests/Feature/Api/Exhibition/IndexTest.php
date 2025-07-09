<?php

namespace Tests\Feature\Api\Exhibition;

use App\Models\Exhibition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function it_lists_exhibitions(): void
    {
        Exhibition::factory()->count(2)->create();
        $response = $this->getJson(route('exhibition.index'));
        $response->assertOk()->assertJsonStructure(['data']);
    }
}
