<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHostelRequest;
use App\Http\Requests\UpdateHostelRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\HostelRepository;
use Illuminate\Http\Request;
use Flash;

class HostelController extends AppBaseController
{
    /** @var HostelRepository $hostelRepository*/
    private $hostelRepository;

    public function __construct(HostelRepository $hostelRepo)
    {
        $this->hostelRepository = $hostelRepo;
    }

    /**
     * Display a listing of the Hostel.
     */
    public function index(Request $request)
    {
        $hostels = $this->hostelRepository->paginate(10);

        return view('hostels.index')
            ->with('hostels', $hostels);
    }

    /**
     * Show the form for creating a new Hostel.
     */
    public function create()
    {
        return view('hostels.create');
    }

    /**
     * Store a newly created Hostel in storage.
     */
    public function store(CreateHostelRequest $request)
    {
        $input = $request->all();

        $hostel = $this->hostelRepository->create($input);

        Flash::success('Hostel saved successfully.');

        return redirect(route('hostels.index'));
    }

    /**
     * Display the specified Hostel.
     */
    public function show($id)
    {
        $hostel = $this->hostelRepository->find($id);

        if (empty($hostel)) {
            Flash::error('Hostel not found');

            return redirect(route('hostels.index'));
        }

        return view('hostels.show')->with('hostel', $hostel);
    }

    /**
     * Show the form for editing the specified Hostel.
     */
    public function edit($id)
    {
        $hostel = $this->hostelRepository->find($id);

        if (empty($hostel)) {
            Flash::error('Hostel not found');

            return redirect(route('hostels.index'));
        }

        return view('hostels.edit')->with('hostel', $hostel);
    }

    /**
     * Update the specified Hostel in storage.
     */
    public function update($id, UpdateHostelRequest $request)
    {
        $hostel = $this->hostelRepository->find($id);

        if (empty($hostel)) {
            Flash::error('Hostel not found');

            return redirect(route('hostels.index'));
        }

        $hostel = $this->hostelRepository->update($request->all(), $id);

        Flash::success('Hostel updated successfully.');

        return redirect(route('hostels.index'));
    }

    /**
     * Remove the specified Hostel from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $hostel = $this->hostelRepository->find($id);

        if (empty($hostel)) {
            Flash::error('Hostel not found');

            return redirect(route('hostels.index'));
        }

        $this->hostelRepository->delete($id);

        Flash::success('Hostel deleted successfully.');

        return redirect(route('hostels.index'));
    }
}
