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
        $query = $this->libraryMemberRepository->allQuery()->with('user');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('member_type', 'like', "%$search%")
                  ->orWhere('membership_number', 'like', "%$search%") // Assuming this field exists or similar
                  ->orWhereHas('user', function($u) use ($search) {
                       $u->where('name', 'like', "%$search%"); // User relation search
                  });
            });
        }

        $libraryMembers = $query->paginate(10);

        return view('library_members.index')
            ->with('libraryMembers', $libraryMembers);
    }

    /**
     * Show the form for creating a new LibraryMember.
     */
    public function create()
    {
        // We need to fetch students/staff who are NOT yet library members
        // simplified for now: just get all users as potential members
        $users = \App\Models\User::pluck('name', 'id');
        return view('library_members.create', compact('users'));
    }

    /**
     * Store a newly created LibraryMember in storage.
     */
    public function store(CreateLibraryMemberRequest $request)
    {
        $input = $request->all();
        // Auto-generate a membership ID if not provided
        if (!isset($input['reference_id'])) {
             $input['reference_id'] = 'LIB-' . date('Y') . '-' . rand(1000, 9999);
        }

        $libraryMember = $this->libraryMemberRepository->create($input);

        Flash::success('Library Member saved successfully.');

        return redirect(route('library-members.index'));
    }

    /**
     * Display the specified LibraryMember.
     */
    public function show($id)
    {
        $libraryMember = $this->libraryMemberRepository->find($id);

        if (empty($libraryMember)) {
            Flash::error('Library Member not found');

            return redirect(route('library-members.index'));
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

            return redirect(route('library-members.index'));
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

            return redirect(route('library-members.index'));
        }

        $libraryMember = $this->libraryMemberRepository->update($request->all(), $id);

        Flash::success('Library Member updated successfully.');

        return redirect(route('library-members.index'));
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

            return redirect(route('library-members.index'));
        }

        $this->libraryMemberRepository->delete($id);

        Flash::success('Library Member deleted successfully.');

        return redirect(route('library-members.index'));
    }
}
