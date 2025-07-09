<?php

namespace Tests\Feature\Api\Theme;

use App\Models\Theme;
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

    public function it_shows_a_theme(): void
    {
        $theme = Theme::factory()->create();
        $response = $this->getJson(route('theme.show', $theme));
        $response->assertOk()->assertJsonPath('data.id', $theme->id);
    }
}
