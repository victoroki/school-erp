<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BookCategory;
use App\Models\Book;
use App\Models\BookIssue;
use App\Models\LibraryMember;
use App\Models\Student;
use Carbon\Carbon;

class LibrarySeeder extends Seeder
{
    public function run()
    {
        // 1. Book categories (Kenyan school library)
        $categories = [
            ['name' => 'Set Books & Literature', 'description' => 'KCSE/CBC set books, novels and plays set by KICD'],
            ['name' => 'CBC Readers (Lower Primary)', 'description' => 'Graded readers for Pre-primary to Grade 3 learners'],
            ['name' => 'CBC Readers (Upper Primary)', 'description' => 'Graded readers for Grade 4 to Grade 6 learners'],
            ['name' => 'Mathematics', 'description' => 'Mathematics textbooks and revision materials'],
            ['name' => 'Sciences', 'description' => 'Biology, Chemistry, Physics and Integrated Science'],
            ['name' => 'Languages', 'description' => 'English, Kiswahili and other language resources'],
            ['name' => 'Humanities', 'description' => 'History, Geography, CRE, IRE and Social Studies'],
        ];

        foreach ($categories as $cat) {
            BookCategory::updateOrCreate(['name' => $cat['name']], ['description' => $cat['description']]);
        }

        // 2. Books (Kenyan titles / KES prices / stable unique ISBN)
        $books = [
            ['title' => 'The River Between', 'author' => 'Ngugi wa Thiong\'o', 'category' => 'Set Books & Literature', 'isbn' => '978-0439055863', 'publisher' => 'Heinemann', 'year' => 1965, 'price' => 1200, 'pages' => 228, 'quantity' => 12, 'condition' => 'Good'],
            ['title' => 'Weep Not, Child', 'author' => 'Ngugi wa Thiong\'o', 'category' => 'Set Books & Literature', 'isbn' => '978-0439055876', 'publisher' => 'Heinemann', 'year' => 1964, 'price' => 1100, 'pages' => 160, 'quantity' => 12, 'condition' => 'Good'],
            ['title' => 'A Grain of Wheat', 'author' => 'Ngugi wa Thiong\'o', 'category' => 'Set Books & Literature', 'isbn' => '978-0439055882', 'publisher' => 'Heinemann', 'year' => 1967, 'price' => 1450, 'pages' => 280, 'quantity' => 10, 'condition' => 'Excellent'],
            ['title' => 'Betrayal in the City', 'author' => 'Francis Imbuga', 'category' => 'Set Books & Literature', 'isbn' => '978-0457005863', 'publisher' => 'Heinemann', 'year' => 1976, 'price' => 950, 'pages' => 120, 'quantity' => 14, 'condition' => 'Good'],
            ['title' => 'The Promised Land', 'author' => 'Grace Ogot', 'category' => 'Set Books & Literature', 'isbn' => '978-0457005864', 'publisher' => 'East African Publishing House', 'year' => 1966, 'price' => 1050, 'pages' => 224, 'quantity' => 9, 'condition' => 'Good'],
            ['title' => 'Kill Me Quick', 'author' => 'Meja Mwangi', 'category' => 'Set Books & Literature', 'isbn' => '978-0457005865', 'publisher' => 'Heinemann', 'year' => 1973, 'price' => 1150, 'pages' => 224, 'quantity' => 11, 'condition' => 'Fair'],
            ['title' => 'Going Down River Road', 'author' => 'Meja Mwangi', 'category' => 'Set Books & Literature', 'isbn' => '978-0457005866', 'publisher' => 'Heinemann', 'year' => 1976, 'price' => 1250, 'pages' => 208, 'quantity' => 8, 'condition' => 'Good'],
            ['title' => 'Secondary Mathematics Form 1', 'author' => 'KICD', 'category' => 'Mathematics', 'isbn' => '978-9966005867', 'publisher' => 'Kenya Literature Bureau', 'year' => 2019, 'price' => 850, 'pages' => 320, 'quantity' => 40, 'condition' => 'Excellent'],
            ['title' => 'KCSE Mathematics KCS Revision', 'author' => 'Longhorn Publishers', 'category' => 'Mathematics', 'isbn' => '978-9966005868', 'publisher' => 'Longhorn', 'year' => 2020, 'price' => 1500, 'pages' => 480, 'quantity' => 25, 'condition' => 'Good'],
            ['title' => 'Understanding Chemistry for KCSE', 'author' => 'Moran Publishers', 'category' => 'Sciences', 'isbn' => '978-9966005869', 'publisher' => 'Moran', 'year' => 2021, 'price' => 1650, 'pages' => 400, 'quantity' => 22, 'condition' => 'Good'],
            ['title' => 'New Integrated Science Primary 5', 'author' => 'Phoenix Publishers', 'category' => 'Sciences', 'isbn' => '978-9966005870', 'publisher' => 'Phoenix', 'year' => 2019, 'price' => 650, 'pages' => 180, 'quantity' => 35, 'condition' => 'Excellent'],
            ['title' => 'Mazoezi ya Kiswahili Elimu ya Msingi', 'author' => 'Kenya Institute of Curriculum Development', 'category' => 'Languages', 'isbn' => '978-9966005871', 'publisher' => 'KICD', 'year' => 2018, 'price' => 580, 'pages' => 160, 'quantity' => 40, 'condition' => 'Good'],
            ['title' => 'English Activities Grade 4', 'author' => 'KLB', 'category' => 'Languages', 'isbn' => '978-9966005872', 'publisher' => 'Kenya Literature Bureau', 'year' => 2020, 'price' => 620, 'pages' => 176, 'quantity' => 38, 'condition' => 'Excellent'],
            ['title' => 'History & Government Form 4', 'author' => 'KLB', 'category' => 'Humanities', 'isbn' => '978-9966005873', 'publisher' => 'Kenya Literature Bureau', 'year' => 2020, 'price' => 1180, 'pages' => 350, 'quantity' => 20, 'condition' => 'Good'],
            ['title' => 'Geography for Secondary Schools', 'author' => 'Longhorn Publishers', 'category' => 'Humanities', 'isbn' => '978-9966005874', 'publisher' => 'Longhorn', 'year' => 2019, 'price' => 1350, 'pages' => 390, 'quantity' => 24, 'condition' => 'Good'],
            ['title' => 'PP2 Reading Series: Safari ya Twiga', 'author' => 'Storymoja', 'category' => 'CBC Readers (Lower Primary)', 'isbn' => '978-9966005875', 'publisher' => 'Storymoja', 'year' => 2022, 'price' => 320, 'pages' => 48, 'quantity' => 50, 'condition' => 'Excellent'],
        ];

        $cats = BookCategory::all();

        foreach ($books as $i => $book) {
            $category = $cats->firstWhere('name', $book['category']);
            if (!$category) {
                continue;
            }

            Book::firstOrCreate(
                ['isbn' => $book['isbn']],
                [
                    'title' => $book['title'],
                    'author' => $book['author'],
                    'category_id' => $category->category_id,
                    'publisher' => $book['publisher'],
                    'publication_year' => $book['year'],
                    'price' => $book['price'],
                    'pages' => $book['pages'],
                    'quantity' => $book['quantity'],
                    'available_quantity' => $book['quantity'],
                    'shelf_location' => 'Library Row ' . chr(65 + ($book['year'] % 6)) . '-' . (($book['quantity'] % 10) + 1),
                    'added_date' => Carbon::now()->subMonths(2),
                    'description' => ($book['category'] . ' resource for ' . $book['author']),
                    'condition' => $book['condition'],
                    'barcode' => 'BC' . str_pad((string)($i + 1), 7, '0', STR_PAD_LEFT),
                ]
            );
        }

        // 3. Create library members for students (no dependency on users table)
        //    Keyed on reference_id so re-seeding never duplicates a member.
        $students = Student::where('status', 'active')->get();
        foreach ($students as $student) {
            LibraryMember::firstOrCreate(
                ['member_type' => 'student', 'reference_id' => (string) $student->student_id],
                [
                    'membership_date' => $student->admission_date ?: Carbon::now()->subMonths(2),
                    'membership_expiry_date' => Carbon::now()->addYear(),
                    'max_allowed_books' => 5,
                    'status' => 'active',
                ]
            );
        }

        // 4. Issue a handful of books to the first members
        $members = LibraryMember::all();
        $booksForIssue = Book::limit(6)->get();
        $issuedCount = min($members->count(), $booksForIssue->count());

        for ($i = 0; $i < $issuedCount; $i++) {
            $member = $members->get($i);
            $book = $booksForIssue->get($i);
            if (!$member || !$book) {
                continue;
            }

            BookIssue::firstOrCreate(
                ['book_id' => $book->book_id, 'member_id' => $member->member_id, 'issue_date' => Carbon::now()->subDays(3)->toDateString()],
                [
                    'due_date' => Carbon::now()->addDays(11)->toDateString(),
                    'status' => 'issued',
                    'remarks' => 'Issued for CBC reading programme',
                ]
            );
        }
    }
}
