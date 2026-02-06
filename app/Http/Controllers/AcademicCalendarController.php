<?php

namespace App\Http\Controllers;

use App\Models\AcademicEvent;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Flash;

class AcademicCalendarController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $selectedAcademicYearId = $request->get('academic_year_id');
        
        if (!$selectedAcademicYearId && $academicYears->count() > 0) {
            $current = $academicYears->firstWhere('is_current', true);
            $selectedAcademicYearId = $current ? $current->academic_year_id : $academicYears->first()->academic_year_id;
        }

        $events = AcademicEvent::where('academic_year_id', $selectedAcademicYearId)
            ->orderBy('start_date')
            ->get();

        return view('academic_calendar.index', compact('events', 'academicYears', 'selectedAcademicYearId'));
    }

    public function create()
    {
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->pluck('name', 'academic_year_id');
        return view('academic_calendar.create', compact('academicYears'));
    }

    public function store(Request $request)
    {
        $request->validate(AcademicEvent::$rules);
        AcademicEvent::create($request->all());

        Flash::success('Academic Event saved successfully.');
        return redirect(route('academic-calendar.index'));
    }

    public function edit($id)
    {
        $event = AcademicEvent::findOrFail($id);
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->pluck('name', 'academic_year_id');
        return view('academic_calendar.edit', compact('event', 'academicYears'));
    }

    public function update(Request $request, $id)
    {
        $event = AcademicEvent::findOrFail($id);
        $request->validate(AcademicEvent::$rules);
        $event->update($request->all());

        Flash::success('Academic Event updated successfully.');
        return redirect(route('academic-calendar.index'));
    }

    public function destroy($id)
    {
        $event = AcademicEvent::findOrFail($id);
        $event->delete();

        Flash::success('Academic Event deleted successfully.');
        return redirect(route('academic-calendar.index'));
    }
}
