<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookIssue;
use App\Models\LibraryMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileLibraryController extends Controller
{
    /**
     * GET /api/mobile/library/my-books
     *
     * Returns books currently borrowed by the authenticated user.
     */
    public function myBorrowedBooks(Request $request): JsonResponse
    {
        $user = $request->user();

        $member = LibraryMember::where('user_id', $user->id)->first();
        if (!$member) {
            return response()->json(['message' => 'You are not registered as a library member.'], 404);
        }

        $borrowedBooks = BookIssue::where('member_id', $member->member_id)
            ->where('status', 'issued')
            ->with('book')
            ->orderBy('issue_date', 'desc')
            ->get()
            ->map(fn($issue) => [
                'book_title' => $issue->book->title ?? 'Unknown',
                'issue_date' => $issue->issue_date->toDateString(),
                'due_date'   => $issue->due_date->toDateString(),
                'overdue'    => $issue->due_date->isPast(),
                'fine'       => (float) $issue->fine_amount,
            ]);

        return response()->json(['borrowed_books' => $borrowedBooks]);
    }

    /**
     * GET /api/mobile/library/catalog
     *
     * Returns a searchable list of books in the library.
     */
    public function catalog(Request $request): JsonResponse
    {
        $search = $request->query('search');

        $books = Book::query()
            ->when($search, function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('author', 'like', "%$search%");
            })
            ->paginate(20);

        return response()->json($books);
    }
}