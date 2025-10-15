<?php

namespace App\Http\Controllers;

use App\Enums\Permission;
use App\Http\Requests\Web\UpdateSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:'.Permission::MANAGE_SETTINGS->value]);
    }

    public function index(): View
    {
        $settings = [
            'self_registration_enabled' => [
                'key' => 'self_registration_enabled',
                'label' => 'Self-Registration',
                'description' => 'Allow new users to create accounts (they will need manager approval)',
                'type' => 'boolean',
                'value' => Setting::get('self_registration_enabled', false),
            ],
        ];

        return view('settings.index', compact('settings'));
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Setting::set(
            'self_registration_enabled',
            $validated['self_registration_enabled'],
            'boolean',
            'Allow new users to register themselves (they will be assigned Non-verified users role)'
        );

        return redirect()->route('settings.index')
            ->with('success', 'Settings updated successfully.');
    }
}
