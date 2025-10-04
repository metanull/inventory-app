<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:manage settings']);
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

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'self_registration_enabled' => 'required|boolean',
        ]);

        Setting::set(
            'self_registration_enabled',
            $request->boolean('self_registration_enabled'),
            'boolean',
            'Allow new users to register themselves (they will be assigned Non-verified users role)'
        );

        return redirect()->route('settings.index')
            ->with('success', 'Settings updated successfully.');
    }
}
