<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateLibraryMemberRequest;
use App\Http\Requests\UpdateLibraryMemberRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\LibraryMemberRepository;
use Illuminate\Http\Request;
use Flash;

class LibraryMemberController extends AppBaseController
{
    /** @var LibraryMemberRepository $libraryMemberRepository*/
    private $libraryMemberRepository;

    public function __construct(LibraryMemberRepository $libraryMemberRepo)
    {
        $this->libraryMemberRepository = $libraryMemberRepo;
    }

    /**
     * Display a listing of the LibraryMember.
     */
    public function index(Request $request)
    {
        $libraryMembers = $this->libraryMemberRepository->paginate(10);

        return view('library_members.index')
            ->with('libraryMembers', $libraryMembers);
    }

    /**
     * Show the form for creating a new LibraryMember.
     */
    public function create()
    {
        return view('library_members.create');
    }

    /**
     * Store a newly created LibraryMember in storage.
     */
    public function store(CreateLibraryMemberRequest $request)
    {
        $input = $request->all();

        $libraryMember = $this->libraryMemberRepository->create($input);

        Flash::success('Library Member saved successfully.');

        return redirect(route('libraryMembers.index'));
    }

    /**
     * Display the specified LibraryMember.
     */
    public function show($id)
    {
        $libraryMember = $this->libraryMemberRepository->find($id);

        if (empty($libraryMember)) {
            Flash::error('Library Member not found');

            return redirect(route('libraryMembers.index'));
        }

        return view('library_members.show')->with('libraryMember', $libraryMember);
    }

    /**
     * Show the form for editing the specified LibraryMember.
     */
    public function edit($id)
    {
        $libraryMember = $this->libraryMemberRepository->find($id);

        if (empty($libraryMember)) {
            Flash::error('Library Member not found');

            return redirect(route('libraryMembers.index'));
        }

        return view('library_members.edit')->with('libraryMember', $libraryMember);
    }

    /**
     * Update the specified LibraryMember in storage.
     */
    public function update($id, UpdateLibraryMemberRequest $request)
    {
        $libraryMember = $this->libraryMemberRepository->find($id);

        if (empty($libraryMember)) {
            Flash::error('Library Member not found');

            return redirect(route('libraryMembers.index'));
        }

        $libraryMember = $this->libraryMemberRepository->update($request->all(), $id);

        Flash::success('Library Member updated successfully.');

        return redirect(route('libraryMembers.index'));
    }

    /**
     * Remove the specified LibraryMember from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $libraryMember = $this->libraryMemberRepository->find($id);

        if (empty($libraryMember)) {
            Flash::error('Library Member not found');

            return redirect(route('libraryMembers.index'));
        }

        $this->libraryMemberRepository->delete($id);

        Flash::success('Library Member deleted successfully.');

        return redirect(route('libraryMembers.index'));
    }
}
