<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\Student;
use App\Models\StudentTransportAssignment;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Flash;

class StudentTransportAssignmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:transport.view')->only(['index', 'show']);
        $this->middleware('can:transport.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index(Request $request)
    {
        $query = StudentTransportAssignment::with(['student', 'route', 'pickupStop', 'dropStop']);

        if ($request->filled('route_id')) {
            $query->where('route_id', $request->route_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $assignments = $query->paginate(10)->withQueryString();
        $routes = Route::pluck('name', 'route_id');

        return view('student_transport_assignments.index', compact('assignments', 'routes'));
    }

    public function create()
    {
        $students = Student::selectRaw("student_id, CONCAT(first_name, ' ', last_name, ' (', admission_no, ')') as name")
            ->pluck('name', 'student_id')
            ->toArray();
        $routes = Route::pluck('name', 'route_id')->toArray();
        $academicYears = AcademicYear::pluck('name', 'academic_year_id')->toArray();

        // The stop options are filled in by the cascade partial, which lists only
        // the stops belonging to the chosen route.
        return view('student_transport_assignments.create', compact('students', 'routes', 'academicYears'));
    }

    public function store(Request $request)
    {
        $request->validate(StudentTransportAssignment::$rules);

        // Check capacity
        $route = Route::find($request->route_id);
        if ($route && $route->vehicle_capacity > 0) {
            $currentOccupancy = $route->studentAssignments()->where('status', 'active')->count();
            if ($currentOccupancy >= $route->vehicle_capacity) {
                Flash::error('Route is at full capacity.');
                return redirect()->back()->withInput();
            }
        }

        $assignment = StudentTransportAssignment::create($request->all());

        AuditTrail::log('Transport', 'STUDENT ASSIGN', $assignment->assignment_id, null, $assignment->toArray());

        Flash::success('Student assigned to transport successfully.');

        return redirect(route('student-transport-assignments.index'));
    }

    public function edit($id)
    {
        $assignment = StudentTransportAssignment::find($id);
        if (empty($assignment)) {
            Flash::error('Assignment not found');
            return redirect(route('student-transport-assignments.index'));
        }

        $students = Student::selectRaw("student_id, CONCAT(first_name, ' ', last_name, ' (', admission_no, ')') as name")
            ->pluck('name', 'student_id')
            ->toArray();
        $routes = Route::pluck('name', 'route_id')->toArray();
        $academicYears = AcademicYear::pluck('name', 'academic_year_id')->toArray();

        // Server-render the current route's stops so the saved pickup/drop are
        // preselected before the cascade refetches them. Labels match the ones the
        // cascade builds, time included, so nothing visibly jumps on load.
        $stops = RouteStop::where('route_id', $assignment->route_id)
            ->orderBy('sequence')
            ->get()
            ->mapWithKeys(fn ($stop) => [
                $stop->stop_id => $this->stopOptionLabel($stop),
            ])
            ->all();

        return view('student_transport_assignments.edit', compact('assignment', 'students', 'routes', 'academicYears', 'stops'));
    }

    public function update($id, Request $request)
    {
        $assignment = StudentTransportAssignment::find($id);
        if (empty($assignment)) {
            Flash::error('Assignment not found');
            return redirect(route('student-transport-assignments.index'));
        }

        $request->validate(StudentTransportAssignment::$rules);
        $oldData = $assignment->toArray();
        $assignment->update($request->all());

        AuditTrail::log('Transport', 'STUDENT ASSIGN UPDATE', $assignment->assignment_id, $oldData, $assignment->toArray());

        Flash::success('Assignment updated successfully.');

        return redirect(route('student-transport-assignments.index'));
    }

    public function destroy($id)
    {
        $assignment = StudentTransportAssignment::find($id);
        if (empty($assignment)) {
            Flash::error('Assignment not found');
            return redirect(route('student-transport-assignments.index'));
        }

        $oldData = $assignment->toArray();
        $assignment->delete();
        AuditTrail::log('Transport', 'STUDENT ASSIGN DELETE', $id, $oldData, null);
        Flash::success('Assignment deleted successfully.');

        return redirect(route('student-transport-assignments.index'));
    }

    public function getStopsByRoute($routeId)
    {
        // Only what the cascading selects render, so a stop renamed in the database
        // cannot smuggle markup into the page through string-built <option> HTML.
        $stops = RouteStop::where('route_id', $routeId)
            ->orderBy('sequence')
            ->get(['stop_id', 'stop_name', 'stop_time']);

        return response()->json($stops);
    }

    /**
     * "Main Gate  (07:30)".
     *
     * stop_time is a MySQL TIME column, so it arrives as 07:30:00. Trimmed here and
     * by formatTime() in the cascade partial, so the two never disagree.
     */
    private function stopOptionLabel(RouteStop $stop): string
    {
        if (empty($stop->stop_time)) {
            return $stop->stop_name;
        }

        return $stop->stop_name.'  ('.Carbon::parse($stop->stop_time)->format('H:i').')';
    }
}
