<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\StaffExitClearance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laracasts\Flash\Flash;

class StaffExitController extends Controller
{
    public function index()
    {
        $exitingStaff = Staff::whereIn('employment_status', ['resigned', 'terminated'])
            ->orWhereNotNull('exit_date')
            ->with(['department', 'jobPosition', 'exitClearance'])
            ->latest('exit_date')
            ->get();

        return view('hr.exit.index', compact('exitingStaff'));
    }

    public function create($staffId)
    {
        $staff = Staff::with('department', 'jobPosition')->findOrFail($staffId);
        return view('hr.exit.create', compact('staff'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,staff_id',
            'exit_type' => 'required|in:resignation,termination,retirement,contract_end',
            'exit_date' => 'required|date',
            'reason' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $staff = Staff::findOrFail($request->staff_id);

            // Update staff record
            $staff->update([
                'employment_status' => $request->exit_type == 'resignation' ? 'resigned' : 'terminated',
                'exit_date' => $request->exit_date,
                'exit_reason' => $request->reason,
            ]);

            // Create exit clearance
            $clearance = StaffExitClearance::create([
                'staff_id' => $staff->staff_id,
                'exit_type' => $request->exit_type,
                'exit_date' => $request->exit_date,
                'reason' => $request->reason,
                'notice_period_days' => $request->notice_period_days,
                'final_working_date' => $request->final_working_date,
                'clearance_status' => 'pending',
            ]);

            // Disable user account
            if ($staff->user_id) {
                User::where('id', $staff->user_id)->update(['is_active' => false]);
            }

            DB::commit();
            Flash::success('Exit process initiated successfully.');
            return redirect()->route('hr.exit.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Flash::error('Error initiating exit: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }
}
