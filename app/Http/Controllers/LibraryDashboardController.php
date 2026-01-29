<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookIssue;
use App\Models\LibraryMember;
use App\Models\BookCategory;
use App\Services\LibraryService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LibraryDashboardController extends Controller
{
    protected $libraryService;

    public function __construct(LibraryService $libraryService)
    {
        $this->libraryService = $libraryService;
    }

    public function index()
    {
        // Get statistics
        $stats = $this->libraryService->getDashboardStats();

        // Get recent issues (last 10)
        $recentIssues = BookIssue::with(['book', 'member.user', 'issuer'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get overdue books
        $overdueBooks = BookIssue::with(['book', 'member.user'])
            ->where('status', 'issued')
            ->where('due_date', '<', Carbon::now())
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();

        // Top borrowed books (this month)
        $topBooks = BookIssue::with('book')
            ->whereMonth('issue_date', Carbon::now()->month)
            ->whereYear('issue_date', Carbon::now()->year)
            ->selectRaw('book_id, COUNT(*) as issue_count')
            ->groupBy('book_id')
            ->orderByDesc('issue_count')
            ->limit(5)
            ->get();

        // Category-wise book distribution
        $categoryStats = BookCategory::withCount('books')->get();

        return view('library.dashboard', compact(
            'stats',
            'recentIssues',
            'overdueBooks',
            'topBooks',
            'categoryStats'
        ));
    }
}
