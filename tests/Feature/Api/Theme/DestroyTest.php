<?php

namespace Tests\Feature\Api\Theme;

use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class DestroyTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createDataUser();
        $this->actingAs($this->user);
    }

    public function it_deletes_a_theme(): void
    {
        $theme = Theme::factory()->create();
        $response = $this->deleteJson(route('theme.destroy', $theme));
        $response->assertNoContent();
        $this->assertDatabaseMissing('themes', ['id' => $theme->id]);
    }
}
