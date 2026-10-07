<?php

namespace App\Http\Controllers;

use App\Models\BookIssue;
use App\Models\Book;
use App\Models\LibraryMember;
use App\Models\AuditTrail;
use App\Services\LibrarySettings;
use Illuminate\Http\Request;
use Flash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class BookIssueController extends Controller
{
    protected $libraryService;

    public function __construct(\App\Services\LibraryService $libraryService)
    {
        $this->libraryService = $libraryService;
        $this->middleware('can:library.view')->only(['index', 'show']);
        $this->middleware('can:library.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = BookIssue::with(['book', 'member.user', 'member.student', 'member.staff', 'issuer']);

        if ($request->has('status') && $request->status != '') {
            if ($request->status === 'overdue') {
                // A book is overdue while it is still out and past its due
                // date. The status column is never set to 'overdue' for these —
                // returning a book records 'returned' and the lateness lives in
                // return_date/fine_amount — so filtering on the column matched
                // nothing at all.
                $query->where('status', 'issued')
                    ->where('due_date', '<', Carbon::now());
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;

            // Member names come from the student/staff record. users.name is
            // empty for every membership, so searching on it alone found
            // nothing.
            $memberColumns = [
                'member.student' => ['first_name', 'middle_name', 'last_name', 'admission_no'],
                'member.staff' => ['first_name', 'middle_name', 'last_name', 'employee_number'],
                'member.user' => ['name'],
            ];

            $query->where(function ($q) use ($search, $memberColumns) {
                $q->whereHas('book', function ($b) use ($search) {
                    $b->where('title', 'like', "%$search%")
                      ->orWhere('isbn', 'like', "%$search%")
                      ->orWhere('author', 'like', "%$search%");
                });

                foreach ($memberColumns as $relation => $columns) {
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

        $bookIssues = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        return view('book_issues.index', compact('bookIssues'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Fetch only books with quantity > 0
        $books = Book::where('available_quantity', '>', 0)->get()->mapWithKeys(function ($book) {
            return [$book->book_id => $book->title . ' (ISBN: ' . $book->isbn . ')'];
        });

        // Eager-load the person behind each membership. `user` alone leaves the
        // label blank for members who have no linked login, which is every
        // member created from a student or staff record.
        $members = LibraryMember::with(['user', 'student', 'staff'])
            ->where('status', 'active')
            ->get()
            ->sortBy('display_name')
            ->mapWithKeys(function ($member) {
                return [$member->member_id => $member->display_name];
            });
        
        return view('book_issues.create', compact('books', 'members') + [
            // The form suggests a due date and recalculates it when the issue
            // date changes, both from the configured loan period rather than a
            // hardcoded 14 days.
            'loanPeriodDays' => LibrarySettings::loanPeriodDays(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,book_id',
            'member_id' => 'required|exists:library_members,member_id', // Ensure PK is used
            'due_date' => 'required|date'
        ]);

        try {
            $this->libraryService->issueBook($request->all());
            AuditTrail::log('Book Issue', 'ISSUE', $request->book_id, null, $request->only(['book_id', 'member_id', 'due_date']));
            Flash::success('Book issued successfully.');
        } catch (\Exception $e) {
            Flash::error('Error issuing book: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }

        return redirect(route('book-issues.index'));
    }

    /**
     * Show Return Book Form
     */
    public function returnModal($id)
    {
        $issue = BookIssue::with(['book', 'member.user', 'member.student', 'member.staff'])->findOrFail($id);
        
        // Calculate provisional fine
        $fine = 0;
        $diff = 0;
        if(Carbon::now()->gt($issue->due_date)) {
             $diff = Carbon::now()->diffInDays($issue->due_date);
             $fine = $diff * 50; // 50 per day
        }

        return view('book_issues.return_modal', compact('issue', 'fine', 'diff'));
    }

    /**
     * Process Book Return
     */
    public function returnBook(Request $request, $id)
    {
        try {
            $this->libraryService->returnBook($id, $request->all());
            AuditTrail::log('Book Issue', 'RETURN', $id, ['status' => 'issued'], ['status' => 'returned']);
            Flash::success('Book returned successfully.');
        } catch (\Exception $e) {
             Flash::error('Error returning book: ' . $e->getMessage());
        }
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $bookIssue = BookIssue::find($id);

        if (empty($bookIssue)) {
            Flash::error('Book Issue not found');
            return redirect(route('book-issues.index'));
        }

        // If deleting an issued book, restore quantity
        if ($bookIssue->status == 'issued' || $bookIssue->status == 'overdue') {
            $bookIssue->book->increment('available_quantity');
        }

        $oldData = $bookIssue->toArray();
        $bookIssue->delete();

        AuditTrail::log('Book Issue', 'DELETE', $id, $oldData, null);

        Flash::success('Book Issue deleted successfully.');

        return redirect(route('book-issues.index'));
    }
}
