<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookIssue;
use App\Models\LibraryMember;
use App\Models\Parents;
use App\Models\StudentParentRelationship;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileLibraryController extends Controller
{
    /**
     * GET /api/mobile/library/my-books
     *
     * PHASE 6 — current loans + return history for the caller's own member
     * record. Parents may pass ?student_id= for a linked child (the child's
     * member record, if one exists — member rows are user-keyed, so a parent
     * can only ever see what the child's own account would see).
     */
    public function myBorrowedBooks(Request $request): JsonResponse
    {
        $user = $request->user();

        $member = $this->memberFor($user, $request);
        if (!$member) {
            return response()->json(['message' => 'You are not registered as a library member.'], 404);
        }

        $issues = BookIssue::where('member_id', $member->member_id)
            ->with('book')
            ->orderBy('issue_date', 'desc')
            ->get();

        $borrowed = $issues
            ->filter(fn ($issue) => $issue->status === 'issued')
            ->map(fn ($issue) => [
                'book_title' => $issue->book->title ?? 'Unknown',
                'author'     => $issue->book->author,
                'issue_date' => $issue->issue_date->toDateString(),
                'due_date'   => $issue->due_date->toDateString(),
                'overdue'    => $issue->due_date->isPast(),
                'fine'       => (float) $issue->fine_amount,
                'status'     => $issue->status,
            ])
            ->values();

        $history = $issues
            ->filter(fn ($issue) => in_array($issue->status, ['returned', 'lost'], true))
            ->map(fn ($issue) => [
                'book_title'  => $issue->book->title ?? 'Unknown',
                'author'      => $issue->book->author,
                'issue_date'  => $issue->issue_date->toDateString(),
                'due_date'    => $issue->due_date->toDateString(),
                'return_date' => $issue->return_date?->toDateString(),
                'status'      => $issue->status,
                'fine'        => (float) $issue->fine_amount,
            ])
            ->values();

        return response()->json([
            'borrowed_books' => $borrowed,
            'history'        => $history,
        ]);
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

    /**
     * The library member record the caller may read:
     *  - Parents with ?student_id= → the linked child's member record;
     *  - everyone else → their own user-keyed member record.
     */
    private function memberFor($user, Request $request): ?LibraryMember
    {
        if ($user->hasRole('Parent')) {
            $parent = Parents::where('user_id', $user->id)->first();
            $requested = (int) $request->query('student_id', 0);

            if ($parent && $requested > 0) {
                $linked = StudentParentRelationship::where('parent_id', $parent->parent_id)
                    ->where('student_id', $requested)
                    ->with('student')
                    ->first();

                if ($linked?->student?->user_id) {
                    return LibraryMember::where('user_id', $linked->student->user_id)->first();
                }
            }

            return LibraryMember::where('user_id', $user->id)->first();
        }

        return LibraryMember::where('user_id', $user->id)->first();
    }
}