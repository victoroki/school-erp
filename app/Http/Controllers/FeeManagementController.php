<?php

namespace App\Http\Controllers;

use Flash;
use App\Models\Student;
use App\Models\StudentFeeAssignment;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class FeeManagementController extends Controller
{
    protected $financeService;

    public function __construct(\App\Services\FinanceService $financeService)
    {
        $this->financeService = $financeService;
        $this->middleware('can:fees.view')->only(['index', 'show', 'print']);
        $this->middleware('can:fees.collect')->only(['collect', 'collectPayment', 'storePayment']);
    }

    public function index(Request $request)
    {
        $query = Student::query()
            ->with(['feeAssignments', 'payments', 'studentClassEnrollments.classSection.schoolClass'])
            ->whereHas('feeAssignments');

        if ($request->has('status') && $request->status != '') {
            $status = $request->status;
            if ($status == 'unpaid') {
                $query->whereHas('feeAssignments', function ($q) {
                    $q->where('status', 'active')
                      ->where(function ($subQ) {
                          $subQ->whereNull('paid_amount')
                               ->orWhere('paid_amount', 0);
                      });
                });
            } elseif ($status == 'paid') {
                $query->whereHas('feeAssignments', function ($q) {
                    $q->where('status', 'active')
                      ->whereColumn('paid_amount', '>=', 'final_amount');
                });
            } elseif ($status == 'partial') {
                $query->whereHas('feeAssignments', function ($q) {
                    $q->where('status', 'active')
                      ->whereColumn('paid_amount', '>', 0)
                      ->whereColumn('paid_amount', '<', 'final_amount');
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

    public function collect(Request $request)
    {
        $query = Student::query()
            ->with(['feeAssignments', 'studentClassEnrollments.classSection.schoolClass'])
            ->where('is_active', true);

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('admission_no', 'like', "%{$q}%")
                    ->orWhere('first_name', 'like', "%{$q}%")
                    ->orWhere('middle_name', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%");
            });
        }

        if ($request->filled('class_id')) {
            $query->whereHas('studentClassEnrollments.classSection.schoolClass', function ($sub) use ($request) {
                $sub->where('class_id', $request->class_id);
            });
        }

        $students = $query->orderBy('admission_no')->paginate(25)->withQueryString();
        $classes = \App\Models\SchoolClass::orderBy('name')->pluck('name', 'class_id');

        return view('fee_management.collect', compact('students', 'classes'));
    }

    public function show($id)
    {
        $student = Student::with([
            'feeAssignments.feeStructure.category',
            'feeAssignments.payments.collectedBy',
            'studentClassEnrollments.classSection.schoolClass'
        ])->findOrFail($id);

        return view('fee_management.show', compact('student'));
    }

    public function collectPayment($id)
    {
        $student = Student::with(['feeAssignments' => function($q) {
            $q->where('status', 'active')
              ->whereRaw('COALESCE(paid_amount, 0) < final_amount');
        }, 'feeAssignments.feeStructure.category'])->findOrFail($id);

        // Calculate total balance across all fee assignments
        $totalBalance = $student->feeAssignments->sum('balance');

        return view('fee_management.collect_payment', compact('student', 'totalBalance'));
    }

    public function storePayment(Request $request, $id)
    {
        try {
            // Handle "total" payment - distribute across all unpaid fee assignments
            if ((string)$request->student_fee_assignment_id === 'total') {
                $student = Student::with(['feeAssignments' => function($q) {
                    $q->where('status', 'active')
                      ->whereRaw('COALESCE(paid_amount, 0) < final_amount');
                }])->findOrFail($id);

                $remainingAmount = $request->amount;

                foreach ($student->feeAssignments as $feeAssignment) {
                    if ($remainingAmount <= 0) break;

                    $balance = $feeAssignment->balance;

                    if ($balance > 0) {
                        $paymentAmount = min($remainingAmount, $balance);

                        $this->financeService->recordPayment([
                            'student_fee_assignment_id' => $feeAssignment->id,
                            'amount' => $paymentAmount,
                            'payment_date' => $request->payment_date,
                            'payment_method' => $request->payment_method,
                            'transaction_id' => $request->transaction_id,
                            'remarks' => $request->remarks . ' (Part of total payment)',
                        ]);

                        $remainingAmount -= $paymentAmount;
                    }
                }

                AuditTrail::log('Fees', 'RECORD PAYMENT', $id, null, [
                    'student_id' => $id,
                    'amount' => $request->amount,
                    'payment_method' => $request->payment_method,
                    'type' => 'total',
                ]);

                Flash::success('Total payment recorded successfully across all fees.');
                \Log::info('Total payment recorded successfully');
            } else {
                // Single fee payment
                $this->financeService->recordPayment($request->all());
                AuditTrail::log('Fees', 'RECORD PAYMENT', $id, null, [
                    'student_id' => $id,
                    'student_fee_assignment_id' => $request->student_fee_assignment_id,
                    'amount' => $request->amount,
                    'payment_method' => $request->payment_method,
                ]);
                Flash::success('Payment recorded successfully.');
            }
        } catch (\Exception $e) {
            \Log::error('Payment recording failed: ' . $e->getMessage());
            Flash::error('Error recording payment: ' . $e->getMessage());
        }

        return redirect()->route('fee-management.show', $id);
    }

    public function print($id)
    {
        $student = Student::with([
            'feeAssignments.feeStructure.category',
            'feeAssignments.payments',
            'studentClassEnrollments.classSection.schoolClass'
        ])->findOrFail($id);

        return view('fee_management.print', compact('student'));
    }

    public function exportPdf(Request $request)
    {
        $query = Student::query()
            ->with(['feeAssignments', 'payments', 'studentClassEnrollments.classSection.schoolClass'])
            ->whereHas('feeAssignments');

        if ($request->has('status') && $request->status != '') {
            $status = $request->status;
            if ($status == 'unpaid') {
                $query->whereHas('feeAssignments', function ($q) {
                    $q->where('status', 'active')
                      ->where(function ($subQ) {
                          $subQ->whereNull('paid_amount')
                               ->orWhere('paid_amount', 0);
                      });
                });
            } elseif ($status == 'paid') {
                $query->whereHas('feeAssignments', function ($q) {
                    $q->where('status', 'active')
                      ->whereColumn('paid_amount', '>=', 'final_amount');
                });
            } elseif ($status == 'partial') {
                $query->whereHas('feeAssignments', function ($q) {
                    $q->where('status', 'active')
                      ->whereColumn('paid_amount', '>', 0)
                      ->whereColumn('paid_amount', '<', 'final_amount');
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

        $students = $query->get();
        $metrics = $this->financeService->getMetrics();

        $pdf = Pdf::loadView('fee_management.export_pdf', compact('students', 'metrics'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('fee-report-' . date('Y-m-d') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $query = Student::query()
            ->with(['feeAssignments', 'payments', 'studentClassEnrollments.classSection.schoolClass'])
            ->whereHas('feeAssignments');

        if ($request->has('status') && $request->status != '') {
            $status = $request->status;
            if ($status == 'unpaid') {
                $query->whereHas('feeAssignments', function ($q) {
                    $q->where('status', 'active')
                      ->where(function ($subQ) {
                          $subQ->whereNull('paid_amount')
                               ->orWhere('paid_amount', 0);
                      });
                });
            } elseif ($status == 'paid') {
                $query->whereHas('feeAssignments', function ($q) {
                    $q->where('status', 'active')
                      ->whereColumn('paid_amount', '>=', 'final_amount');
                });
            } elseif ($status == 'partial') {
                $query->whereHas('feeAssignments', function ($q) {
                    $q->where('status', 'active')
                      ->whereColumn('paid_amount', '>', 0)
                      ->whereColumn('paid_amount', '<', 'final_amount');
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

        $students = $query->get();

        $filename = 'fee-report-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($students) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Admission No', 'Student Name', 'Class', 'Total Fee', 'Paid', 'Balance', 'Status']);

            foreach ($students as $student) {
                $classInfo = '';
                foreach ($student->studentClassEnrollments as $enrollment) {
                    $classInfo .= ($enrollment->classSection->schoolClass->name ?? '') . ' - ' . ($enrollment->classSection->section->name ?? '') . '; ';
                }

                fputcsv($file, [
                    $student->admission_no,
                    $student->full_name,
                    rtrim($classInfo, '; '),
                    number_format($student->total_fee, 2),
                    number_format($student->paid_fee, 2),
                    number_format($student->balance_fee, 2),
                    $student->payment_status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
