<?php

namespace App\Providers\Filament;

use App\Filament\Auth\Login;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    private const LIGHT_LOGO_CLASSES = 'h-full w-auto text-blue-900';

    private const DARK_LOGO_CLASSES = 'h-full w-auto text-indigo-200';

    public function panel(Panel $panel): Panel
    {
        $panel = $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->authGuard((string) config('fortify.guard'))
            ->authPasswordBroker((string) config('fortify.passwords'))
            ->brandName((string) config('app.name'))
            ->brandLogo($this->brandLogo(self::LIGHT_LOGO_CLASSES))
            ->darkModeBrandLogo($this->brandLogo(self::DARK_LOGO_CLASSES))
            ->brandLogoHeight('2rem')
            ->darkMode()
            ->defaultThemeMode(ThemeMode::System)
            ->font('Inter')
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);

        if (! $this->shouldUseViteTheme()) {
            return $panel;
        }

        return $panel->viteTheme('resources/css/filament/admin/theme.css');
    }

    protected function brandLogo(string $classes): HtmlString
    {
        return new HtmlString(
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="'.htmlspecialchars($classes, ENT_QUOTES, 'UTF-8').'">'
            .'<path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />'
            .'</svg>'
        );
    }

    protected function shouldUseViteTheme(): bool
    {
        return file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json'));
    }
}
