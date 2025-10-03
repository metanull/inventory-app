<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:manage users']);
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::with('roles');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->has('role') && $request->role) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        $users = $query->paginate(20)->withQueryString();
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = Role::all();

        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'roles' => ['array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        // Generate a secure password
        $generatedPassword = $this->generateSecurePassword();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($generatedPassword),
        ]);

        if (isset($validated['roles'])) {
            // Convert role IDs to role objects for syncRoles
            $roles = Role::whereIn('id', $validated['roles'])->get();
            $user->syncRoles($roles);
        } else {
            // If no roles are provided, remove all roles
            $user->syncRoles([]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.')
            ->with('generated_password', $generatedPassword)
            ->with('user_name', $user->name)
            ->with('user_email', $user->email);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load('roles.permissions');

        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $user->load('roles');

        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'roles' => ['array'],
            'roles.*' => ['exists:roles,id'],
            'verify_email' => ['nullable', 'boolean'],
            'unverify_email' => ['nullable', 'boolean'],
            'generate_new_password' => ['nullable', 'boolean'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        $generatedPassword = null;
        if (! empty($validated['generate_new_password'])) {
            $generatedPassword = $this->generateSecurePassword();
            $user->update(['password' => Hash::make($generatedPassword)]);
        }

        if (isset($validated['roles'])) {
            // Convert role IDs to role objects for syncRoles
            $roles = Role::whereIn('id', $validated['roles'])->get();
            $user->syncRoles($roles);
        } else {
            // If no roles are provided, remove all roles
            $user->syncRoles([]);
        }

        // Handle email verification management
        if (! empty($validated['verify_email']) && ! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        } elseif (! empty($validated['unverify_email']) && $user->hasVerifiedEmail()) {
            $user->email_verified_at = null;
            $user->save();
        }

        $redirect = redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');

        if ($generatedPassword) {
            $redirect->with('generated_password', $generatedPassword)
                ->with('user_name', $user->name)
                ->with('user_email', $user->email);
        }

        return $redirect;
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Generate a secure password.
     */
    private function generateSecurePassword(): string
    {
        $length = 16;
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $password;
    }
}
