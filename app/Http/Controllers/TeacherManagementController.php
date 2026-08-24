<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTeacherManagementRequest;
use App\Models\AuditTrail;
use App\Models\Department;
use App\Models\JobPosition;
use App\Models\Staff;
use Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Teacher Management: list, inspect, edit and remove teaching staff.
 * Create is handled by Teacher Onboarding (shared service + form); this screen
 * owns the read/update/delete lifecycle and never touches linked user accounts.
 */
class TeacherManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:academics.settings.manage');
    }

    private function teachingStaffQuery()
    {
        return Staff::query()->where('staff_type', 'teaching');
    }

    public function index(Request $request)
    {
        $query = $this->teachingStaffQuery();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('employee_number', 'like', "%{$search}%")
                    ->orWhere('tsc_number', 'like', "%{$search}%")
                    ->orWhere('work_email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('employment_status', $request->get('status'));
        }

        $teachers = $query->with(['department', 'jobPosition'])->paginate(12);

        return view('teacher-management.index', compact('teachers'));
    }

    public function show($id)
    {
        $teacher = $this->teachingStaffQuery()->findOrFail($id);

        return view('teacher-management.show', compact('teacher'));
    }

    public function edit($id)
    {
        $teacher = $this->teachingStaffQuery()->findOrFail($id);

        $departments = Department::orderBy('name')->pluck('name', 'department_id')->toArray();
        $jobPositions = JobPosition::orderBy('title')->pluck('title', 'position_id')->toArray();

        return view('teacher-management.edit', compact('teacher', 'departments', 'jobPositions'));
    }

    public function update($id, UpdateTeacherManagementRequest $request)
    {
        $teacher = $this->teachingStaffQuery()->findOrFail($id);

        try {
            $input = $request->validated();

            // Nullable columns: convert empty strings to null.
            foreach (['employee_number', 'middle_name', 'tsc_number', 'department_id', 'job_position_id', 'designation', 'qualification', 'personal_email'] as $field) {
                if (isset($input[$field]) && $input[$field] === '') {
                    $input[$field] = null;
                }
            }

            // NOT NULL columns with no DB default: empty input becomes an
            // empty string (null would violate the column constraint).
            foreach (['current_address', 'city', 'country'] as $field) {
                if (empty($input[$field])) {
                    $input[$field] = '';
                }
            }
            if (empty($input['date_of_joining'] ?? null)) {
                unset($input['date_of_joining']);
            }

            $input['updated_by'] = Auth::id();

            $oldData = $teacher->toArray();
            $teacher->update($input);

            AuditTrail::log('Staff', 'UPDATE', $teacher->staff_id, $oldData, $teacher->toArray());

            Flash::success('Teacher updated successfully.');
        } catch (\Exception $e) {
            Flash::error('Error updating teacher: '.$e->getMessage());

            return redirect()->back()->withInput();
        }

        return redirect(route('teacher-management.show', $teacher->staff_id));
    }

    public function destroy($id)
    {
        $teacher = $this->teachingStaffQuery()->findOrFail($id);

        try {
            $oldData = $teacher->toArray();
            $teacher->delete();

            AuditTrail::log('Staff', 'DELETE', $id, $oldData, null);

            Flash::success('Teacher deleted successfully.');
        } catch (\Exception $e) {
            Flash::error('Error deleting teacher: '.$e->getMessage());
        }

        return redirect(route('teacher-management.index'));
    }
}
