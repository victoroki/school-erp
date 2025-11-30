<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\VehicleRepository;
use App\Models\Staff;
use Illuminate\Http\Request;
use Flash;

class VehicleController extends AppBaseController
{
    /** @var VehicleRepository $vehicleRepository*/
    private $vehicleRepository;

    public function __construct(VehicleRepository $vehicleRepo)
    {
        $this->vehicleRepository = $vehicleRepo;
    }

    private function getDropdownData()
    {
        return [
            'drivers' => Staff::where('staff_type', 'driver')->pluck('first_name', 'staff_id')
        ];
    }

    /**
     * Display a listing of the Vehicle.
     */
    public function index(Request $request)
    {
        $vehicles = $this->vehicleRepository->paginate(10);

        return view('vehicles.index')
            ->with('vehicles', $vehicles);
    }

    /**
     * Show the form for creating a new Vehicle.
     */
    public function create()
    {
        $dropdownData = $this->getDropdownData();
        return view('vehicles.create', $dropdownData);
    }

    /**
     * Store a newly created Vehicle in storage.
     */
    public function store(CreateVehicleRequest $request)
    {
        $input = $request->all();

        $vehicle = $this->vehicleRepository->create($input);

        Flash::success('Vehicle saved successfully.');

        return redirect(route('vehicles.index'));
    }

    /**
     * Display the specified Vehicle.
     */
    public function show($id)
    {
        $vehicle = $this->vehicleRepository->find($id);

        if (empty($vehicle)) {
            Flash::error('Vehicle not found');

            return redirect(route('vehicles.index'));
        }

        return view('vehicles.show')->with('vehicle', $vehicle);
    }

    /**
     * Show the form for editing the specified Vehicle.
     */
    public function edit($id)
    {
        $vehicle = $this->vehicleRepository->find($id);

        if (empty($vehicle)) {
            Flash::error('Vehicle not found');

            return redirect(route('vehicles.index'));
        }

        $dropdownData = $this->getDropdownData();
        return view('vehicles.edit', array_merge(['vehicle' => $vehicle], $dropdownData));
    }

    /**
     * Update the specified Vehicle in storage.
     */
    public function update($id, UpdateVehicleRequest $request)
    {
        $vehicle = $this->vehicleRepository->find($id);

        if (empty($vehicle)) {
            Flash::error('Vehicle not found');

            return redirect(route('vehicles.index'));
        }

        $vehicle = $this->vehicleRepository->update($request->all(), $id);

        Flash::success('Vehicle updated successfully.');

        return redirect(route('vehicles.index'));
    }

    /**
     * Remove the specified Vehicle from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $vehicle = $this->vehicleRepository->find($id);

        if (empty($vehicle)) {
            Flash::error('Vehicle not found');

            return redirect(route('vehicles.index'));
        }

        $this->vehicleRepository->delete($id);

        Flash::success('Vehicle deleted successfully.');

        return redirect(route('vehicles.index'));
    }
}