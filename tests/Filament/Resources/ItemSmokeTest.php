<?php

namespace Tests\Filament\Resources;

use App\Enums\ItemType;
use App\Enums\Permission;
use App\Filament\Pages\BrowseItemTree;
use App\Filament\Resources\ItemResource\Pages\ListItem;
use App\Models\Item;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class ItemSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_item_resource_handles_a_ten_thousand_row_dataset(): void
    {
        $user = $this->createAuthorizedUser();
        $this->seedItems(10_000);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->actingAs($user)->get('/admin/items');

        $response->assertOk();
        $this->assertLessThan(50, count(DB::getQueryLog()));
        $this->assertLessThan(512 * 1024, strlen($response->getContent()));
        DB::disableQueryLog();

        $this->setCurrentPanel();

        $expectedFirstPage = Item::query()
            ->orderBy('internal_name')
            ->limit(10)
            ->get();

        $target = Item::query()->where('internal_name', 'Item 09999')->firstOrFail();
        $nonTarget = Item::query()->where('internal_name', 'Item 00000')->firstOrFail();

        Livewire::actingAs($user)
            ->test(ListItem::class)
            ->assertCanSeeTableRecords($expectedFirstPage, inOrder: true)
            ->searchTable('Item 09999')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$nonTarget]);
    }

    public function test_browse_item_tree_expands_five_levels_deep(): void
    {
        $user = $this->createAuthorizedUser();
        $hierarchy = $this->seedHierarchy(5);

        $this->setCurrentPanel();

        $component = Livewire::actingAs($user)
            ->test(BrowseItemTree::class);

        $component->assertSee($hierarchy[0]->internal_name);

        foreach ($hierarchy as $depth => $node) {
            $component->call('expand', $node->id);

            if ($depth + 1 < count($hierarchy)) {
                $component->assertSee($hierarchy[$depth + 1]->internal_name);
            }
        }

        $this->assertLessThan(2 * 1024 * 1024, strlen($component->html()));
    }

    protected function createAuthorizedUser(): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([
            Permission::ACCESS_ADMIN_PANEL->value,
            Permission::VIEW_DATA->value,
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

    /**
     * Seed a chain of items: root → level1 → level2 → … → level(depth-1).
     *
     * @return array<int, Item>
     */
    protected function seedHierarchy(int $depth): array
    {
        $nodes = [];
        $parentId = null;
        $types = [ItemType::OBJECT, ItemType::MONUMENT, ItemType::DETAIL, ItemType::PICTURE, ItemType::DETAIL];

        for ($i = 0; $i < $depth; $i++) {
            $node = Item::factory()->create([
                'internal_name' => sprintf('Hierarchy Level %d', $i),
                'type' => $types[$i] ?? ItemType::DETAIL,
                'parent_id' => $parentId,
            ]);
            $nodes[] = $node;
            $parentId = $node->id;
        }

        return $nodes;
    }

    protected function setCurrentPanel(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }
}
