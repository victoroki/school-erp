<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BookCategory;
use App\Models\Book;
use App\Models\BookIssue;
use App\Models\LibraryMember;
use App\Models\User;
use Carbon\Carbon;

class LibrarySeeder extends Seeder
{
    public function run()
    {
        // 1. Create Book Categories
        $categories = [
            ['name' => 'Fiction', 'description' => 'Fictional novels and stories'],
            ['name' => 'Science & Tech', 'description' => 'Scientific books, physics, chemistry and computer science'],
            ['name' => 'History', 'description' => 'Historical records, world wars and biographies'],
            ['name' => 'Mathematics', 'description' => 'Pure and applied mathematics'],
            ['name' => 'Literature', 'description' => 'Classic and modern literature'],
            ['name' => 'General Knowledge', 'description' => 'Encyclopedia and trivia']
        ];

        foreach ($categories as $cat) {
            BookCategory::updateOrCreate(['name' => $cat['name']], ['description' => $cat['description']]);
        }

        // 2. Create Books with covers
        $cats = BookCategory::all();
        $covers = [
            'https://images-na.ssl-images-amazon.com/images/I/81af+S9v82L.jpg',
            'https://images-na.ssl-images-amazon.com/images/I/41-G6p7SltL._SX331_BO1,204,203,200_.jpg',
            'https://images-na.ssl-images-amazon.com/images/I/91S+P9i2V-L.jpg',
            'https://images-na.ssl-images-amazon.com/images/I/51Zu0H6S7IL._SX322_BO1,204,203,200_.jpg',
            'https://images-na.ssl-images-amazon.com/images/I/71kxa1-qmfL.jpg',
            'https://m.media-amazon.com/images/I/51rJ6zW92sL._AC_UF1000,1000_QL80_.jpg',
            'https://m.media-amazon.com/images/I/61N6K6W+7tL._AC_UF1000,1000_QL80_.jpg'
        ];

        $titles = ['The Great Gatsby', '1984', 'To Kill a Mockingbird', 'The Catcher in the Rye', 'The Hobbit', 'Fahrenheit 451', 'Brave New World', 'Lord of the Flies', 'Animal Farm', 'The Alchemist'];
        $authors = ['F. Scott Fitzgerald', 'George Orwell', 'Harper Lee', 'J.D. Salinger', 'J.R.R. Tolkien', 'Ray Bradbury', 'Aldous Huxley', 'William Golding', 'George Orwell', 'Paulo Coelho'];

        foreach ($cats as $cat) {
            for ($i = 0; $i < 3; $i++) {
                $q = rand(5, 15);
                Book::create([
                    'title' => $titles[array_rand($titles)] . ' - Vol ' . ($i+1),
                    'author' => $authors[array_rand($authors)],
                    'category_id' => $cat->category_id,
                    'isbn' => rand(100, 999) . '-' . rand(10, 99) . '-' . rand(10000, 99999),
                    'publisher' => 'Global Publishing ' . rand(1, 5),
                    'publication_year' => rand(2010, 2024),
                    'price' => rand(500, 2500),
                    'pages' => rand(200, 600),
                    'quantity' => $q,
                    'available_quantity' => $q,
                    'shelf_location' => 'Sec-' . chr(rand(65, 70)) . '-' . rand(1, 10),
                    'added_date' => Carbon::now(),
                    'condition' => 'good',
                    'cover_url' => $covers[array_rand($covers)],
                    'barcode' => 'BC' . rand(1000000, 9999999),
                    'description' => 'This is a premium high-quality book suitable for academic research and leisure reading.'
                ]);
            }
        }

        // 3. Create Library Members (link to existing users)
        $users = User::limit(5)->get();
        foreach ($users as $user) {
            LibraryMember::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'member_type' => 'student',
                    'reference_id' => rand(1000, 9999),
                    'membership_date' => Carbon::now()->subMonths(2),
                    'membership_expiry_date' => Carbon::now()->addYear(),
                    'max_allowed_books' => 5,
                    'status' => 'active'
                ]
            );
        }

        // 4. Create some active issues
        $members = LibraryMember::all();
        $books = Book::limit(5)->get();

        if ($members->isNotEmpty() && $books->isNotEmpty()) {
            foreach ($members as $index => $member) {
                 if(isset($books[$index])) {
                    $book = $books[$index];
                    BookIssue::create([
                        'book_id' => $book->book_id,
                        'member_id' => $member->member_id,
                        'issue_date' => Carbon::now()->subDays(rand(1, 10)),
                        'due_date' => Carbon::now()->addDays(rand(1, 7)),
                        'status' => 'issued',
                        'issued_by' => 1
                    ]);
                    $book->decrement('available_quantity');
                 }
            }
        }
    }
}
