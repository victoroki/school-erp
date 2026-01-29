<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentFee;
use Illuminate\Http\Request;

class FeeManagementController extends Controller
{
    protected $financeService;

    public function __construct(\App\Services\FinanceService $financeService)
    {
        $this->financeService = $financeService;
    }

    public function index(Request $request)
    {
        $query = Student::query()
            ->with(['studentFees', 'payments', 'studentClassEnrollments.classSection.schoolClass'])
            ->whereHas('studentFees');

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

        if ($request->has('class_id') && $request->class_id != '') {
            $query->whereHas('studentClassEnrollments.classSection.schoolClass', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
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
        $metrics = $this->financeService->getMetrics();
        $classes = \App\Models\SchoolClass::pluck('name', 'class_id');

        return view('fee_management.index', compact('students', 'metrics', 'classes'));
    }

    public function show($id)
    {
        $student = Student::with([
            'studentFees.feeStructure.category',
            'payments.collectedBy',
            'studentClassEnrollments.classSection.schoolClass'
        ])->findOrFail($id);

        return view('fee_management.show', compact('student'));
    }

    public function collectPayment($id)
    {
        $student = Student::with(['studentFees' => function($q) {
            $q->where('status', '!=', 'paid');
        }, 'studentFees.feeStructure.category'])->findOrFail($id);
        
        return view('fee_management.collect_payment', compact('student'));
    }

    public function storePayment(Request $request, $id)
    {
        $request->validate([
            'student_fee_id' => 'required|exists:student_fees,student_fee_id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        try {
            $this->financeService->recordPayment($request->all());
            Flash::success('Payment recorded successfully.');
        } catch (\Exception $e) {
            Flash::error('Error recording payment: ' . $e->getMessage());
        }

        return redirect()->route('fee-management.show', $id);
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
