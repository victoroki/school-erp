<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\PermissionRepository;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Flash;

class PermissionController extends AppBaseController
{
    /** @var PermissionRepository $permissionRepository*/
    private $permissionRepository;

    public function __construct(PermissionRepository $permissionRepo)
    {
        $this->permissionRepository = $permissionRepo;
        $this->middleware('can:users.view')->only(['index', 'show']);
        $this->middleware('can:users.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Display a listing of the Permission.
     */
    public function index(Request $request)
    {
        $allPermissions = $this->permissionRepository->all();

        // Group permissions by their prefix (e.g., "users" from "users.create")
        $permissions = $allPermissions->groupBy(function($permission) {
            return explode('.', $permission->permission_name)[0];
        });

        return view('permissions.index')
            ->with('permissions', $permissions);
    }

    /**
     * Show the form for creating a new Permission.
     */
    public function create()
    {
        return view('permissions.create');
    }

    /**
     * Store a newly created Permission in storage.
     */
    public function store(CreatePermissionRequest $request)
    {
        $input = $request->all();

        $permission = $this->permissionRepository->create($input);

        AuditTrail::log('Permission', 'CREATE', $permission->permission_id, null, $permission->toArray());

        Flash::success('Permission saved successfully.');

        return redirect(route('permissions.index'));
    }

    /**
     * Display the specified Permission.
     */
    public function show($id)
    {
        $permission = $this->permissionRepository->find($id);

        if (empty($permission)) {
            Flash::error('Permission not found');

            return redirect(route('permissions.index'));
        }

        return view('permissions.show')->with('permission', $permission);
    }

    /**
     * Show the form for editing the specified Permission.
     */
    public function edit($id)
    {
        $permission = $this->permissionRepository->find($id);

        if (empty($permission)) {
            Flash::error('Permission not found');

            return redirect(route('permissions.index'));
        }

        return view('permissions.edit')->with('permission', $permission);
    }

    /**
     * Update the specified Permission in storage.
     */
    public function update($id, UpdatePermissionRequest $request)
    {
        $permission = $this->permissionRepository->find($id);

        if (empty($permission)) {
            Flash::error('Permission not found');

            return redirect(route('permissions.index'));
        }

        $oldData = $permission->toArray();
        $permission = $this->permissionRepository->update($request->all(), $id);

        AuditTrail::log('Permission', 'UPDATE', $permission->permission_id, $oldData, $permission->toArray());

        Flash::success('Permission updated successfully.');

        return redirect(route('permissions.index'));
    }

    /**
     * Remove the specified Permission from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $permission = $this->permissionRepository->find($id);

        if (empty($permission)) {
            Flash::error('Permission not found');

            return redirect(route('permissions.index'));
        }

        $oldData = $permission->toArray();
        $this->permissionRepository->delete($id);

        AuditTrail::log('Permission', 'DELETE', $id, $oldData, null);

        Flash::success('Permission deleted successfully.');

        return redirect(route('permissions.index'));
    }
}
