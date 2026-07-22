<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\StaffOnboardingChecklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laracasts\Flash\Flash;

class StaffOnboardingController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:hr.view')->only(['index', 'show']);
        $this->middleware('can:hr.manage')->only(['update']);
    }

    public function index()
    {
        $onboardingStaff = Staff::whereHas('onboardingChecklist', function($q) {
            $q->where('is_completed', false);
        })->with(['onboardingChecklist', 'department', 'jobPosition'])->get();

        return view('hr.onboarding.index', compact('onboardingStaff'));
    }

    public function show($id)
    {
        $staff = Staff::with(['onboardingChecklist', 'department', 'jobPosition'])->findOrFail($id);
        
        // If no checklist exists, create default one
        if ($staff->onboardingChecklist->isEmpty()) {
            $this->createDefaultChecklist($staff);
            $staff->load('onboardingChecklist');
        }

        $completionPercentage = $this->calculateCompletion($staff);

        return view('hr.onboarding.show', compact('staff', 'completionPercentage'));
    }

    public function completeItem($staffId, $itemId)
    {
        $item = StaffOnboardingChecklist::where('staff_id', $staffId)
            ->where('id', $itemId)
            ->firstOrFail();

        $item->update([
            'is_completed' => true,
            'completed_date' => now(),
            'completed_by' => auth()->id(),
        ]);

        Flash::success('Checklist item marked as completed.');
        return redirect()->back();
    }

    private function createDefaultChecklist($staff)
    {
        $defaultItems = [
            'Complete employment contract',
            'Submit required documents (ID, certificates, etc.)',
            'Create user account',
            'Issue ID card',
            'Setup email account',
            'Assign to department',
            'Workspace setup',
            'IT equipment allocation',
            'Introduction to team',
            'HR orientation session',
            'Safety & security briefing',
            'Add to payroll system',
        ];

        foreach ($defaultItems as $item) {
            StaffOnboardingChecklist::create([
                'staff_id' => $staff->staff_id,
                'checklist_item' => $item,
                'is_completed' => false,
            ]);
        }
    }

    private function calculateCompletion($staff)
    {
        $total = $staff->onboardingChecklist->count();
        if ($total == 0) return 0;
        
        $completed = $staff->onboardingChecklist->where('is_completed', true)->count();
        return round(($completed / $total) * 100);
    }
}
