<?php

namespace Tests\Feature\Api\Theme;

use App\Models\Theme;
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

    public function it_updates_a_theme(): void
    {
        $theme = Theme::factory()->create(['internal_name' => 'old-theme']);
        $response = $this->patchJson(route('theme.update', $theme), [
            'internal_name' => 'new-theme',
        ]);
        $response->assertOk()->assertJsonPath('data.internal_name', 'new-theme');
        $this->assertDatabaseHas('themes', ['id' => $theme->id, 'internal_name' => 'new-theme']);
    }
}
