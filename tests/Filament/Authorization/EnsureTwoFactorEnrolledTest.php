<?php

namespace Tests\Filament\Authorization;

use App\Enums\Permission;
use App\Http\Middleware\Filament\EnsureTwoFactorEnrolled;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Tests\TestCase;

class EnsureTwoFactorEnrolledTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_without_confirmed_2fa_hitting_admin_is_redirected_to_setup(): void
    {
        $user = User::factory()->create([
            'two_factor_confirmed_at' => null,
        ]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertRedirect(route('filament.admin.auth.two-factor-setup'));
    }

    public function test_authenticated_user_without_confirmed_2fa_hitting_setup_page_is_not_redirected(): void
    {
        $user = User::factory()->create([
            'two_factor_confirmed_at' => null,
        ]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        // The setup page itself should not cause an infinite redirect loop
        $request = Request::create('/admin/two-factor-setup');
        $request->setRouteResolver(function () {
            return tap(new Route('GET', '/admin/two-factor-setup', []), function ($route) {
                $route->name('filament.admin.auth.two-factor-setup');
            });
        });

        $middleware = new EnsureTwoFactorEnrolled;
        $nextCalled = false;
        $response = $middleware->handle($request, function ($req) use (&$nextCalled) {
            $nextCalled = true;

            return response('ok');
        });

        $this->assertTrue($nextCalled, 'Middleware should pass through for the setup page');
    }

    public function test_authenticated_user_without_confirmed_2fa_hitting_logout_is_not_redirected(): void
    {
        $user = User::factory()->create([
            'two_factor_confirmed_at' => null,
        ]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        $request = Request::create('/admin/logout', 'POST');
        $request->setRouteResolver(function () {
            return tap(new Route('POST', '/admin/logout', []), function ($route) {
                $route->name('filament.admin.auth.logout');
            });
        });

        $this->actingAs($user);

        $middleware = new EnsureTwoFactorEnrolled;
        $nextCalled = false;
        $response = $middleware->handle($request, function ($req) use (&$nextCalled) {
            $nextCalled = true;

            return response('ok');
        });

        $this->assertTrue($nextCalled, 'Middleware should pass through for the logout route');
    }

    public function test_authenticated_user_with_confirmed_2fa_hitting_admin_is_not_redirected(): void
    {
        $user = User::factory()->create([
            'two_factor_confirmed_at' => now(),
        ]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertOk();
    }

    public function test_unauthenticated_request_is_not_handled_by_ensure_two_factor_enrolled(): void
    {
        // Unauthenticated request should be redirected to login by Authenticate middleware, not setup
        $response = $this->get('/admin');

        $response->assertRedirect(url('/admin/login'));
    }
}
