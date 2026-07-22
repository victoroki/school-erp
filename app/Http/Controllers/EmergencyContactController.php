<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Http\Requests\CreateEmergencyContactRequest;
use App\Http\Requests\UpdateEmergencyContactRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\EmergencyContactRepository;
use Illuminate\Http\Request;
use Flash;

class EmergencyContactController extends AppBaseController
{
    /** @var EmergencyContactRepository $emergencyContactRepository*/
    private $emergencyContactRepository;

    public function __construct(EmergencyContactRepository $emergencyContactRepo)
    {
        $this->emergencyContactRepository = $emergencyContactRepo;
        $this->middleware('can:students.view')->only(['index', 'show']);
        $this->middleware('can:students.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Display a listing of the EmergencyContact.
     */
    public function index(Request $request)
    {
        $emergencyContacts = $this->emergencyContactRepository->with(['student'])->paginate(10);

        return view('emergency_contacts.index')
            ->with('emergencyContacts', $emergencyContacts);
    }

    /**
     * Show the form for creating a new EmergencyContact.
     */
    public function create()
    {
        $students = Student::all()->mapWithKeys(function ($s) {
            return [$s->student_id => $s->admission_no . ' - ' . $s->full_name];
        })->toArray();
        return view('emergency_contacts.create', compact('students'));
    }

    /**
     * Store a newly created EmergencyContact in storage.
     */
    public function store(CreateEmergencyContactRequest $request)
    {
        $input = $request->all();

        $emergencyContact = $this->emergencyContactRepository->create($input);

        Flash::success('Emergency Contact saved successfully.');

        return redirect(route('emergencyContacts.index'));
    }

    /**
     * Display the specified EmergencyContact.
     */
    public function show($id)
    {
        $emergencyContact = $this->emergencyContactRepository->find($id);

        if (empty($emergencyContact)) {
            Flash::error('Emergency Contact not found');

            return redirect(route('emergencyContacts.index'));
        }

        return view('emergency_contacts.show')->with('emergencyContact', $emergencyContact);
    }

    /**
     * Show the form for editing the specified EmergencyContact.
     */
    public function edit($id)
    {
        $emergencyContact = $this->emergencyContactRepository->find($id);

        if (empty($emergencyContact)) {
            Flash::error('Emergency Contact not found');

            return redirect(route('emergencyContacts.index'));
        }

        $students = Student::all()->mapWithKeys(function ($s) {
            return [$s->student_id => $s->admission_no . ' - ' . $s->full_name];
        })->toArray();

        return view('emergency_contacts.edit')->with(compact('emergencyContact', 'students'));
    }

    /**
     * Update the specified EmergencyContact in storage.
     */
    public function update($id, UpdateEmergencyContactRequest $request)
    {
        $emergencyContact = $this->emergencyContactRepository->find($id);

        if (empty($emergencyContact)) {
            Flash::error('Emergency Contact not found');

            return redirect(route('emergencyContacts.index'));
        }

        $emergencyContact = $this->emergencyContactRepository->update($request->all(), $id);

        Flash::success('Emergency Contact updated successfully.');

        return redirect(route('emergencyContacts.index'));
    }

    /**
     * Remove the specified EmergencyContact from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $emergencyContact = $this->emergencyContactRepository->find($id);

        if (empty($emergencyContact)) {
            Flash::error('Emergency Contact not found');

            return redirect(route('emergencyContacts.index'));
        }

        $this->emergencyContactRepository->delete($id);

        Flash::success('Emergency Contact deleted successfully.');

        return redirect(route('emergencyContacts.index'));
    }
}
