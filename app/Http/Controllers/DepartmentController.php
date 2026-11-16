<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\DepartmentRepository;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Flash;

class DepartmentController extends AppBaseController
{
    /** @var DepartmentRepository $departmentRepository*/
    private $departmentRepository;

    public function __construct(DepartmentRepository $departmentRepo)
    {
        $this->departmentRepository = $departmentRepo;
        $this->middleware('can:hr.view')->only(['index', 'show']);
        $this->middleware('can:hr.manage')->only(['create', 'store', 'edit', 'update', 'destroy', 'updateHod']);
    }

    /**
     * Display a listing of the Department.
     */
    public function index(Request $request)
    {
        $departments = $this->departmentRepository->with(['hod'])->paginate(10);

        // Eligible HOD candidates: active staff, listed by name. Eligibility is
        // enforced again server-side in updateHod(), so a crafted post naming
        // an inactive or non-existent staff member is refused.
        $hods = \App\Models\Staff::where('employment_status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->mapWithKeys(fn ($s) => [$s->staff_id => $s->full_name]);

        return view('departments.index')
            ->with('departments', $departments)
            ->with('hods', $hods);
    }

    /**
     * Get dropdown data for forms
     */
    private function getDropdownData()
    {
        return [
            'staff' => \App\Models\Staff::get()->pluck('full_name', 'staff_id')->toArray()
        ];
    }

    /**
     * Assign or change the Head of Department from the listing page.
     *
     * Split out from the general update so the index can offer the action
     * without a round-trip through the edit form. The HOD field is set
     * directly on the department — no pivot involved — so duplicate HOD rows
     * are structurally impossible; assigning the same teacher again is simply
     * a no-op update.
     */
    public function updateHod(Request $request, $id)
    {
        $department = $this->departmentRepository->find($id);

        if (empty($department)) {
            Flash::error('Department not found');

            return redirect(route('departments.index'));
        }

        $validated = $request->validate([
            // Nullable: clearing the field removes the current HOD.
            'hod_id' => 'nullable|integer|exists:staff,staff_id',
        ]);

        if (!empty($validated['hod_id'])) {
            $staff = \App\Models\Staff::find($validated['hod_id']);

            if ($staff && $staff->employment_status !== 'active') {
                Flash::error($staff->full_name . ' is not an active staff member and cannot head ' . $department->name . '.');

                return redirect(route('departments.index'));
            }
        }

        $oldHod = $department->hod ? $department->hod->full_name : null;
        $department->hod_id = $validated['hod_id'] ?? null;
        $department->save();

        AuditTrail::log('Department', 'SET_HOD', $department->department_id,
            ['hod_id' => $oldHod], ['hod_id' => $department->hod?->full_name]);

        if ($department->hod) {
            Flash::success($department->hod->full_name . ' is now Head of ' . $department->name . '.');
        } else {
            Flash::success('HOD removed from ' . $department->name . '.');
        }

        return redirect(route('departments.index'));
    }

    /**
     * Show the form for creating a new Department.
     */
    public function create()
    {
        return view('departments.create', $this->getDropdownData());
    }

    /**
     * Store a newly created Department in storage.
     */
    public function store(CreateDepartmentRequest $request)
    {
        $input = $request->all();

        $department = $this->departmentRepository->create($input);

        AuditTrail::log('Department', 'CREATE', $department->department_id, null, $department->toArray());

        Flash::success('Department saved successfully.');

        return redirect(route('departments.index'));
    }

    /**
     * Display the specified Department.
     */
    public function show($id)
    {
        $department = $this->departmentRepository->with(['subjects', 'hod', 'staff'])->find($id);

        if (empty($department)) {
            Flash::error('Department not found');

            return redirect(route('departments.index'));
        }

        return view('departments.show')->with('department', $department);
    }

    /**
     * Show the form for editing the specified Department.
     */
    public function edit($id)
    {
        $department = $this->departmentRepository->find($id);

        if (empty($department)) {
            Flash::error('Department not found');

            return redirect(route('departments.index'));
        }

        return view('departments.edit', array_merge(
            ['department' => $department],
            $this->getDropdownData()
        ));
    }

    /**
     * Update the specified Department in storage.
     */
    public function update($id, UpdateDepartmentRequest $request)
    {
        $department = $this->departmentRepository->find($id);

        if (empty($department)) {
            Flash::error('Department not found');

            return redirect(route('departments.index'));
        }

        $oldData = $department->toArray();
        $department = $this->departmentRepository->update($request->all(), $id);

        AuditTrail::log('Department', 'UPDATE', $department->department_id, $oldData, $department->toArray());

        Flash::success('Department updated successfully.');

        return redirect(route('departments.index'));
    }

    /**
     * Remove the specified Department from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $department = $this->departmentRepository->find($id);

        if (empty($department)) {
            Flash::error('Department not found');

            return redirect(route('departments.index'));
        }

        $oldData = $department->toArray();
        $this->departmentRepository->delete($id);

        AuditTrail::log('Department', 'DELETE', $id, $oldData, null);

        Flash::success('Department deleted successfully.');

        return redirect(route('departments.index'));
    }
}
