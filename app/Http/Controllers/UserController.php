<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Flash;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\AppBaseController;

class UserController extends AppBaseController
{
    public function __construct()
    {
        $this->middleware('can:users.index')->only('index');
        $this->middleware('can:users.create')->only(['create', 'store']);
        $this->middleware('can:users.edit')->only(['edit', 'update']);
        $this->middleware('can:users.delete')->only('destroy');
    }
    /**
     * Display a listing of the User.
     */
    public function index(Request $request)
    {
        $users = User::with('roles')->paginate(10);

        return view('users.index')
            ->with('users', $users);
    }

    /**
     * Show the form for creating a new User.
     */
    public function create()
    {
        $roles = Role::all();
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

        $input = $request->all();
        $input['password'] = Hash::make($input['password']);

        $user = User::create($input);

        if ($request->has('roles')) {
            $user->roles()->sync($request->input('roles'));
        }

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

        $roles = Role::all();

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

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'array'
        ]);

        $input = $request->except('password');
        if ($request->filled('password')) {
            $input['password'] = Hash::make($request->input('password'));
        }

        $user->update($input);

        if ($request->has('roles')) {
            $user->roles()->sync($request->input('roles'));
        } else {
            $user->roles()->detach();
        }

        Flash::success('User updated successfully.');

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

        $user->delete();

        Flash::success('User deleted successfully.');

        return redirect(route('users.index'));
    }
}
