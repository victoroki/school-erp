<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRoleRequest;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\UserRoleRepository;
use Illuminate\Http\Request;
use Flash;
use App\Models\User;
use App\Models\Role;

class UserRoleController extends AppBaseController
{
    /** @var UserRoleRepository $userRoleRepository*/
    private $userRoleRepository;

    public function __construct(UserRoleRepository $userRoleRepo)
    {
        $this->userRoleRepository = $userRoleRepo;
    }

    /**
     * Display a listing of the UserRole.
     */
    public function index(Request $request)
    {
        $userRoles = $this->userRoleRepository->paginate(10);

        return view('user_roles.index')
            ->with('userRoles', $userRoles);
    }

    /**
     * Show the form for creating a new UserRole.
     */
    public function create()
    {
        $users = User::orderBy('name')->get();
        $roles = Role::orderBy('role_name')->get();
        return view('user_roles.create', compact('users', 'roles'));
    }

    /**
     * Store a newly created UserRole in storage.
     */
    public function store(CreateUserRoleRequest $request)
    {
        $input = $request->all();

        $userRole = $this->userRoleRepository->create($input);

        Flash::success('User Role saved successfully.');

        return redirect(route('userRoles.index'));
    }

    /**
     * Display the specified UserRole.
     */
    public function show($id)
    {
        $userRole = $this->userRoleRepository->find($id);

        if (empty($userRole)) {
            Flash::error('User Role not found');

            return redirect(route('userRoles.index'));
        }

        return view('user_roles.show')->with('userRole', $userRole);
    }

    /**
     * Show the form for editing the specified UserRole.
     */
    public function edit($id)
    {
        $userRole = $this->userRoleRepository->find($id);

        if (empty($userRole)) {
            Flash::error('User Role not found');

            return redirect(route('userRoles.index'));
        }

        $users = User::orderBy('name')->get();
        $roles = Role::orderBy('role_name')->get();
        return view('user_roles.edit', compact('userRole', 'users', 'roles'));
    }

    /**
     * Update the specified UserRole in storage.
     */
    public function update($id, UpdateUserRoleRequest $request)
    {
        $userRole = $this->userRoleRepository->find($id);

        if (empty($userRole)) {
            Flash::error('User Role not found');

            return redirect(route('userRoles.index'));
        }

        $userRole = $this->userRoleRepository->update($request->all(), $id);

        Flash::success('User Role updated successfully.');

        return redirect(route('userRoles.index'));
    }

    /**
     * Remove the specified UserRole from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $userRole = $this->userRoleRepository->find($id);

        if (empty($userRole)) {
            Flash::error('User Role not found');

            return redirect(route('userRoles.index'));
        }

        $this->userRoleRepository->delete($id);

        Flash::success('User Role deleted successfully.');

        return redirect(route('userRoles.index'));
    }
}
