<?php

namespace App\Http\Controllers;

use Flash;
use Illuminate\Http\Request;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Repositories\StaffRepository;
use Illuminate\Support\Facades\Storage;
use App\Repositories\UserRoleRepository;
use App\Http\Requests\CreateStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Repositories\DepartmentRepository;
use App\Http\Controllers\AppBaseController;
use App\Models\Department;
use App\Models\User;
use App\Models\JobPosition;
use App\Models\AuditTrail;

class StaffController extends AppBaseController
{
    /** @var StaffRepository $staffRepository*/
    private $staffRepository;

    /** @var UserRepository $userRepository*/
    private $userRepository;

    /** @var DepartmentRepository $departmentRepository*/
    private $departmentRepository;

    public function __construct(
        StaffRepository $staffRepo,
        UserRoleRepository $userRepo,
        DepartmentRepository $departmentRepo
    ) {
        $this->staffRepository = $staffRepo;
        $this->userRepository = $userRepo;
        $this->departmentRepository = $departmentRepo;

        // Apply middleware for authentication
        $this->middleware('auth');
    }

    /**
     * Display a listing of the Staff.
     */
    public function index(Request $request)
    {
        $query = $this->staffRepository->model()::query();

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                  ->orWhere('last_name', 'like', "%$search%")
                  ->orWhere('employee_number', 'like', "%$search%")
                  ->orWhere('work_email', 'like', "%$search%");
            });
        }

        // Staff Type Filter
        if ($request->filled('staff_type')) {
            $query->where('staff_type', $request->get('staff_type'));
        }

        $staff = $query->with(['department', 'jobPosition'])->paginate(12);

        return view('staff.index')->with('staff', $staff);
    }

    /**
     * Show the form for creating a new Staff.
     */
    public function create()
    {
        // Authorization check
        // if (Gate::denies('create-staff')) {
        //     abort(403, 'Unauthorized to create staff');
        // }

        // Get dropdown data
        $users = User::pluck('name', 'id')->toArray();
        $departments = Department::pluck('name', 'department_id')->toArray();
        $jobPositions = JobPosition::pluck('title', 'position_id')->toArray();

        return view('staff.create', compact('users', 'departments', 'jobPositions'));
    }

    /**
     * Store a newly created Staff in storage.
     */
    public function store(CreateStaffRequest $request)
    {
        // Authorization check
        // if (Gate::denies('create-staff')) {
        //     abort(403, 'Unauthorized to create staff');
        // }

        try {
            $input = $request->validated();

            // Convert empty strings to null for nullable fields to avoid DB issues
            foreach (['employee_number', 'job_position_id', 'designation', 'basic_salary', 'middle_name'] as $field) {
                if (isset($input[$field]) && $input[$field] === '') {
                    $input[$field] = null;
                }
            }

            // Handle photo upload
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('staff-photos', 'public');
                $input['photo_url'] = Storage::url($photoPath);
            }

            // Ensure created_by is set
            $input['created_by'] = Auth::id();

            $staff = $this->staffRepository->create($input);

            // Audit Log
            AuditTrail::log('Staff', 'CREATE', $staff->staff_id, null, $staff->toArray());

            Flash::success('Staff saved successfully.');

            return redirect(route('staff.index'));

        } catch (\Exception $e) {
            Flash::error('Error creating staff: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified Staff.
     */
    public function show($id)
    {
        // Authorization check
        // if (Gate::denies('view-staff')) {
        //     abort(403, 'Unauthorized to view staff');
        // }

        $staff = $this->staffRepository->find($id);

        if (empty($staff)) {
            Flash::error('Staff not found');
            return redirect(route('staff.index'));
        }

        return view('staff.show')->with('staff', $staff);
    }

    /**
     * Show the form for editing the specified Staff.
     */
    public function edit($id)
    {
        // Authorization check
        // if (Gate::denies('edit-staff')) {
        //     abort(403, 'Unauthorized to edit staff');
        // }

        $staff = $this->staffRepository->find($id);

        if (empty($staff)) {
            Flash::error('Staff not found');
            return redirect(route('staff.index'));
        }

        // Get dropdown data
        $users = User::pluck('name', 'id')->toArray();
        $departments = Department::pluck('name', 'department_id')->toArray();
        $jobPositions = JobPosition::pluck('title', 'position_id')->toArray();

        return view('staff.edit', compact('staff', 'users', 'departments', 'jobPositions'));
    }

    /**
     * Update the specified Staff in storage.
     */
    public function update($id, UpdateStaffRequest $request)
    {
        // Authorization check
        // if (Gate::denies('edit-staff')) {
        //     abort(403, 'Unauthorized to update staff');
        // }

        $staff = $this->staffRepository->find($id);

        if (empty($staff)) {
            Flash::error('Staff not found');
            return redirect(route('staff.index'));
        }

        try {
            $input = $request->validated();

            // Convert empty strings to null for nullable fields to avoid DB issues
            foreach (['employee_number', 'job_position_id', 'designation', 'basic_salary', 'middle_name'] as $field) {
                if (isset($input[$field]) && $input[$field] === '') {
                    $input[$field] = null;
                }
            }

            // Handle photo upload
            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($staff->photo_url) {
                    $oldPhotoPath = str_replace('/storage/', '', $staff->photo_url);
                    Storage::disk('public')->delete($oldPhotoPath);
                }

                $photoPath = $request->file('photo')->store('staff-photos', 'public');
                $input['photo_url'] = Storage::url($photoPath);
            }

            $input['updated_by'] = Auth::id();

            $oldData = $staff->toArray();
            $staff = $this->staffRepository->update($input, $id);

            // Audit Log
            AuditTrail::log('Staff', 'UPDATE', $staff->staff_id, $oldData, $staff->toArray());

            Flash::success('Staff updated successfully.');

            return redirect(route('staff.index'));

        } catch (\Exception $e) {
            Flash::error('Error updating staff: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified Staff from storage.
     */
    public function destroy($id)
    {
        // Authorization check
        // if (Gate::denies('delete-staff')) {
        //     abort(403, 'Unauthorized to delete staff');
        // }

        $staff = $this->staffRepository->find($id);

        if (empty($staff)) {
            Flash::error('Staff not found');
            return redirect(route('staff.index'));
        }

        try {
            // Delete associated photo if exists
            if ($staff->photo_url) {
                $photoPath = str_replace('/storage/', '', $staff->photo_url);
                Storage::disk('public')->delete($photoPath);
            }

            $oldData = $staff->toArray();
            $this->staffRepository->delete($id);

            // Audit Log
            AuditTrail::log('Staff', 'DELETE', $id, $oldData, null);

            Flash::success('Staff deleted successfully.');

            return redirect(route('staff.index'));

        } catch (\Exception $e) {
            Flash::error('Error deleting staff: ' . $e->getMessage());
            return redirect(route('staff.index'));
        }
    }
}
