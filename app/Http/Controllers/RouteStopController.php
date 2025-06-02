<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRouteStopRequest;
use App\Http\Requests\UpdateRouteStopRequest;
use App\Http\Controllers\AppBaseController;
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

    /**
     * Display a listing of the RouteStop.
     */
    public function index(Request $request)
    {
        $routeStops = $this->routeStopRepository->paginate(10);

        return view('route_stops.index')
            ->with('routeStops', $routeStops);
    }

    /**
     * Show the form for creating a new RouteStop.
     */
    public function create()
    {
        return view('route_stops.create');
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
        $routeStop = $this->routeStopRepository->find($id);

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
        $routeStop = $this->routeStopRepository->find($id);

        if (empty($routeStop)) {
            Flash::error('Route Stop not found');

            return redirect(route('routeStops.index'));
        }

        return view('route_stops.edit')->with('routeStop', $routeStop);
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

        $this->routeStopRepository->delete($id);

        Flash::success('Route Stop deleted successfully.');

        return redirect(route('routeStops.index'));
    }
}
