<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Languages;

use App\Models\Language;
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

    public function test_show_displays_core_fields(): void
    {
        $language = Language::factory()->create([
            'internal_name' => 'Alpha Language',
            'backward_compatibility' => 'LEG-LANG',
        ]);

        $response = $this->get(route('languages.show', $language));
        $response->assertOk();
        $response->assertSee('Alpha Language');
        $response->assertSee('Legacy: LEG-LANG');
        $response->assertSee('Information');
    }
}
