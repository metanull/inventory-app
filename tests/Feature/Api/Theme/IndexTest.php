<?php

namespace Tests\Feature\Api\Theme;

use App\Models\Theme;
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

    public function it_lists_themes(): void
    {
        Theme::factory()->count(2)->create();
        $response = $this->getJson(route('theme.index'));
        $response->assertOk()->assertJsonStructure(['data']);
    }
}
