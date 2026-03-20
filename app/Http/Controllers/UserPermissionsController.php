<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexUserPermissionsRequest;
use App\Http\Resources\UserPermissionsResource;

class UserPermissionsController extends Controller
{
    /**
     * Get the authenticated user's permissions.
     *
     * Returns a list of permission names that the authenticated user has.
     * This is a read-only endpoint for UI clients to determine what features
     * to show to the user.
     */
    public function index(IndexUserPermissionsRequest $request)
    {
        $permissions = $request->user()
            ->getAllPermissions()
            ->pluck('name')
            ->values()
            ->toArray();

        return new UserPermissionsResource([
            'permissions' => $permissions,
        ]);
    }
}
