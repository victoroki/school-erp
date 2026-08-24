<?php

namespace App\Http\Controllers;

use App\Models\StaffAttendance;
use App\Models\Staff;
use App\Models\Department;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laracasts\Flash\Flash;

class StaffAttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:hr.view')->only(['index', 'show']);
        $this->middleware('can:hr.manage')->only(['store', 'update']);
    }

    public function index(Request $request)
    {
        $date = $request->input('date', today()->format('Y-m-d'));
        $departmentId = $request->input('department_id');

        $query = StaffAttendance::with('staff.department')
            ->whereDate('date', $date);

        if ($departmentId) {
            $query->whereHas('staff', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        $attendances = $query->get();
        $departments = Department::all();

        // Get all active staff for the selected department
        $staffQuery = Staff::where('employment_status', 'active');
        if ($departmentId) {
            $staffQuery->where('department_id', $departmentId);
        }
        $allStaff = $staffQuery->get();

        // Calculate summary
        $summary = [
            'total' => $allStaff->count(),
            'present' => $attendances->where('status', 'present')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'on_leave' => $attendances->where('status', 'on_leave')->count(),
        ];

        return view('hr.attendance.index', compact('attendances', 'departments', 'date', 'departmentId', 'allStaff', 'summary'));
    }

    public function create()
    {
        $date = today()->format('Y-m-d');
        $staff = Staff::where('employment_status', 'active')->with('department')->get();
        $departments = Department::all();

        // Check if attendance already marked for today
        $existingAttendance = StaffAttendance::whereDate('date', $date)->pluck('staff_id')->toArray();

        return view('hr.attendance.create', compact('staff', 'departments', 'date', 'existingAttendance'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.staff_id' => 'required|exists:staff,staff_id',
            'attendance.*.status' => 'required|in:present,absent,late,half_day,on_leave',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->attendance as $record) {
                StaffAttendance::updateOrCreate(
                    [
                        'staff_id' => $record['staff_id'],
                        'date' => $request->date,
                    ],
                    [
                        'status' => $record['status'],
                        'time_in' => $record['time_in'] ?? null,
                        'time_out' => $record['time_out'] ?? null,
                        'notes' => $record['notes'] ?? null,
                        'marked_by' => Auth::id(),
                    ]
                );
            }

            AuditTrail::log('Staff Attendance', 'CREATE', null, null, [
                'date' => $request->date,
                'records' => $request->attendance,
            ]);

            DB::commit();
            Flash::success('Attendance marked successfully.');
            return redirect()->route('staff-attendance.index', ['date' => $request->date]);
        } catch (\Exception $e) {
            DB::rollBack();
            Flash::error('Error marking attendance: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function show(StaffAttendance $staffAttendance)
    {
        $staffAttendance->load('staff', 'markedBy');
        return view('hr.attendance.show', compact('staffAttendance'));
    }

    public function edit(StaffAttendance $staffAttendance)
    {
        return view('hr.attendance.edit', compact('staffAttendance'));
    }

    public function update(Request $request, StaffAttendance $staffAttendance)
    {
        $request->validate([
            'status' => 'required|in:present,absent,late,half_day,on_leave',
        ]);

        $oldData = $staffAttendance->toArray();
        $staffAttendance->update([
            'status' => $request->status,
            'time_in' => $request->time_in,
            'time_out' => $request->time_out,
            'notes' => $request->notes,
        ]);

        AuditTrail::log('Staff Attendance', 'UPDATE', $staffAttendance->attendance_id, $oldData, $staffAttendance->toArray());

        Flash::success('Attendance updated successfully.');
        return redirect()->route('staff-attendance.index');
    }

    public function destroy(StaffAttendance $staffAttendance)
    {
        $oldData = $staffAttendance->toArray();
        $staffAttendance->delete();
        AuditTrail::log('Staff Attendance', 'DELETE', $staffAttendance->attendance_id, $oldData, null);
        Flash::success('Attendance record deleted successfully.');
        return redirect()->route('staff-attendance.index');
    }
}
