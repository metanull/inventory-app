<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Languages;

use App\Models\Language;
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

    public function test_update_persists_changes_and_redirects(): void
    {
        $language = Language::factory()->create([
            'internal_name' => 'Old Lang',
        ]);

        $response = $this->put(route('languages.update', $language), [
            'internal_name' => 'New Lang',
        ]);

        $response->assertRedirect(route('languages.show', $language));
        $this->assertDatabaseHas('languages', [
            'id' => $language->id,
            'internal_name' => 'New Lang',
        ]);
    }
}
