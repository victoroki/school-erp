<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRouteStopRequest;
use App\Http\Requests\UpdateRouteStopRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\Route;
use App\Models\RouteStop;
use App\Repositories\RouteStopRepository;
use Illuminate\Http\Request;
use Flash;

class RouteStopController extends AppBaseController
{
    /** @var RouteStopRepository $routeStopRepository*/
    private $routeStopRepository;

    public function __construct(RouteStopRepository $routeStopRepo)
    {
        $this->routeStopRepository = $routeStopRepo;
    }

    private function getdropdownData(){
        return [
            'route' => Route::pluck('name', 'route_id')
        ];
    }

    /**
     * Display a listing of the RouteStop.
     */
    public function index(Request $request)
    {
        $query = RouteStop::with(['route']);

        if ($request->filled('route_id')) {
            $query->where('route_id', $request->route_id);
        }

        $routeStops = $query->paginate(10);
        $routes = Route::pluck('name', 'route_id');

        return view('route_stops.index', compact('routeStops', 'routes'));
    }

    /**
     * Show the form for creating a new RouteStop.
     */
    public function create()
    {
        $dropdownData = $this->getdropdownData();
        return view('route_stops.create', $dropdownData);
    }

    /**
     * Store a newly created RouteStop in storage.
     */
    public function store(CreateRouteStopRequest $request)
    {
        $input = $request->all();

        $routeStop = $this->routeStopRepository->create($input);

        Flash::success('Route Stop saved successfully.');

        return redirect(route('routeStops.index'));
    }

    /**
     * Display the specified RouteStop.
     */
    public function show($id)
    {
        $routeStop = RouteStop::with(['route', 'studentAssignments.student'])->find($id);

        if (empty($routeStop)) {
            Flash::error('Route Stop not found');

            return redirect(route('routeStops.index'));
        }

        return view('route_stops.show')->with('routeStop', $routeStop);
    }

    /**
     * Show the form for editing the specified RouteStop.
     */
    public function edit($id)
    {
        $dropdownData = $this->getdropdownData();
        $routeStop = $this->routeStopRepository->find($id);

        if (empty($routeStop)) {
            Flash::error('Route Stop not found');

            return redirect(route('routeStops.index'));
        }

        return view('route_stops.edit', array_merge([
            'routeStop' => $routeStop
        ], $dropdownData));
    }

    /**
     * Update the specified RouteStop in storage.
     */
    public function update($id, UpdateRouteStopRequest $request)
    {
        $routeStop = $this->routeStopRepository->find($id);

        if (empty($routeStop)) {
            Flash::error('Route Stop not found');

            return redirect(route('routeStops.index'));
        }

        $routeStop = $this->routeStopRepository->update($request->all(), $id);

        Flash::success('Route Stop updated successfully.');

        return redirect(route('routeStops.index'));
    }

    /**
     * Remove the specified RouteStop from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $routeStop = $this->routeStopRepository->find($id);

        if (empty($routeStop)) {
            Flash::error('Route Stop not found');

            return redirect(route('routeStops.index'));
        }

        // Check dependencies
        if ($routeStop->getStudentCount() > 0) {
            Flash::error('Cannot delete stop that has active student assignments.');
            return redirect()->back();
        }

        $this->routeStopRepository->delete($id);

        Flash::success('Route Stop deleted successfully.');

        return redirect(route('routeStops.index'));
    }
}
