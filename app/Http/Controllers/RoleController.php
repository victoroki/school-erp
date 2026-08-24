<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\RoleRepository;
use App\Models\Permission;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Flash;

class RoleController extends AppBaseController
{
    /** @var RoleRepository $roleRepository*/
    private $roleRepository;

    public function __construct(RoleRepository $roleRepo)
    {
        $this->roleRepository = $roleRepo;
        $this->middleware('can:users.view')->only(['index', 'show']);
        $this->middleware('can:users.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Display a listing of the Role.
     */
    public function index(Request $request)
    {
        $query = \App\Models\Role::orderBy('role_name');

        if (! auth()->user() || ! auth()->user()->canBypassProtection()) {
            $query->where('is_hidden', false);
        }

        $roles = $query->paginate(10);

        return view('roles.index')
            ->with('roles', $roles);
    }

    /**
     * Show the form for creating a new Role.
     */
    public function create()
    {
        $permissions = Permission::all();
        return view('roles.create')->with('permissions', $permissions);
    }

    /**
     * Store a newly created Role in storage.
     */
    public function store(CreateRoleRequest $request)
    {
        $input = $request->all();

        $role = $this->roleRepository->create($input);

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->input('permissions'));
        }

        AuditTrail::log('Role', 'CREATE', $role->role_id, null, $role->toArray());

        Flash::success('Role saved successfully.');

        return redirect(route('roles.index'));
    }

    /**
     * Display the specified Role.
     */
    public function show($id)
    {
        $role = $this->roleRepository->find($id);

        if (empty($role)) {
            Flash::error('Role not found');

            return redirect(route('roles.index'));
        }

        return view('roles.show')->with('role', $role);
    }

    /**
     * Show the form for editing the specified Role.
     */
    public function edit($id)
    {
        $role = $this->roleRepository->find($id);

        if (empty($role)) {
            Flash::error('Role not found');

            return redirect(route('roles.index'));
        }

        $permissions = Permission::all();

        return view('roles.edit')->with('role', $role)->with('permissions', $permissions);
    }

    /**
     * Update the specified Role in storage.
     */
    public function update($id, UpdateRoleRequest $request)
    {
        $role = $this->roleRepository->find($id);

        if (empty($role)) {
            Flash::error('Role not found');

            return redirect(route('roles.index'));
        }

        $this->authorize('update', $role);

        $oldData = $role->toArray();
        $role = $this->roleRepository->update($request->all(), $id);

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->input('permissions'));
        } else {
            $role->permissions()->sync([]); // remove all if none selected
        }

        AuditTrail::log('Role', 'UPDATE', $role->role_id, $oldData, $role->toArray());

        Flash::success('Role updated successfully.');

        return redirect(route('roles.index'));
    }

    /**
     * Remove the specified Role from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $role = $this->roleRepository->find($id);

        if (empty($role)) {
            Flash::error('Role not found');

            return redirect(route('roles.index'));
        }

        $this->authorize('delete', $role);

        $oldData = $role->toArray();
        $this->roleRepository->delete($id);

        AuditTrail::log('Role', 'DELETE', $id, $oldData, null);

        Flash::success('Role deleted successfully.');

        return redirect(route('roles.index'));
    }
}
