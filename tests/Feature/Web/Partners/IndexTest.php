<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Partners;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_partners_index_renders_and_lists_partners(): void
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        Partner::factory()->count(2)->create();

        $this->actingAs($user);
        $response = $this->get(route('partners.index'));
        $response->assertOk();
        $response->assertSee('Partners');
        $response->assertSee('Add Partner');
        $response->assertSee('Search');
    }

    public function test_partners_index_search_filters_results(): void
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        Partner::factory()->create(['internal_name' => 'OMEGA_ENTITY']);
        Partner::factory()->create(['internal_name' => 'DELTA_ENTITY']);

        $this->actingAs($user);
        $response = $this->get(route('partners.index', ['q' => 'OMEGA']));
        $response->assertOk();
        $response->assertSee('OMEGA_ENTITY');
        $response->assertDontSee('DELTA_ENTITY');
    }
}
