<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateLibraryMemberRequest;
use App\Http\Requests\UpdateLibraryMemberRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\LibraryMemberRepository;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Flash;

class LibraryMemberController extends AppBaseController
{
    /** @var LibraryMemberRepository $libraryMemberRepository*/
    private $libraryMemberRepository;

    public function __construct(LibraryMemberRepository $libraryMemberRepo)
    {
        $this->libraryMemberRepository = $libraryMemberRepo;
        $this->middleware('can:library.view')->only(['index', 'show']);
        $this->middleware('can:library.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Display a listing of the LibraryMember.
     */
    public function index(Request $request)
    {
        $query = $this->libraryMemberRepository->allQuery()
            ->with(['user', 'student', 'staff']);

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;

            // Search the person behind the membership, not just the linked
            // login: most members have no user_id at all, so filtering on
            // users.name alone hid every real record. `membership_number` was
            // also being queried here even though the column is reference_id,
            // which made any search on this page fail outright.
            //
            // The columns are listed per relation on purpose — students have
            // no employee_number, staff have no admission_no, and users have
            // neither, so one shared list just throws an unknown-column error.
            $searchable = [
                'student' => ['first_name', 'middle_name', 'last_name', 'admission_no'],
                'staff' => ['first_name', 'middle_name', 'last_name', 'employee_number'],
                'user' => ['name'],
            ];

            $query->where(function($q) use ($search, $searchable) {
                $q->where('member_type', 'like', "%$search%")
                  ->orWhere('reference_id', 'like', "%$search%");

                foreach ($searchable as $relation => $columns) {
                    $q->orWhereHas($relation, function ($r) use ($search, $columns) {
                        $r->where(function ($inner) use ($search, $columns) {
                            foreach ($columns as $column) {
                                $inner->orWhere($column, 'like', "%$search%");
                            }
                        });
                    });
                }
            });
        }

        $libraryMembers = $query->paginate(10)->withQueryString();

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

        AuditTrail::log('Library Member', 'CREATE', $libraryMember->member_id, null, $libraryMember->toArray());

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

        $oldData = $libraryMember->toArray();
        $libraryMember = $this->libraryMemberRepository->update($request->all(), $id);

        AuditTrail::log('Library Member', 'UPDATE', $libraryMember->member_id, $oldData, $libraryMember->toArray());

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

        $oldData = $libraryMember->toArray();
        $this->libraryMemberRepository->delete($id);

        AuditTrail::log('Library Member', 'DELETE', $id, $oldData, null);

        Flash::success('Library Member deleted successfully.');

        return redirect(route('library-members.index'));
    }
}
