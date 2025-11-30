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
            'Fiction' => 'Fictional novels and stories',
            'Science' => 'Scientific books and journals',
            'History' => 'Historical records and biographies',
            'Technology' => 'Computer science and engineering',
            'Literature' => 'Classic and modern literature'
        ];

        foreach ($categories as $name => $desc) {
            BookCategory::firstOrCreate(['name' => $name], ['description' => $desc]);
        }

        // 2. Create Books
        $cats = BookCategory::all();
        
        $titles = ['The Great Gatsby', '1984', 'To Kill a Mockingbird', 'Pride and Prejudice', 'The Catcher in the Rye', 'The Hobbit', 'Fahrenheit 451', 'Brave New World', 'The Lord of the Rings', 'Animal Farm'];
        $authors = ['F. Scott Fitzgerald', 'George Orwell', 'Harper Lee', 'Jane Austen', 'J.D. Salinger', 'J.R.R. Tolkien', 'Ray Bradbury', 'Aldous Huxley', 'J.R.R. Tolkien', 'George Orwell'];
        $publishers = ['Penguin Books', 'HarperCollins', 'Simon & Schuster', 'Macmillan', 'Hachette'];

        if ($cats->isNotEmpty()) {
            foreach ($cats as $cat) {
                for ($i = 0; $i < 5; $i++) {
                    Book::create([
                        'title' => $titles[array_rand($titles)] . ' ' . rand(1, 100),
                        'author' => $authors[array_rand($authors)],
                        'category_id' => $cat->id,
                        'isbn' => '978-' . rand(1000000000, 9999999999),
                        'publisher' => $publishers[array_rand($publishers)],
                        'publication_year' => rand(1950, 2023),
                        'edition' => rand(1, 10) . 'th Edition',
                        'price' => rand(10, 100) + (rand(0, 99) / 100),
                        'pages' => rand(100, 1000),
                        'quantity' => 10,
                        'available_quantity' => 10, // Initially all available
                        'shelf_location' => 'Shelf ' . chr(rand(65, 90)) . rand(1, 10),
                        'added_date' => Carbon::now(),
                        'description' => 'A sample book description.'
                    ]);
                }
            }
        }

        // 3. Create Library Members will be done later if needed
        // 4. Create Book Issues will be done later if needed
    }
}
