<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Flash;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\AppBaseController;

class UserController extends AppBaseController
{
    public function __construct()
    {
        $this->middleware('can:users.view')->only(['index', 'show']);
        $this->middleware('can:users.manage')->only(['create', 'store', 'edit', 'update', 'destroy', 'resetPassword']);
    }
    /**
     * Display a listing of the User.
     */
    public function index(Request $request)
    {
        $query = User::with('roles');

        $query->where('is_hidden', false);

        $users = $query->paginate(10);

        return view('users.index')
            ->with('users', $users);
    }

    /**
     * Show the form for creating a new User.
     */
    public function create()
    {
        $roles = $this->assignableRoles();
        return view('users.create')->with('roles', $roles);
    }

    /**
     * Store a newly created User in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'array'
        ]);

        $input = $request->except(['password', 'is_protected', 'is_hidden']);
        $input['password'] = Hash::make($request->input('password'));

        $user = User::create($input);

        $this->syncRolesForRequest($user, $request->input('roles', []));

        AuditTrail::log('User', 'CREATE', $user->id, null, ['name' => $user->name, 'email' => $user->email, 'roles' => $request->input('roles', [])]);

        Flash::success('User saved successfully.');

        return redirect(route('users.index'));
    }

    /**
     * Display the specified User.
     */
    public function show($id)
    {
        $user = User::with('roles')->find($id);

        if (empty($user)) {
            Flash::error('User not found');
            return redirect(route('users.index'));
        }

        $this->authorize('view', $user);

        return view('users.show')->with('user', $user);
    }

    /**
     * Show the form for editing the specified User.
     */
    public function edit($id)
    {
        $user = User::find($id);

        if (empty($user)) {
            Flash::error('User not found');
            return redirect(route('users.index'));
        }

        $roles = $this->assignableRoles();

        return view('users.edit')->with('user', $user)->with('roles', $roles);
    }

    /**
     * Update the specified User in storage.
     */
    public function update($id, Request $request)
    {
        $user = User::find($id);

        if (empty($user)) {
            Flash::error('User not found');
            return redirect(route('users.index'));
        }

        $this->authorize('update', $user);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'array'
        ]);

        $input = $request->except(['password', 'is_protected', 'is_hidden']);
        if ($request->filled('password')) {
            $input['password'] = Hash::make($request->input('password'));
        }

        $user->update($input);

        $this->syncRolesForRequest($user, $request->input('roles', []));

        AuditTrail::log('User', 'UPDATE', $user->id, null, ['name' => $user->name, 'email' => $user->email, 'roles' => $request->input('roles', [])]);

        Flash::success('User updated successfully.');

        return redirect(route('users.index'));
    }

    /**
     * Reset a user's password without needing the current one.
     * Returns JSON for the AJAX-driven modal; falls back to a redirect for plain form posts.
     */
    public function resetPassword($id, Request $request)
    {
        $user = User::find($id);

        if (empty($user)) {
            if ($request->wantsJson()) {
                return response()->json(['errors' => ['user' => ['User not found.']]], 404);
            }
            Flash::error('User not found');
            return redirect(route('users.index'));
        }

        $this->authorize('resetPassword', $user);

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json(['errors' => $validator->errors()->toArray()], 422);
            }
            return redirect(route('users.index'))
                ->withErrors($validator)
                ->withInput();
        }

        $user->update(['password' => Hash::make($request->input('password'))]);

        AuditTrail::log('User', 'PASSWORD RESET', $user->id, null, ['email' => $user->email]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Password reset for ' . $user->name . ' successfully.']);
        }

        Flash::success('Password reset for ' . $user->name . ' successfully.');
        return redirect(route('users.index'));
    }

    /**
     * Remove the specified User from storage.
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if (empty($user)) {
            Flash::error('User not found');
            return redirect(route('users.index'));
        }

        $this->authorize('delete', $user);

        $oldData = $user->toArray();
        $user->delete();

        AuditTrail::log('User', 'DELETE', $id, $oldData, null);

        Flash::success('User deleted successfully.');

        return redirect(route('users.index'));
    }

    /**
     * Roles the acting user may assign in the UI. Protected and hidden roles
     * are only offered to users who hold a protected role themselves, and the
     * platform Owner role is reserved for the SaaS provider alone.
     */
    protected function assignableRoles(): \Illuminate\Database\Eloquent\Collection
    {
        $query = Role::orderBy('role_name');

        $actor = auth()->user();

        if (! $actor || ! $actor->canBypassProtection()) {
            $query->where('is_protected', false)->where('is_hidden', false);
        } elseif (! $actor->isOwner()) {
            $query->where('role_name', '!=', 'Owner');
        }

        return $query->get();
    }

    /**
     * Sync a user's roles, never granting or stripping protected roles unless
     * the acting user holds a protected role themselves. The platform Owner
     * role can only ever be granted or stripped by the Owner.
     */
    protected function syncRolesForRequest(User $user, array $requestedRoleIds): void
    {
        $actor = auth()->user();
        $ownerRoleId = Role::where('role_name', 'Owner')->value('role_id');

        if (! $actor || ! $actor->canBypassProtection()) {
            $kept = $user->roles()->where('is_protected', true)->pluck('roles.role_id')->toArray();

            $granted = Role::where('is_protected', false)
                ->whereIn('role_id', $requestedRoleIds)
                ->pluck('role_id')
                ->toArray();

            $user->roles()->sync(array_values(array_unique(array_merge($kept, $granted))));

            return;
        }

        // Privileged actors may manage other protected roles, but the Owner
        // role is untouchable for them: never granted, never stripped.
        if ($ownerRoleId && ! $actor->isOwner()) {
            $requestedRoleIds = array_values(array_diff($requestedRoleIds, [$ownerRoleId]));
        }

        $user->roles()->sync($requestedRoleIds);
    }
}
