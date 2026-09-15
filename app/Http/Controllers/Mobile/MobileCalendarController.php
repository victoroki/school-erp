<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\AcademicEvent;
use App\Models\AcademicYear;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileCalendarController extends Controller
{
    /**
     * GET /api/mobile/calendar
     *
     * Returns the school academic events for the current year.
     */
    public function index(Request $request): JsonResponse
    {
        $year = AcademicYear::where('is_current', true)->first();

        if (!$year) {
            return response()->json(['error' => 'No current academic year set.'], 404);
        }

        $events = AcademicEvent::where('academic_year_id', $year->academic_year_id)
            ->orderBy('start_date', 'asc')
            ->get()
            ->map(fn($e) => [
                'id'          => $e->getKey(),
                'title'       => $e->title,
                'description' => $e->description,
                'date'        => $e->start_date->toDateString(),
                'type'        => $e->event_type, // e.g., Holiday, Exam, Event
                'is_all_day'  => true,
            ]);

        return response()->json([
            'academic_year' => $year->name,
            'events'        => $events,
        ]);
    }
}