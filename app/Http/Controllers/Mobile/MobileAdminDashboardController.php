<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\AdminMobileHomeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * PHASE 3 — GET /api/mobile/admin/dashboard
 *
 * The Admin "school today" briefing. Every figure is computed here from
 * authoritative tables (see AdminMobileHomeService) so the app renders a
 * briefing without doing school-wide math on the device.
 *
 * Access: Owner / Super Admin / Admin roles only (same gate the web
 * dashboards use). Teacher/Parent/Student/Accountant get 403 — the
 * accountant home uses /finance/summary instead.
 *
 * Blocks that the caller lacks the permission for are returned as null with
 * an explanatory `unavailable` reason — the screen shows "unavailable",
 * never a fabricated zero.
 */
class MobileAdminDashboardController extends Controller
{
    public function __invoke(Request $request, AdminMobileHomeService $home): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasAnyRole(['Owner', 'Super Admin', 'Admin'])) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $today = now()->toDateString();
        $canHr = $user->hasPermission('hr.view');
        $canFees = $user->hasPermission('fees.view');

        return response()->json([
            'date'   => $today,
            'school' => [
                'students' => Student::where('status', 'active')->count(),
            ],
            'attendance' => $home->attendanceToday($today),
            'pending_registers' => $home->pendingRegisters($today, 10),
            'staff' => $canHr
                ? $home->staffToday($today)
                : null,
            'staff_unavailable_reason' => $canHr ? null : 'hr.view permission required',
            'finance' => $canFees
                ? $home->financeToday($today)
                : null,
            'recent_payments' => $canFees
                ? $home->recentPayments(6)
                : [],
            'tasks' => $home->taskCounts($today, $canHr),
            'events' => $home->upcomingEvents(14),
        ]);
    }
}
