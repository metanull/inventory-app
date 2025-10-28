<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Collections;

use App\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class ShowTest extends TestCase
{
    use RefreshDatabase;
    use CreatesUsersWithPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsDataUser();
    }

    public function test_show_displays_core_fields(): void
    {
        $collection = Collection::factory()->create([
            'internal_name' => 'Alpha Collection',
            'backward_compatibility' => 'LEG-COL',
        ]);

        $response = $this->get(route('collections.show', $collection));
        $response->assertOk();
        $response->assertSee('Alpha Collection');
        $response->assertSee('Legacy: LEG-COL');
        $response->assertSee('Information');
    }
}
