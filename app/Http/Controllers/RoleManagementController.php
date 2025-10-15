<?php

namespace App\Http\Controllers;

use App\Enums\Permission as PermissionEnum;
use App\Http\Requests\Web\IndexRoleManagementRequest;
use App\Http\Requests\Web\StoreRoleManagementRequest;
use App\Http\Requests\Web\UpdatePermissionsRoleManagementRequest;
use App\Http\Requests\Web\UpdateRoleManagementRequest;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:'.PermissionEnum::MANAGE_ROLES->value]);
    }

    /**
     * Display a listing of roles.
     */
    public function index(IndexRoleManagementRequest $request)
    {
        $query = Role::with('permissions');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $roles = $query->paginate(20)->withQueryString();

        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $permissions = Permission::all();

        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role.
     */
    public function store(StoreRoleManagementRequest $request)
    {
        $validated = $request->validated();

        $role = Role::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'guard_name' => 'web',
        ]);

        if (isset($validated['permissions'])) {
            $permissions = Permission::whereIn('id', $validated['permissions'])->get();
            $role->syncPermissions($permissions);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role)
    {
        $role->load('permissions');

        return view('admin.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        $permissions = Permission::all();
        $role->load('permissions');

        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified role.
     */
    public function update(UpdateRoleManagementRequest $request, Role $role)
    {
        $validated = $request->validated();

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        if (isset($validated['permissions'])) {
            $permissions = Permission::whereIn('id', $validated['permissions'])->get();
            $role->syncPermissions($permissions);
        } else {
            // If no permissions are provided, remove all permissions
            $role->syncPermissions([]);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role)
    {
        // Check if role has users assigned
        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Cannot delete role that has users assigned. Please remove users from this role first.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    /**
     * Show the permissions management page for a role.
     */
    public function permissions(Role $role)
    {
        $role->load('permissions');
        $permissions = Permission::all();

        return view('admin.roles.permissions', compact('role', 'permissions'));
    }

    /**
     * Update the permissions for a role.
     */
    public function updatePermissions(UpdatePermissionsRoleManagementRequest $request, Role $role)
    {
        $validated = $request->validated();

        $action = $validated['action'];

        if ($action === 'add') {
            $permission = Permission::findById($validated['permission_id']);
            $role->givePermissionTo($permission);
            $message = 'Permission added successfully.';
        } elseif ($action === 'remove') {
            $permission = Permission::findById($validated['permission_id']);
            $role->revokePermissionTo($permission);
            $message = 'Permission removed successfully.';
        } else { // sync
            $permissions = Permission::whereIn('id', $validated['permissions'])->get();
            $role->syncPermissions($permissions);
            $message = 'Permissions synchronized successfully.';
        }

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.roles.permissions', $role)
            ->with('success', $message);
    }
}
