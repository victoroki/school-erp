<?php

namespace App\Http\Controllers;

use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\Staff;
use App\Models\StaffLeaveBalance;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laracasts\Flash\Flash;

class LeaveApplicationController extends Controller
{
    public function index(Request $request)
    {
        $query = LeaveApplication::with(['staff', 'leaveType', 'reliefStaff']);

        // Filters
        if ($request->filled('status')) {
            $query->where('application_status', $request->status);
        }

        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }

        $applications = $query->latest()->paginate(20);
        $leaveTypes = LeaveType::where('status', 'active')->get();
        $staff = Staff::where('employment_status', 'active')->get();

        return view('hr.leave.index', compact('applications', 'leaveTypes', 'staff'));
    }

    public function create()
    {
        $leaveTypes = LeaveType::where('status', 'active')->get();
        $currentYear = AcademicYear::where('is_current', true)->first();
        
        // Get current user's staff record
        $currentStaff = Staff::where('user_id', Auth::id())->first();
        
        if (!$currentStaff) {
            Flash::error('Staff record not found for current user.');
            return redirect()->back();
        }

        // Get leave balances
        $leaveBalances = StaffLeaveBalance::where('staff_id', $currentStaff->staff_id)
            ->where('academic_year_id', $currentYear->academic_year_id ?? null)
            ->with('leaveType')
            ->get();

        // Get relief staff (same department)
        $reliefStaff = Staff::where('employment_status', 'active')
            ->where('department_id', $currentStaff->department_id)
            ->where('staff_id', '!=', $currentStaff->staff_id)
            ->get();

        return view('hr.leave.create', compact('leaveTypes', 'leaveBalances', 'reliefStaff', 'currentStaff'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,leave_type_id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:10',
            'relief_staff_id' => 'nullable|exists:staff,staff_id',
            'supporting_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $currentStaff = Staff::where('user_id', Auth::id())->first();
        $leaveType = LeaveType::find($request->leave_type_id);
        $currentYear = AcademicYear::where('is_current', true)->first();

        // Calculate working days
        $workingDays = $this->calculateWorkingDays($request->start_date, $request->end_date);

        // Check leave balance
        $balance = StaffLeaveBalance::where('staff_id', $currentStaff->staff_id)
            ->where('leave_type_id', $request->leave_type_id)
            ->where('academic_year_id', $currentYear->academic_year_id ?? null)
            ->first();

        if ($balance && $balance->remaining < $workingDays) {
            Flash::error("Insufficient leave balance. You have only {$balance->remaining} days remaining.");
            return redirect()->back()->withInput();
        }

        // Check advance notice requirement
        $daysUntilLeave = now()->diffInDays($request->start_date, false);
        if ($daysUntilLeave < $leaveType->notice_days_required) {
            Flash::error("This leave type requires {$leaveType->notice_days_required} days advance notice.");
            return redirect()->back()->withInput();
        }

        // Handle file upload
        $documentPath = null;
        if ($request->hasFile('supporting_document')) {
            $documentPath = $request->file('supporting_document')->store('leave_documents', 'public');
        }

        // Create leave application
        $leave = LeaveApplication::create([
            'staff_id' => $currentStaff->staff_id,
            'leave_type_id' => $request->leave_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'working_days' => $workingDays,
            'reason' => $request->reason,
            'relief_staff_id' => $request->relief_staff_id,
            'handover_notes' => $request->handover_notes,
            'supporting_document' => $documentPath,
            'application_status' => 'pending',
            'submitted_date' => now(),
        ]);

        Flash::success('Leave application submitted successfully.');
        return redirect()->route('leave-applications.index');
    }

    public function show(LeaveApplication $leaveApplication)
    {
        $leaveApplication->load(['staff', 'leaveType', 'reliefStaff']);
        return view('hr.leave.show', compact('leaveApplication'));
    }

    public function approve($id, Request $request)
    {
        $leave = LeaveApplication::findOrFail($id);
        $user = Auth::user();
        $currentStaff = Staff::where('user_id', $user->id)->first();

        // Determine approval level
        $isHOD = $currentStaff && $leave->staff->department_id == $currentStaff->department_id 
                 && $leave->staff->department->hod_id == $currentStaff->staff_id;
        $isHR = $user->hasRole('HR Manager') || $user->hasRole('Admin');

        DB::beginTransaction();
        try {
            if ($isHOD && $leave->hod_approval_status == 'pending') {
                $leave->update([
                    'hod_approval_status' => 'approved',
                    'hod_approved_by' => $user->id,
                    'hod_approval_date' => now(),
                    'hod_comments' => $request->comments,
                ]);
                Flash::success('Leave approved by HOD.');
            } elseif ($isHR && $leave->hr_approval_status == 'pending') {
                $leave->update([
                    'hr_approval_status' => 'approved',
                    'hr_approved_by' => $user->id,
                    'hr_approval_date' => now(),
                    'hr_comments' => $request->comments,
                ]);

                // If both approvals done, finalize
                if ($leave->hod_approval_status == 'approved') {
                    $leave->update([
                        'final_status' => 'approved',
                        'application_status' => 'approved',
                    ]);

                    // Deduct from leave balance
                    $currentYear = AcademicYear::where('is_current', true)->first();
                    $balance = StaffLeaveBalance::where('staff_id', $leave->staff_id)
                        ->where('leave_type_id', $leave->leave_type_id)
                        ->where('academic_year_id', $currentYear->academic_year_id ?? null)
                        ->first();

                    if ($balance) {
                        $balance->used += $leave->working_days;
                        $balance->remaining = $balance->total_available - $balance->used;
                        $balance->save();
                    }
                }

                Flash::success('Leave approved by HR.');
            } else {
                Flash::error('You are not authorized to approve this leave.');
                DB::rollBack();
                return redirect()->back();
            }

            DB::commit();
            return redirect()->route('leave-applications.show', $leave->id);
        } catch (\Exception $e) {
            DB::rollBack();
            Flash::error('Error approving leave: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function reject($id, Request $request)
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:10',
        ]);

        $leave = LeaveApplication::findOrFail($id);
        
        $leave->update([
            'application_status' => 'rejected',
            'final_status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        Flash::success('Leave application rejected.');
        return redirect()->route('leave-applications.index');
    }

    public function destroy(LeaveApplication $leaveApplication)
    {
        if ($leaveApplication->application_status != 'draft') {
            Flash::error('Cannot delete submitted leave applications.');
            return redirect()->back();
        }

        $leaveApplication->delete();
        Flash::success('Leave application deleted successfully.');
        return redirect()->route('leave-applications.index');
    }

    private function calculateWorkingDays($startDate, $endDate)
    {
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);
        $workingDays = 0;

        while ($start->lte($end)) {
            if ($start->isWeekday()) {
                $workingDays++;
            }
            $start->addDay();
        }

        return $workingDays;
    }
}
