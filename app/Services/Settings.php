<?php

namespace App\Services;

use App\Models\Setting;

class Settings
{
    private ?bool $selfRegistrationEnabled = null;

    public function selfRegistrationEnabled(): bool
    {
        if ($this->selfRegistrationEnabled === null) {
            $this->selfRegistrationEnabled = (bool) Setting::get('self_registration_enabled', false);
        }

        return $this->selfRegistrationEnabled;
    }
}
