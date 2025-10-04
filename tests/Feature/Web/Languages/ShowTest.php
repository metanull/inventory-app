<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Languages;

use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class ShowTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
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
