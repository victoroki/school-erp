<?php

namespace App\Http\Controllers;

use App\Models\BookIssue;
use App\Models\Book;
use App\Models\LibraryMember;
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
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = BookIssue::with(['book', 'member.user', 'issuer']);
        
        if ($request->has('status') && $request->status != '') {
             $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search != '') {
             $search = $request->search;
             $query->whereHas('member.user', function($q) use ($search) {
                 $q->where('name', 'like', "%$search%");
             })->orWhereHas('book', function($q) use ($search) {
                 $q->where('title', 'like', "%$search%");
             });
        }

        $bookIssues = $query->orderBy('created_at', 'desc')->paginate(10);
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
        
        $members = LibraryMember::with('user')->where('status', 'active')->get()->mapWithKeys(function ($member) {
            return [$member->member_id => ($member->user->name ?? 'Unknown') . ' (' . $member->reference_id . ')'];
        });
        
        return view('book_issues.create', compact('books', 'members'));
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
        $issue = BookIssue::with(['book', 'member.user'])->findOrFail($id);
        
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

        $bookIssue->delete();

        Flash::success('Book Issue deleted successfully.');

        return redirect(route('book-issues.index'));
    }
}
