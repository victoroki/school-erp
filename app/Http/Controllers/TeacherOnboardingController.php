<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeacherOnboardingRequest;
use App\Models\Department;
use App\Models\JobPosition;
use App\Models\Role;
use App\Services\TeacherOnboardingService;
use Flash;

/**
 * Combined teacher onboarding: creates the Staff record, a login User and
 * the Teacher role assignment in one form (replacing three separate flows).
 */
class TeacherOnboardingController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:academics.settings.manage');
    }

    public function create()
    {
        $departments = Department::orderBy('name')->pluck('name', 'department_id')->toArray();
        $jobPositions = JobPosition::orderBy('title')->pluck('title', 'position_id')->toArray();

        $teacherRole = Role::where('role_name', 'Teacher')->first();

        return view('teacher-onboarding.create', compact('departments', 'jobPositions', 'teacherRole'));
    }

    public function store(TeacherOnboardingRequest $request, TeacherOnboardingService $service)
    {
        try {
            $result = $service->onboard($request->validated());
        } catch (\Exception $e) {
            report($e);
            Flash::error('Error onboarding teacher: '.$e->getMessage());

            return redirect()->back()->withInput();
        }

        // All Teachers — the natural next stop after onboarding someone.
        Flash::success('Teacher '.$result['full_name'].' onboarded successfully. An account-setup '
            .'email has been sent to '.$result['user']->email.' — the teacher sets their own '
            .'password through the secure, expiring link inside it.');

        return redirect(route('teacher-management.index'));
    }
}
