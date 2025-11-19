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
        // Authorization check
        // if (Gate::denies('view-staff')) {
        //     abort(403, 'Unauthorized to view staff list');
        // }

        $staff = $this->staffRepository->paginate(10);

        return view('staff.index')
            ->with('staff', $staff);
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

        return view('staff.create', compact('users', 'departments'));
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

            // Handle photo upload
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('staff-photos', 'public');
                $input['photo_url'] = Storage::url($photoPath);
            }

            // Ensure created_by is set
            $input['created_by'] = Auth::id();

            $staff = $this->staffRepository->create($input);

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
        $users = $this->userRepository->pluck('name', 'id')->toArray();
        $departments = $this->departmentRepository->pluck('name', 'id')->toArray();

        return view('staff.edit', compact('staff', 'users', 'departments'));
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

            $staff = $this->staffRepository->update($input, $id);

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

            $this->staffRepository->delete($id);

            Flash::success('Staff deleted successfully.');

            return redirect(route('staff.index'));

        } catch (\Exception $e) {
            Flash::error('Error deleting staff: ' . $e->getMessage());
            return redirect(route('staff.index'));
        }
    }
}
