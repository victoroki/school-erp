<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRolePermissionRequest;
use App\Http\Requests\UpdateRolePermissionRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\RolePermissionRepository;
use Illuminate\Http\Request;
use Flash;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionController extends AppBaseController
{
    /** @var RolePermissionRepository $rolePermissionRepository*/
    private $rolePermissionRepository;

    public function __construct(RolePermissionRepository $rolePermissionRepo)
    {
        $this->rolePermissionRepository = $rolePermissionRepo;
    }

    /**
     * Display a listing of the RolePermission.
     */
    public function index(Request $request)
    {
        $rolePermissions = $this->rolePermissionRepository->paginate(10);

        return view('role_permissions.index')
            ->with('rolePermissions', $rolePermissions);
    }

    /**
     * Show the form for creating a new RolePermission.
     */
    public function create()
    {
        $roles = Role::orderBy('role_name')->get();
        $permissions = Permission::orderBy('permission_name')->get();
        return view('role_permissions.create', compact('roles', 'permissions'));
    }

    /**
     * Store a newly created RolePermission in storage.
     */
    public function store(CreateRolePermissionRequest $request)
    {
        $input = $request->all();

        $rolePermission = $this->rolePermissionRepository->create($input);

        Flash::success('Role Permission saved successfully.');

        return redirect(route('rolePermissions.index'));
    }

    /**
     * Display the specified RolePermission.
     */
    public function show($id)
    {
        $rolePermission = $this->rolePermissionRepository->find($id);

        if (empty($rolePermission)) {
            Flash::error('Role Permission not found');

            return redirect(route('rolePermissions.index'));
        }

        return view('role_permissions.show')->with('rolePermission', $rolePermission);
    }

    /**
     * Show the form for editing the specified RolePermission.
     */
    public function edit($id)
    {
        $rolePermission = $this->rolePermissionRepository->find($id);

        if (empty($rolePermission)) {
            Flash::error('Role Permission not found');

            return redirect(route('rolePermissions.index'));
        }

        $roles = Role::orderBy('role_name')->get();
        $permissions = Permission::orderBy('permission_name')->get();
        return view('role_permissions.edit', compact('rolePermission', 'roles', 'permissions'));
    }

    /**
     * Update the specified RolePermission in storage.
     */
    public function update($id, UpdateRolePermissionRequest $request)
    {
        $rolePermission = $this->rolePermissionRepository->find($id);

        if (empty($rolePermission)) {
            Flash::error('Role Permission not found');

            return redirect(route('rolePermissions.index'));
        }

        $rolePermission = $this->rolePermissionRepository->update($request->all(), $id);

        Flash::success('Role Permission updated successfully.');

        return redirect(route('rolePermissions.index'));
    }

    /**
     * Remove the specified RolePermission from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $rolePermission = $this->rolePermissionRepository->find($id);

        if (empty($rolePermission)) {
            Flash::error('Role Permission not found');

            return redirect(route('rolePermissions.index'));
        }

        $this->rolePermissionRepository->delete($id);

        Flash::success('Role Permission deleted successfully.');

        return redirect(route('rolePermissions.index'));
    }
}
