<?php

namespace Tests\Filament\Authorization;

use App\Http\Responses\Auth\PanelAwareTwoFactorChallengeViewResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\TwoFactorChallengeViewResponse;
use Tests\TestCase;

class PanelAwareTwoFactorChallengeViewResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_with_admin_panel_marker_redirects_to_filament_challenge_page(): void
    {
        $request = Request::create('/two-factor-challenge');
        $request->setLaravelSession($this->app['session']->driver());
        session()->put('filament.auth.panel', 'admin');

        $response = new PanelAwareTwoFactorChallengeViewResponse;
        $result = $response->toResponse($request);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(route('filament.admin.auth.two-factor-challenge'), $result->getTargetUrl());
    }

    public function test_without_panel_marker_delegates_to_default_fortify_view(): void
    {
        $request = Request::create('/two-factor-challenge');
        $request->setLaravelSession($this->app['session']->driver());

        $response = new PanelAwareTwoFactorChallengeViewResponse;
        $result = $response->toResponse($request);

        $this->assertNotInstanceOf(RedirectResponse::class, $result);
    }

    public function test_with_different_panel_marker_delegates_to_default_fortify_view(): void
    {
        $request = Request::create('/two-factor-challenge');
        $request->setLaravelSession($this->app['session']->driver());
        session()->put('filament.auth.panel', 'other-panel');

        $response = new PanelAwareTwoFactorChallengeViewResponse;
        $result = $response->toResponse($request);

        $this->assertNotInstanceOf(RedirectResponse::class, $result);
    }

    public function test_container_binding_resolves_to_panel_aware_response(): void
    {
        $resolved = app(TwoFactorChallengeViewResponse::class);

        $this->assertInstanceOf(PanelAwareTwoFactorChallengeViewResponse::class, $resolved);
    }
}
