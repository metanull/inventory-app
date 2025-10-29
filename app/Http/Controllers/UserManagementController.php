<?php

namespace App\Http\Controllers;

use App\Enums\Permission;
use App\Http\Requests\Web\IndexUserManagementRequest;
use App\Http\Requests\Web\StoreUserManagementRequest;
use App\Http\Requests\Web\UpdateUserManagementRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:'.Permission::MANAGE_USERS->value]);
    }

    /**
     * Display a listing of users.
     */
    public function index(IndexUserManagementRequest $request)
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
    public function store(StoreUserManagementRequest $request)
    {
        $validated = $request->validated();

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
        $isEditingSelf = Auth::id() === $user->id;

        return view('admin.users.edit', compact('user', 'roles', 'isEditingSelf'));
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateUserManagementRequest $request, User $user)
    {
        $validated = $request->validated();

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        $generatedPassword = null;
        if (! empty($validated['generate_new_password'])) {
            $generatedPassword = $this->generateSecurePassword();
            $user->update(['password' => Hash::make($generatedPassword)]);
        }

        // Prevent users from editing their own role assignments
        if (Auth::id() !== $user->id) {
            if (isset($validated['roles'])) {
                // Convert role IDs to role objects for syncRoles
                $roles = Role::whereIn('id', $validated['roles'])->get();
                $user->syncRoles($roles);
            } else {
                // If no roles are provided, remove all roles
                $user->syncRoles([]);
            }
        }

        // Handle email verification management
        if (! empty($validated['verify_email']) && ! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        } elseif (! empty($validated['unverify_email']) && $user->hasVerifiedEmail()) {
            $user->email_verified_at = null;
            $user->save();
        }

        if ($generatedPassword) {
            return redirect()->route('admin.users.index')
                ->with('generated_password', $generatedPassword)
                ->with('user_name', $user->name)
                ->with('user_email', $user->email);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
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
