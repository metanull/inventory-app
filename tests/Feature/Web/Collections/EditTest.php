<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Collections;

use App\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class EditTest extends TestCase
{
    use RefreshDatabase;
    use CreatesUsersWithPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsDataUser();
    }

    public function test_edit_form_renders(): void
    {
        $collection = Collection::factory()->create();
        $response = $this->get(route('collections.edit', $collection));
        $response->assertOk();
        $response->assertSee('Edit Collection');
    }
}
