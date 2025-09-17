<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Partners;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_show_page_displays_core_fields(): void
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $partner = Partner::factory()->create(['backward_compatibility' => 'OLDP1']);

        $this->actingAs($user);
        $response = $this->get(route('partners.show', $partner));
        $response->assertOk();
        $response->assertSee($partner->internal_name);
        $response->assertSee('Partner Detail');
        // Backward compatibility now displayed as plain value in definition list
        $response->assertSee('OLDP1');
    }
}
