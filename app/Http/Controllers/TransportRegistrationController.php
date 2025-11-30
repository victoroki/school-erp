<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTransportRegistrationRequest;
use App\Http\Requests\UpdateTransportRegistrationRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\TransportRegistrationRepository;
use App\Models\Student;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\AcademicYear;
use App\Models\TransportRegistration;
use Illuminate\Http\Request;
use Flash;
use Illuminate\Support\Arr;

class TransportRegistrationController extends AppBaseController
{
    /** @var TransportRegistrationRepository $transportRegistrationRepository*/
    private $transportRegistrationRepository;

    public function __construct(TransportRegistrationRepository $transportRegistrationRepo)
    {
        $this->transportRegistrationRepository = $transportRegistrationRepo;
    }

    private function getDropdownData()
    {
        return [
            'students' => Student::pluck('first_name', 'student_id'),
            'routes' => Route::pluck('name', 'route_id'),
            'stops' => RouteStop::pluck('stop_name', 'stop_id'),
            'academicYears' => AcademicYear::pluck('name', 'academic_year_id')
        ];
    }

    /**
     * Display a listing of the TransportRegistration.
     */
    public function index(Request $request)
    {
        $transportRegistrations = $this->transportRegistrationRepository->paginate(10);

        return view('transport_registrations.index')
            ->with('transportRegistrations', $transportRegistrations);
    }

    /**
     * Show the form for creating a new TransportRegistration.
     */
    public function create()
    {
        $dropdownData = $this->getDropdownData();
        return view('transport_registrations.create', $dropdownData);
    }

    /**
     * Store a newly created TransportRegistration in storage.
     */
    public function store(CreateTransportRegistrationRequest $request)
    {
        $input = $request->all();

        // Check if adding this student would exceed vehicle capacity
        if (TransportRegistration::wouldExceedCapacity($input['route_id'], $input['academic_year_id'])) {
            Flash::error('Cannot register student: Vehicle capacity would be exceeded for this route.');
            return redirect()->back()->withInput();
        }

        $transportRegistration = $this->transportRegistrationRepository->create($input);

        Flash::success('Transport Registration saved successfully.');

        return redirect(route('transport-registrations.index'));
    }

    /**
     * Display the specified TransportRegistration.
     */
    public function show($id)
    {
        $transportRegistration = $this->transportRegistrationRepository->find($id);

        if (empty($transportRegistration)) {
            Flash::error('Transport Registration not found');

            return redirect(route('transport-registrations.index'));
        }

        return view('transport_registrations.show')->with('transportRegistration', $transportRegistration);
    }

    /**
     * Show the form for editing the specified TransportRegistration.
     */
    public function edit($id)
    {
        $transportRegistration = $this->transportRegistrationRepository->find($id);

        if (empty($transportRegistration)) {
            Flash::error('Transport Registration not found');

            return redirect(route('transport-registrations.index'));
        }

        $dropdownData = $this->getDropdownData();
        return view('transport_registrations.edit', array_merge(['transportRegistration' => $transportRegistration], $dropdownData));
    }

    /**
     * Update the specified TransportRegistration in storage.
     */
    public function update($id, UpdateTransportRegistrationRequest $request)
    {
        $transportRegistration = $this->transportRegistrationRepository->find($id);

        if (empty($transportRegistration)) {
            Flash::error('Transport Registration not found');

            return redirect(route('transport-registrations.index'));
        }

        $input = $request->all();

        // Check if updating this student would exceed vehicle capacity
        if (TransportRegistration::wouldExceedCapacity($input['route_id'], $input['academic_year_id'])) {
            Flash::error('Cannot update registration: Vehicle capacity would be exceeded for this route.');
            return redirect()->back()->withInput();
        }

        $transportRegistration = $this->transportRegistrationRepository->update($input, $id);

        Flash::success('Transport Registration updated successfully.');

        return redirect(route('transport-registrations.index'));
    }

    /**
     * Remove the specified TransportRegistration from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $transportRegistration = $this->transportRegistrationRepository->find($id);

        if (empty($transportRegistration)) {
            Flash::error('Transport Registration not found');

            return redirect(route('transport-registrations.index'));
        }

        $this->transportRegistrationRepository->delete($id);

        Flash::success('Transport Registration deleted successfully.');

        return redirect(route('transport-registrations.index'));
    }
}