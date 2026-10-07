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
     * The staff record of the acting user.
     *
     * book_issues.issued_by and book_issues.received_by are foreign keys to
     * staff.staff_id, but the service wrote auth()->id() into them, which is a
     * users.id. For any user whose id did not also exist as a staff row the
     * insert failed with a foreign key violation, and the old `?? 1` fallback
     * quietly attributed the loan to staff #1 instead. Both columns are
     * nullable, so an admin with no staff record simply leaves them null.
     */
    private function actingStaffId(): ?int
    {
        return auth()->user()?->staff?->staff_id;
    }

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

            // Due date falls back to the configured loan period. It was a
            // hardcoded 14 days; schools that lend for a term need this to be
            // a setting rather than a deploy.
            $dueDate = Carbon::now()->addDays(LibrarySettings::loanPeriodDays());

            $issue = BookIssue::create([
                'book_id' => $book->book_id,
                'member_id' => $member->member_id,
                'issue_date' => Carbon::now(),
                'due_date' => $data['due_date'] ?? $dueDate,
                'status' => 'issued',
                'issued_by' => $this->actingStaffId()
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
            $issue->received_by = $this->actingStaffId();
            $issue->remarks = $data['remarks'] ?? null;

            // Fine is charged on the days it was actually overdue, at the
            // configured daily rate. It was a hardcoded KES 50 per day.
            $issue->fine_amount = LibrarySettings::outstandingFine(
                $issue->due_date,
                $issue->return_date
            );

            // The status stays 'returned': the book is back on the shelf. The
            // overdue-ness is recorded by return_date/fine_amount, and the
            // 'overdue' status is reserved for books still out past their due
            // date. The previous code set 'overdue' and then immediately
            // overwrote it with 'returned' again.
            $issue->status = 'returned';

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
        $overdue = BookIssue::where('status', 'issued')
            ->where('due_date', '<', Carbon::now())
            ->get(['issue_id', 'due_date']);

        // What the outstanding fines would come to if every overdue book were
        // returned today. Uses the configured rate rather than a fixed
        // multiple of the overdue count.
        $projectedFines = $overdue->sum(function ($issue) {
            return LibrarySettings::outstandingFine($issue->due_date);
        });

        return [
            'total_books' => Book::count(),
            'total_issued' => BookIssue::where('status', 'issued')->count(),
            'books_available' => Book::sum('available_quantity'),
            'total_members' => LibraryMember::where('status', 'active')->count(),
            'overdue_books' => $overdue->count(),
            'overdue_fines_total' => round($projectedFines, 2),
            'fine_per_day' => LibrarySettings::finePerDay(),
            'loan_period_days' => LibrarySettings::loanPeriodDays(),
        ];
    }
}
