<?php

namespace Tests\Web\Pages;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $manager = User::factory()->create(['email_verified_at' => now()]);
        $manager->givePermissionTo(Permission::MANAGE_USERS);
        $this->actingAs($manager);
    }

    public function test_index_renders_request_driven_user_page_without_livewire_markup(): void
    {
        User::factory()->create(['name' => 'Alice User', 'email' => 'alice@example.com']);

        $response = $this->get(route('admin.users.index'));

        $response
            ->assertOk()
            ->assertViewIs('admin.users.index')
            ->assertSee('Alice User');

        $this->assertStringNotContainsString('livewire:dynamic-component', $response->getContent());
        $this->assertStringNotContainsString('wire:click', $response->getContent());
    }

    public function test_index_can_search_and_filter_by_role(): void
    {
        $curatorRole = Role::create(['name' => 'Curator', 'guard_name' => 'web']);
        $editorRole = Role::create(['name' => 'Editor', 'guard_name' => 'web']);

        $matching = User::factory()->create(['name' => 'Alice', 'email' => 'alice-temple@example.com']);
        $matching->assignRole($curatorRole);

        $other = User::factory()->create(['name' => 'Bob', 'email' => 'bob@example.com']);
        $other->assignRole($editorRole);

        $response = $this->get(route('admin.users.index', [
            'q' => 'alice-temple',
            'role' => 'Curator',
        ]));

        $response
            ->assertOk()
            ->assertSee('Alice')
            ->assertDontSee('Bob');
    }

    public function test_index_normalizes_non_whitelisted_sort_columns_to_the_default(): void
    {
        $response = $this->get(route('admin.users.index', ['sort' => 'password']));

        $response
            ->assertOk()
            ->assertViewHas('listState', fn ($listState): bool => $listState->sort === 'name');
    }

    public function test_index_preserves_query_strings_in_pagination_and_sort_links(): void
    {
        $curatorRole = Role::create(['name' => 'Curator', 'guard_name' => 'web']);

        foreach (range(1, 11) as $index) {
            $user = User::factory()->create([
                'name' => 'User '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'email' => 'temple-user-'.$index.'@example.com',
            ]);
            $user->assignRole($curatorRole);
        }

        $response = $this->get(route('admin.users.index', [
            'role' => 'Curator',
            'q' => 'temple-user',
            'per_page' => 10,
            'sort' => 'name',
            'direction' => 'asc',
        ]));

        $response->assertOk();

        $paginator = $response->viewData('users');
        $nextPageUrl = $paginator->nextPageUrl();

        $this->assertNotNull($nextPageUrl);
        $this->assertStringContainsString('role=Curator', $nextPageUrl);
        $this->assertStringContainsString('q=temple-user', $nextPageUrl);
        $this->assertStringContainsString('per_page=10', $nextPageUrl);
        $this->assertStringContainsString(
            'href="http://localhost/web/admin/users?role=Curator&amp;q=temple-user&amp;per_page=10&amp;sort=created_at&amp;direction=asc&amp;page=1"',
            $response->getContent(),
        );
    }

    public function test_index_requires_manage_users_permission(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.users.index'));

        $response->assertForbidden();
    }
}
