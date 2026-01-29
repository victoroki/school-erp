<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookIssue;
use App\Models\LibraryMember;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class LibraryService
{
    /**
     * Issue a book to a member
     */
    public function issueBook($data)
    {
        return DB::transaction(function () use ($data) {
            $book = Book::findOrFail($data['book_id']);
            $member = LibraryMember::findOrFail($data['member_id']);

            // Validations
            if ($book->available_quantity <= 0) {
                throw new Exception("Book is not available for issue.");
            }

            if ($member->status !== 'active') {
                throw new Exception("Member is not active.");
            }

            $activeIssues = BookIssue::where('member_id', $member->member_id)
                ->where('status', 'issued')
                ->count();

            if ($activeIssues >= $member->max_allowed_books) {
                throw new Exception("Member has reached maximum book limit.");
            }

            // Calculate Due Date
            // Default 14 days
            $dueDate = Carbon::now()->addDays(14); 

            $issue = BookIssue::create([
                'book_id' => $book->book_id,
                'member_id' => $member->member_id,
                'issue_date' => Carbon::now(),
                'due_date' => $data['due_date'] ?? $dueDate,
                'status' => 'issued',
                'issued_by' => auth()->id() ?? 1
            ]);

            // Update Book Availability
            $book->decrement('available_quantity');

            return $issue;
        });
    }

    /**
     * Return a book
     */
    public function returnBook($issueId, $data = [])
    {
        return DB::transaction(function () use ($issueId, $data) {
            $issue = BookIssue::findOrFail($issueId);
            
            if ($issue->status === 'returned') {
                throw new Exception("Book is already returned.");
            }

            $issue->return_date = Carbon::now();
            $issue->status = 'returned';
            $issue->received_by = auth()->id() ?? 1;
            $issue->remarks = $data['remarks'] ?? null;

             // Calculate Fine
             $daysOverdue = 0;
             if ($issue->return_date->gt($issue->due_date)) {
                 $daysOverdue = $issue->return_date->diffInDays($issue->due_date);
                 $finePerDay = 50; // KSh 50 per day
                 $issue->fine_amount = $daysOverdue * $finePerDay;
                 
                 if ($issue->fine_amount > 0) {
                      $issue->status = 'overdue'; // Mark as overdue if returned late (or keep as returned but with fine?)
                      // Usually 'returned' means book is back. we can just store the fine.
                      $issue->status = 'returned';
                 }
             }

            $issue->save();

            // Update Book Availability
            $issue->book->increment('available_quantity');

            return $issue;
        });
    }

    /**
     * Get Library Statistics for Dashboard
     */
    public function getDashboardStats()
    {
        return [
            'total_books' => Book::count(),
            'total_issued' => BookIssue::where('status', 'issued')->count(),
            'books_available' => Book::sum('available_quantity'),
            'total_members' => LibraryMember::where('status', 'active')->count(),
            'overdue_books' => BookIssue::where('status', 'issued')
                ->where('due_date', '<', Carbon::now())
                ->count()
        ];
    }
}
