<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\AcademicEvent;
use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileCalendarController extends Controller
{
    /**
     * GET /api/mobile/calendar
     *
     * PHASE 6 — the school's academic events for the current year plus the
     * current year's term dates. Read-only; the device renders events and
     * terms serverside-sorted (events ascending; terms by display_order).
     */
    public function index(Request $request): JsonResponse
    {
        $year = AcademicYear::where('is_current', true)->first();

        if (!$year) {
            return response()->json(['message' => 'No current academic year set.'], 404);
        }

        $events = AcademicEvent::where('academic_year_id', $year->academic_year_id)
            ->orderBy('start_date', 'asc')
            ->get()
            ->map(fn($e) => [
                'id'          => $e->getKey(),
                'title'       => $e->title,
                'description' => $e->description,
                'date'        => $e->start_date?->toDateString(),
                'type'        => $e->event_type, // e.g., Holiday, Exam, Event
                'is_all_day'  => true,
            ]);

        $terms = Term::where('academic_year_id', $year->academic_year_id)
            ->orderBy('display_order')
            ->orderBy('start_date')
            ->get()
            ->map(fn($t) => [
                'id'         => $t->getKey(),
                'name'       => $t->name,
                'start_date' => $t->start_date?->toDateString(),
                'end_date'   => $t->end_date?->toDateString(),
                'status'     => $t->status,
            ]);

        return response()->json([
            'academic_year' => $year->name,
            'events'        => $events,
            'terms'         => $terms,
        ]);
    }
}