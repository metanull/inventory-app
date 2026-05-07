<?php

namespace Tests\Filament\Resources;

use App\Enums\Permission;
use App\Filament\Resources\CollectionResource\Pages\ViewCollection;
use App\Models\Collection;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CombinedRelationManagerContentTabLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_combined_relation_manager_content_tab_infolist_uses_full_width_layout(): void
    {
        $user = $this->createViewUser();
        $collection = Collection::factory()->create();

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $component = Livewire::actingAs($user)
            ->test(ViewCollection::class, ['record' => $collection->getRouteKey()]);

        $this->assertSame(1, $component->instance()->getInfolist('infolist')->getColumns('lg'));
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
}
