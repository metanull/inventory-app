<?php

namespace Tests\Unit\View\Composers;

use App\Models\Setting;
use App\Services\Settings;
use App\View\Composers\SettingsComposer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\View\View;
use Tests\TestCase;

class SettingsComposerTest extends TestCase
{
    use RefreshDatabase;

    public function test_compose_binds_false_when_setting_is_absent(): void
    {
        Setting::query()->delete();

        $settings = new Settings;
        $composer = new SettingsComposer($settings);

        $view = $this->createMock(View::class);
        $view->expects($this->once())
            ->method('with')
            ->with('selfRegistrationEnabled', false);

        $composer->compose($view);
    }

    public function test_compose_binds_true_when_setting_is_enabled(): void
    {
        Setting::set('self_registration_enabled', true, 'boolean');

        $settings = new Settings;
        $composer = new SettingsComposer($settings);

        $view = $this->createMock(View::class);
        $view->expects($this->once())
            ->method('with')
            ->with('selfRegistrationEnabled', true);

        $composer->compose($view);
    }

    public function test_compose_binds_false_when_setting_is_disabled(): void
    {
        Setting::set('self_registration_enabled', false, 'boolean');

        $settings = new Settings;
        $composer = new SettingsComposer($settings);

        $view = $this->createMock(View::class);
        $view->expects($this->once())
            ->method('with')
            ->with('selfRegistrationEnabled', false);

        $composer->compose($view);
    }

    public function test_settings_service_caches_result_within_same_instance(): void
    {
        Setting::set('self_registration_enabled', true, 'boolean');

        $settings = new Settings;

        $first = $settings->selfRegistrationEnabled();

        // Delete setting after first call — cached value must be returned
        Setting::query()->delete();

        $second = $settings->selfRegistrationEnabled();

        $this->assertTrue($first);
        $this->assertTrue($second);
    }
}
