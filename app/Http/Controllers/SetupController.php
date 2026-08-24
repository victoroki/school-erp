<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * One-time school onboarding.
 *
 * A fresh deployment starts with no administrator account. The school is
 * given a secret setup link (/setup/{token}) which lets them create the first
 * administrator (Super Admin role). As soon as that account exists the link
 * is consumed — visiting it again simply redirects to the login page.
 *
 * The platform Owner role is NOT granted here — it belongs exclusively to the
 * SaaS provider and is seeded per deployment (see OwnerSeeder).
 */
class SetupController extends Controller
{
    /**
     * Show the setup form if the token is valid and setup is still pending.
     */
    public function show(string $token)
    {
        if (!$this->tokenIsValid($token)) {
            abort(404);
        }

        if ($this->setupIsComplete()) {
            return redirect()->route('login');
        }

        return view('setup', ['token' => $token]);
    }

    /**
     * Create the school's first administrator account and consume the link.
     */
    public function store(string $token, Request $request)
    {
        if (!$this->tokenIsValid($token)) {
            abort(404);
        }

        if ($this->setupIsComplete()) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'name'        => ['required', 'string', 'max:255', 'unique:users,name'],
            'email'       => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'    => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name'              => $data['name'],
            'email'             => $data['email'],
            'password'          => Hash::make($data['password']),
            'user_type'         => 'admin',
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        $school = School::first();
        if ($school) {
            $school->update(['name' => $data['school_name']]);
        } else {
            School::create(['name' => $data['school_name']]);
        }

        $superAdminRole = Role::updateOrCreate([
            'role_name' => 'Super Admin',
        ], [
            'description'  => 'School Super Administrator with all permissions (except the platform Administration module)',
            'is_protected' => true,
        ]);

        $user->roles()->syncWithoutDetaching([$superAdminRole->role_id]);

        DB::table('settings')->updateOrInsert(
            ['setting_key' => 'setup_completed'],
            [
                'setting_value' => '1',
                'field_type'    => 'text',
                'is_public'     => false,
                'updated_at'    => now(),
            ]
        );

        Auth::login($user);

        return redirect()->route('home')
            ->with('success', 'Welcome aboard! Your school portal has been set up.');
    }

    /**
     * The setup link only works before the school has an administrator.
     * Checks the Super Admin role — NOT Owner, which belongs to the SaaS
     * provider and is already seeded on every deployment.
     */
    private function setupIsComplete(): bool
    {
        $markedComplete = DB::table('settings')
            ->where('setting_key', 'setup_completed')
            ->value('setting_value');

        if ($markedComplete === '1') {
            return true;
        }

        $superAdminRole = Role::where('role_name', 'Super Admin')->first();

        return $superAdminRole !== null && $superAdminRole->users()->exists();
    }

    /**
     * Constant-time comparison against the configured token.
     */
    private function tokenIsValid(string $token): bool
    {
        $expected = config('setup.token');

        return is_string($expected)
            && $expected !== ''
            && hash_equals($expected, $token);
    }
}
