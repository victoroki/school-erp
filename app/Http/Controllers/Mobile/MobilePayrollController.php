<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\Staff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobilePayrollController extends Controller
{
    /**
     * GET /api/mobile/payroll/history
     *
     * Returns the payment history for the authenticated staff member.
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();
        $staff = Staff::where('user_id', $user->id)->first();

        if (!$staff) {
            return response()->json(['error' => 'Staff record not found.'], 404);
        }

        $history = Payroll::where('staff_id', $staff->staff_id)
            ->orderBy('payment_date', 'desc')
            ->get()
            ->map(fn($p) => [
                'month'        => $p->month,
                'year'         => $p->year,
                'net_salary'   => (float) $p->net_salary,
                'payment_date' => $p->payment_date ? $p->payment_date->toDateString() : 'TBD',
                'status'       => $p->status,
                'reference'    => $p->reference_number,
            ]);

        return response()->json(['payroll_history' => $history]);
    }

    /**
     * GET /api/mobile/payroll/latest
     *
     * Returns the most recent payroll record.
     */
    public function latest(Request $request): JsonResponse
    {
        $user = $request->user();
        $staff = Staff::where('user_id', $user->id)->first();

        if (!$staff) return response()->json(['error' => 'Staff record not found.'], 404);

        $latest = Payroll::where('staff_id', $staff->staff_id)
            ->orderBy('payment_date', 'desc')
            ->first();

        if (!$latest) {
            return response()->json(['message' => 'No payroll records found.'], 404);
        }

        return response()->json([
            'month'        => $latest->month,
            'year'         => $latest->year,
            'net_salary'   => (float) $latest->net_salary,
            'payment_date' => $latest->payment_date ? $latest->payment_date->toDateString() : 'TBD',
            'status'       => $latest->status,
        ]);
    }
}