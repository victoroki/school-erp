<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\Student;
use App\Models\StudentTransportAssignment;
use Illuminate\Http\Request;
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

        $assignments = $query->paginate(10);
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
        $stops = RouteStop::all()->groupBy('route_id');

        return view('student_transport_assignments.create', compact('students', 'routes', 'academicYears', 'stops'));
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

        StudentTransportAssignment::create($request->all());

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
        $stops = RouteStop::where('route_id', $assignment->route_id)->pluck('stop_name', 'stop_id')->toArray();

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
        $assignment->update($request->all());

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

        $assignment->delete();
        Flash::success('Assignment deleted successfully.');

        return redirect(route('student-transport-assignments.index'));
    }

    public function getStopsByRoute($routeId)
    {
        $stops = RouteStop::where('route_id', $routeId)->orderBy('sequence')->get();
        return response()->json($stops);
    }
}
