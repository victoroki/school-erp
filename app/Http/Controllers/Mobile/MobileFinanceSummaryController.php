<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Services\AdminMobileHomeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * PHASE 3 — GET /api/mobile/finance/summary
 *
 * Today's money snapshot for the Accountant/Bursar home (also usable by
 * Admin). Authoritative queries only — same sources the web "Collections"
 * report uses (FeePayment filtered by payment_date, never created_at).
 *
 * Access: `fees.view` permission (Accountant, Admin, Super Admin, Owner all
 * hold it; Teacher/Parent/Student do not).
 */
class MobileFinanceSummaryController extends Controller
{
    public function __invoke(Request $request, AdminMobileHomeService $home): JsonResponse
    {
        $user = $request->user();
        if (! $user->hasPermission('fees.view')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $request->validate([
            'date' => 'nullable|date',
        ]);

        $date = $request->query('date', now()->toDateString());

        return response()->json([
            'date'    => $date,
            'finance' => $home->financeToday($date),
            'recent_payments' => $home->recentPayments(10),
        ]);
    }
}
