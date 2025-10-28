<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Partner;

use App\Models\Partner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class ShowTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsDataUser();
    }

    public function test_show_displays_partner_detail(): void
    {
        $partner = Partner::factory()->create();
        $response = $this->get(route('partners.show', $partner));
        $response->assertOk();
        $response->assertSee(e($partner->internal_name));
    }
}
