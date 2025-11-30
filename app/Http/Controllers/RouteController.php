<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRouteRequest;
use App\Http\Requests\UpdateRouteRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\RouteRepository;
use Illuminate\Http\Request;
use Flash;

class RouteController extends AppBaseController
{
    /** @var RouteRepository $routeRepository*/
    private $routeRepository;

    public function __construct(RouteRepository $routeRepo)
    {
        $this->routeRepository = $routeRepo;
    }

    private function getDropdownData()
    {
        return [];
    }

    /**
     * Display a listing of the Route.
     */
    public function index(Request $request)
    {
        $routes = $this->routeRepository->paginate(10);

        return view('routes.index')
            ->with('routes', $routes);
    }

    /**
     * Show the form for creating a new Route.
     */
    public function create()
    {
        $dropdownData = $this->getDropdownData();
        return view('routes.create', $dropdownData);
    }

    /**
     * Store a newly created Route in storage.
     */
    public function store(CreateRouteRequest $request)
    {
        $input = $request->all();

        $route = $this->routeRepository->create($input);

        Flash::success('Route saved successfully.');

        return redirect(route('routes.index'));
    }

    /**
     * Display the specified Route.
     */
    public function show($id)
    {
        $route = $this->routeRepository->find($id);

        if (empty($route)) {
            Flash::error('Route not found');

            return redirect(route('routes.index'));
        }

        return view('routes.show')->with('route', $route);
    }

    /**
     * Show the form for editing the specified Route.
     */
    public function edit($id)
    {
        $route = $this->routeRepository->find($id);

        if (empty($route)) {
            Flash::error('Route not found');

            return redirect(route('routes.index'));
        }

        $dropdownData = $this->getDropdownData();
        return view('routes.edit', array_merge(['route' => $route], $dropdownData));
    }

    /**
     * Update the specified Route in storage.
     */
    public function update($id, UpdateRouteRequest $request)
    {
        $route = $this->routeRepository->find($id);

        if (empty($route)) {
            Flash::error('Route not found');

            return redirect(route('routes.index'));
        }

        $route = $this->routeRepository->update($request->all(), $id);

        Flash::success('Route updated successfully.');

        return redirect(route('routes.index'));
    }

    /**
     * Remove the specified Route from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $route = $this->routeRepository->find($id);

        if (empty($route)) {
            Flash::error('Route not found');

            return redirect(route('routes.index'));
        }

        $this->routeRepository->delete($id);

        Flash::success('Route deleted successfully.');

        return redirect(route('routes.index'));
    }
}
