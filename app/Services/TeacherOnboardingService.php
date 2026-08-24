<?php

namespace App\Services;

use App\Models\AuditTrail;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Single source of truth for the combined teacher onboarding flow: creates the
 * Staff record, a login User and the Teacher role assignment atomically, then
 * writes audit trails. Shared by every controller that onboards teachers.
 */
class TeacherOnboardingService
{
    /**
     * @param  array  $input  validated TeacherOnboardingRequest payload
     * @return array{staff: Staff, user: User, full_name: string}
     */
    public function onboard(array $input): array
    {
        $fullName = trim(implode(' ', array_filter([
            $input['first_name'],
            $input['middle_name'] ?? '',
            $input['last_name'],
        ])));

        [$staff, $user] = DB::transaction(function () use ($input, $fullName) {
            $user = User::create([
                'name' => $fullName,
                'email' => $input['login_email'],
                'password' => Hash::make($input['password']),
                'user_type' => 'staff',
                'is_active' => true,
            ]);

            // Teacher onboarding always assigns the Teacher role; the role is
            // never taken from client input to prevent privilege escalation.
            $teacherRole = Role::where('role_name', 'Teacher')->first();
            if ($teacherRole) {
                $user->roles()->sync([$teacherRole->role_id]);
            }

            foreach (['employee_number', 'middle_name', 'tsc_number', 'department_id', 'job_position_id', 'designation', 'qualification', 'date_of_joining', 'personal_email', 'current_address', 'city', 'country'] as $field) {
                if (isset($input[$field]) && $input[$field] === '') {
                    $input[$field] = null;
                }
            }

            // Columns with no DB default must always be provided.
            $input['date_of_joining'] ??= now()->toDateString();
            $input['current_address'] ??= '';
            $input['city'] ??= '';
            $input['country'] ??= '';

            $staff = Staff::create([
                'user_id' => $user->id,
                'employee_number' => $input['employee_number'] ?? null,
                'first_name' => $input['first_name'],
                'middle_name' => $input['middle_name'] ?? null,
                'last_name' => $input['last_name'],
                'date_of_birth' => $input['date_of_birth'],
                'gender' => $input['gender'],
                'phone_primary' => $input['phone_primary'],
                'work_email' => $input['work_email'],
                'personal_email' => $input['personal_email'] ?? null,
                'current_address' => $input['current_address'],
                'city' => $input['city'],
                'country' => $input['country'],
                'department_id' => $input['department_id'] ?? null,
                'job_position_id' => $input['job_position_id'] ?? null,
                'designation' => $input['designation'] ?? null,
                'qualification' => $input['qualification'] ?? null,
                'date_of_joining' => $input['date_of_joining'],
                'tsc_number' => $input['tsc_number'] ?? null,
                'staff_type' => 'teaching',
                'employment_type' => $input['employment_type'],
                'employment_status' => $input['employment_status'],
                'created_by' => Auth::id(),
            ]);

            return [$staff, $user];
        });

        AuditTrail::log('Staff', 'ONBOARD', $staff->staff_id, null, $staff->toArray());
        AuditTrail::log('User', 'ONBOARD', $user->id, null, ['name' => $user->name, 'email' => $user->email, 'staff_id' => $staff->staff_id]);

        return ['staff' => $staff, 'user' => $user, 'full_name' => $fullName];
    }
}
