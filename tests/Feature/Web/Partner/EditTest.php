<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Partner;

use App\Models\Partner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class EditTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsDataUser();
    }

    public function test_edit_form_renders(): void
    {
        $partner = Partner::factory()->create();
        $response = $this->get(route('partners.edit', $partner));
        $response->assertOk();
        $response->assertSee('Edit Partner');
    }
}
