<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Languages;

use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_destroy_deletes_and_redirects(): void
    {
        $language = Language::factory()->create();

        $response = $this->delete(route('languages.destroy', $language));
        $response->assertRedirect(route('languages.index'));
        $this->assertDatabaseMissing('languages', ['id' => $language->id]);
    }
}
