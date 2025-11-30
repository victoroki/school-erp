<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentFee;
use Illuminate\Http\Request;

class FeeManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::query()
            ->with(['studentFees', 'payments'])
            ->whereHas('studentFees'); // Only show students with fee assignments

        if ($request->has('status') && $request->status != '') {
            $status = $request->status;
            if ($status == 'unpaid') {
                $query->whereHas('studentFees', function ($q) {
                    $q->where('status', 'unpaid');
                });
            } elseif ($status == 'paid') {
                $query->whereDoesntHave('studentFees', function ($q) {
                    $q->where('status', '!=', 'paid');
                });
            } elseif ($status == 'partial') {
                $query->whereHas('studentFees', function ($q) {
                    $q->where('status', 'partially_paid');
                });
            }
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('admission_no', 'like', "%{$search}%");
            });
        }

        $students = $query->paginate(10);

        return view('fee_management.index', compact('students'));
    }

    public function show($id)
    {
        $student = Student::with([
            'studentFees.feeStructure.category',
            'payments',
            'studentClassEnrollments.classSection.schoolClass'
        ])->findOrFail($id);

        return view('fee_management.show', compact('student'));
    }

    public function print($id)
    {
        $student = Student::with([
            'studentFees.feeStructure.category',
            'payments',
            'studentClassEnrollments.classSection.schoolClass'
        ])->findOrFail($id);

        return view('fee_management.print', compact('student'));
    }
}
