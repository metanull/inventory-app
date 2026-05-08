<?php

namespace Tests\Filament\Authorization;

use App\Enums\Permission;
use App\Models\Timeline;
use App\Models\TimelineEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimelineResourceAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_without_view_data_permission_cannot_see_timeline_navigation_or_pages(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        $this->actingAs($user)->get('/admin')
            ->assertOk()
            ->assertDontSee('Timelines');

        $this->actingAs($user)->get('/admin/timelines')
            ->assertForbidden();
    }

    public function test_view_only_users_can_open_timeline_index_and_view_but_not_create_or_edit(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([
            Permission::ACCESS_ADMIN_PANEL->value,
            Permission::VIEW_DATA->value,
        ]);

        $timeline = Timeline::factory()->create([
            'internal_name' => 'Islamic Timeline',
        ]);

        $this->actingAs($user)->get('/admin')
            ->assertOk()
            ->assertSee('Timelines');

        $this->actingAs($user)->get('/admin/timelines')
            ->assertOk()
            ->assertSee('Islamic Timeline');

        $this->actingAs($user)->get("/admin/timelines/{$timeline->getKey()}")
            ->assertOk()
            ->assertSee('Islamic Timeline');

        $this->actingAs($user)->get('/admin/timelines/create')
            ->assertForbidden();

        $this->actingAs($user)->get("/admin/timelines/{$timeline->getKey()}/edit")
            ->assertForbidden();
    }

    public function test_users_without_view_data_cannot_access_timeline_event_pages(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        $this->actingAs($user)->get('/admin')
            ->assertOk()
            ->assertDontSee('Timeline Events');

        $this->actingAs($user)->get('/admin/timeline-events')
            ->assertForbidden();
    }

    public function test_view_only_users_can_access_timeline_event_view_pages(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([
            Permission::ACCESS_ADMIN_PANEL->value,
            Permission::VIEW_DATA->value,
        ]);

        $timeline = Timeline::factory()->create(['internal_name' => 'Test Timeline']);
        $event = TimelineEvent::factory()->create([
            'timeline_id' => $timeline->id,
            'internal_name' => 'Medieval Period',
        ]);

        $this->actingAs($user)->get('/admin/timeline-events')
            ->assertOk()
            ->assertSee('Medieval Period');

        $this->actingAs($user)->get("/admin/timeline-events/{$event->getKey()}")
            ->assertOk()
            ->assertSee('Medieval Period');

        $this->actingAs($user)->get('/admin/timeline-events/create')
            ->assertForbidden();

        $this->actingAs($user)->get("/admin/timeline-events/{$event->getKey()}/edit")
            ->assertForbidden();
    }
}
