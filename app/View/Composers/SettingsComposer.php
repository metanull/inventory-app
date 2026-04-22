<?php

namespace App\View\Composers;

use App\Services\Settings;
use Illuminate\View\View;

class SettingsComposer
{
    public function __construct(private readonly Settings $settings)
    {
        //
    }

    public function compose(View $view): void
    {
        $view->with('selfRegistrationEnabled', $this->settings->selfRegistrationEnabled());
    }
}
