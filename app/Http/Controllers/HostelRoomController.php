<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHostelRoomRequest;
use App\Http\Requests\UpdateHostelRoomRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\HostelRoomRepository;
use Illuminate\Http\Request;
use Flash;

class HostelRoomController extends AppBaseController
{
    /** @var HostelRoomRepository $hostelRoomRepository*/
    private $hostelRoomRepository;

    public function __construct(HostelRoomRepository $hostelRoomRepo)
    {
        $this->hostelRoomRepository = $hostelRoomRepo;
    }

    /**
     * Display a listing of the HostelRoom.
     */
    public function index(Request $request)
    {
        $hostelRooms = $this->hostelRoomRepository->paginate(10);

        return view('hostel_rooms.index')
            ->with('hostelRooms', $hostelRooms);
    }

    /**
     * Show the form for creating a new HostelRoom.
     */
    public function create()
    {
        return view('hostel_rooms.create');
    }

    /**
     * Store a newly created HostelRoom in storage.
     */
    public function store(CreateHostelRoomRequest $request)
    {
        $input = $request->all();

        $hostelRoom = $this->hostelRoomRepository->create($input);

        Flash::success('Hostel Room saved successfully.');

        return redirect(route('hostelRooms.index'));
    }

    /**
     * Display the specified HostelRoom.
     */
    public function show($id)
    {
        $hostelRoom = $this->hostelRoomRepository->find($id);

        if (empty($hostelRoom)) {
            Flash::error('Hostel Room not found');

            return redirect(route('hostelRooms.index'));
        }

        return view('hostel_rooms.show')->with('hostelRoom', $hostelRoom);
    }

    /**
     * Show the form for editing the specified HostelRoom.
     */
    public function edit($id)
    {
        $hostelRoom = $this->hostelRoomRepository->find($id);

        if (empty($hostelRoom)) {
            Flash::error('Hostel Room not found');

            return redirect(route('hostelRooms.index'));
        }

        return view('hostel_rooms.edit')->with('hostelRoom', $hostelRoom);
    }

    /**
     * Update the specified HostelRoom in storage.
     */
    public function update($id, UpdateHostelRoomRequest $request)
    {
        $hostelRoom = $this->hostelRoomRepository->find($id);

        if (empty($hostelRoom)) {
            Flash::error('Hostel Room not found');

            return redirect(route('hostelRooms.index'));
        }

        $hostelRoom = $this->hostelRoomRepository->update($request->all(), $id);

        Flash::success('Hostel Room updated successfully.');

        return redirect(route('hostelRooms.index'));
    }

    /**
     * Remove the specified HostelRoom from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $hostelRoom = $this->hostelRoomRepository->find($id);

        if (empty($hostelRoom)) {
            Flash::error('Hostel Room not found');

            return redirect(route('hostelRooms.index'));
        }

        $this->hostelRoomRepository->delete($id);

        Flash::success('Hostel Room deleted successfully.');

        return redirect(route('hostelRooms.index'));
    }
}
