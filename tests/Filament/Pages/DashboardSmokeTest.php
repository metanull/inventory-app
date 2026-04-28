<?php

namespace Tests\Filament\Pages;

use App\Enums\ItemType;
use App\Enums\Permission;
use App\Filament\Pages\Dashboard;
use App\Models\Item;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_for_authorized_user(): void
    {
        $user = $this->createViewUser();

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Dashboard');
    }

    public function test_dashboard_loads_under_1000ms_with_100k_items(): void
    {
        $user = $this->createViewUser();
        $this->seedItems(100_000);

        $start = microtime(true);
        $response = $this->actingAs($user)->get('/admin');
        $elapsed = (microtime(true) - $start) * 1000;

        $response->assertOk();
        $this->assertLessThan(1000, $elapsed, "Dashboard took {$elapsed}ms, expected < 1000ms");
    }

    public function test_dashboard_shows_inventory_stats_for_authorized_user(): void
    {
        $user = $this->createViewUser();

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertSuccessful();
    }

    public function test_dashboard_shows_quick_create_actions_for_user_with_create_permission(): void
    {
        $user = $this->createCrudUser();

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertSuccessful()
            ->assertSeeHtml('New Item');
    }

    protected function createViewUser(): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([
            Permission::ACCESS_ADMIN_PANEL->value,
            Permission::VIEW_DATA->value,
        ]);

        return $user;
    }

    protected function createCrudUser(): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([
            Permission::ACCESS_ADMIN_PANEL->value,
            Permission::VIEW_DATA->value,
            Permission::CREATE_DATA->value,
            Permission::UPDATE_DATA->value,
            Permission::DELETE_DATA->value,
        ]);

        return $user;
    }

    protected function seedItems(int $count): void
    {
        $timestamp = Carbon::now();

        $rows = [];
        for ($i = 0; $i < $count; $i++) {
            $rows[] = [
                'id' => (string) Str::uuid(),
                'internal_name' => sprintf('Item %05d', $i),
                'backward_compatibility' => sprintf('itm-%05d', $i),
                'type' => ItemType::OBJECT->value,
                'partner_id' => null,
                'parent_id' => null,
                'project_id' => null,
                'country_id' => null,
                'display_order' => null,
                'owner_reference' => null,
                'mwnf_reference' => null,
                'start_date' => null,
                'end_date' => null,
                'latitude' => null,
                'longitude' => null,
                'map_zoom' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        foreach (array_chunk($rows, 1000) as $chunk) {
            Item::query()->insert($chunk);
        }
    }

    protected function setCurrentPanel(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }
}
