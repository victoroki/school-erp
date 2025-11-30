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
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bookIssues = BookIssue::with(['book', 'member', 'issuer'])->orderBy('created_at', 'desc')->paginate(10);
        return view('book_issues.index', compact('bookIssues'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $books = Book::where('available_quantity', '>', 0)->pluck('title', 'book_id');
        // We might want to show member name + reference ID
        $members = LibraryMember::with('user')->get()->mapWithKeys(function ($member) {
            return [$member->id => $member->user ? $member->user->name . ' (' . $member->reference_id . ')' : $member->reference_id];
        });
        
        return view('book_issues.create', compact('books', 'members'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate(BookIssue::$rules);

        $book = Book::find($request->book_id);
        if ($book->available_quantity <= 0) {
            Flash::error('Book is not available for issue.');
            return redirect()->back()->withInput();
        }

        $input = $request->all();
        $input['issued_by'] = Auth::id();
        $input['status'] = 'issued';

        $bookIssue = BookIssue::create($input);

        // Decrease available quantity
        $book->decrement('available_quantity');

        Flash::success('Book issued successfully.');

        return redirect(route('book-issues.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $bookIssue = BookIssue::with(['book', 'member', 'issuer', 'receiver'])->find($id);

        if (empty($bookIssue)) {
            Flash::error('Book Issue not found');
            return redirect(route('book-issues.index'));
        }

        return view('book_issues.show', compact('bookIssue'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $bookIssue = BookIssue::find($id);

        if (empty($bookIssue)) {
            Flash::error('Book Issue not found');
            return redirect(route('book-issues.index'));
        }

        $books = Book::pluck('title', 'book_id');
        $members = LibraryMember::with('user')->get()->mapWithKeys(function ($member) {
            return [$member->id => $member->user ? $member->user->name . ' (' . $member->reference_id . ')' : $member->reference_id];
        });

        return view('book_issues.edit', compact('bookIssue', 'books', 'members'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $bookIssue = BookIssue::find($id);

        if (empty($bookIssue)) {
            Flash::error('Book Issue not found');
            return redirect(route('book-issues.index'));
        }

        $request->validate([
            'status' => 'required|in:issued,returned,overdue,lost',
            'return_date' => 'nullable|date',
            'remarks' => 'nullable|string'
        ]);

        $input = $request->all();
        
        // Handle Return
        if ($input['status'] == 'returned' && $bookIssue->status != 'returned') {
            $input['return_date'] = $input['return_date'] ?? Carbon::now();
            $input['received_by'] = Auth::id();
            
            // Increase available quantity
            $bookIssue->book->increment('available_quantity');
        }
        
        // Handle undo return (if status changed back from returned to issued/overdue)
        if ($bookIssue->status == 'returned' && $input['status'] != 'returned') {
             // Decrease available quantity again
             if ($bookIssue->book->available_quantity > 0) {
                 $bookIssue->book->decrement('available_quantity');
             }
        }

        $bookIssue->update($input);

        Flash::success('Book Issue updated successfully.');

        return redirect(route('book-issues.index'));
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
