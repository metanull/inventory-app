<?php

namespace Tests\Web\Pages;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleManagementIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $manager = User::factory()->create(['email_verified_at' => now()]);
        $manager->givePermissionTo(Permission::MANAGE_ROLES);
        $this->actingAs($manager);
    }

    public function test_index_renders_request_driven_role_page_without_livewire_markup(): void
    {
        Role::create(['name' => 'Temple Role', 'guard_name' => 'web', 'description' => 'Temple access']);

        $response = $this->get(route('admin.roles.index'));

        $response
            ->assertOk()
            ->assertViewIs('admin.roles.index')
            ->assertSee('Temple Role');

        $this->assertStringNotContainsString('livewire:dynamic-component', $response->getContent());
        $this->assertStringNotContainsString('wire:click', $response->getContent());
    }

    public function test_index_can_search_by_description(): void
    {
        Role::create(['name' => 'Temple Role', 'guard_name' => 'web', 'description' => 'Temple access']);
        Role::create(['name' => 'Other Role', 'guard_name' => 'web', 'description' => 'Other access']);

        $response = $this->get(route('admin.roles.index', ['q' => 'Temple']));

        $response
            ->assertOk()
            ->assertSee('Temple Role')
            ->assertDontSee('Other Role');
    }

    public function test_index_normalizes_non_whitelisted_sort_columns_to_the_default(): void
    {
        $response = $this->get(route('admin.roles.index', ['sort' => 'guard_name']));

        $response
            ->assertOk()
            ->assertViewHas('listState', fn ($listState): bool => $listState->sort === 'name');
    }

    public function test_index_preserves_query_strings_in_pagination_and_sort_links(): void
    {
        foreach (range(1, 11) as $index) {
            Role::create([
                'name' => 'Temple Role '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'guard_name' => 'web',
                'description' => 'Temple description '.$index,
            ]);
        }

        $response = $this->get(route('admin.roles.index', [
            'q' => 'Temple',
            'per_page' => 10,
            'sort' => 'name',
            'direction' => 'asc',
        ]));

        $response->assertOk();

        $paginator = $response->viewData('roles');
        $nextPageUrl = $paginator->nextPageUrl();

        $this->assertNotNull($nextPageUrl);
        $this->assertStringContainsString('q=Temple', $nextPageUrl);
        $this->assertStringContainsString('per_page=10', $nextPageUrl);
        $this->assertStringContainsString(
            'href="http://localhost/web/admin/roles?q=Temple&amp;per_page=10&amp;sort=created_at&amp;direction=asc&amp;page=1"',
            $response->getContent(),
        );
    }

    public function test_index_requires_manage_roles_permission(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.roles.index'));

        $response->assertForbidden();
    }
}
