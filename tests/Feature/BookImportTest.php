<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookCategory;
use App\Models\User;
use App\Services\BookImportService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

/**
 * Bulk book import.
 *
 * The library only had a one-book-at-a-time form, so cataloguing a real
 * collection meant hundreds of page loads. This covers the Excel template and
 * the importer built on the same column definition.
 *
 * The behaviour that matters:
 *
 *  1. The template is a real .xlsx with the expected headers, a worked example
 *     and an Instructions sheet, and it downloads.
 *
 *  2. A filled template round-trips into books.
 *
 *  3. Rows are validated individually. A bad row is reported with its row
 *     number and skipped; the good rows still import. Rejecting a whole file
 *     over one typo is not a useful failure for catalogue entry.
 *
 *  4. books.isbn is uniquely indexed, so a duplicate would abort the whole
 *     import with an exception. It is caught during validation instead, so the
 *     clash becomes a row-level message and the rest of the file still lands.
 *
 *  5. The template's example row is not imported as a real book.
 */
class BookImportTest extends TestCase
{
    use RefreshDatabase;

    /** @var User */
    private $admin;

    /** @var BookImportService */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::hasTable('roles')) {
            $this->seed(PermissionSeeder::class);
            $this->seed(RbacSeeder::class);
        }

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');

        $this->service = new BookImportService();
    }

    /** Headers of the template's first sheet, in order. */
    private function headers(): array
    {
        return array_keys(BookImportService::COLUMNS);
    }

    /**
     * Build a workbook from the template with the given data rows.
     * Row 2 stays as the template's example row unless $clearExample is false.
     */
    private function workbook(array $rows, bool $clearExample = true): Spreadsheet
    {
        $spreadsheet = $this->service->buildTemplate();
        $sheet = $spreadsheet->getSheetByName('Books');

        if ($clearExample) {
            $sheet->removeRow(2);
        }

        $rowNumber = 2;
        foreach ($rows as $row) {
            foreach (array_values($row) as $index => $value) {
                $sheet->setCellValue(
                    Coordinate::stringFromColumnIndex($index + 1) . $rowNumber,
                    $value
                );
            }
            $rowNumber++;
        }

        return $spreadsheet;
    }

    private function uploadWorkbook(Spreadsheet $spreadsheet, string $name = 'books.xlsx'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'bk') . '.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile($path, $name, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    /**
     * A complete, valid row, with any column overridden.
     *
     * array_merge (not `$overrides + $defaults`) is deliberate: the union
     * operator keeps the left array's key order, which would shuffle the
     * columns and write values into the wrong cells.
     */
    private function validRow(array $overrides = []): array
    {
        $row = [
            'Title' => 'A Valid Title',
            'Author' => 'A Writer',
            'ISBN' => '',
            'Category' => '',
            'Publisher' => '',
            'Publication Year' => '',
            'Edition' => '',
            'Price' => '',
            'Pages' => '',
            'Copies' => '1',
            'Condition' => '',
            'Shelf Location' => '',
            'Added Date' => '',
            'Description' => '',
        ];

        // Re-project through the canonical order so array_values() below always
        // lines up with the template's column order.
        $merged = array_merge($row, $overrides);

        $ordered = [];
        foreach (array_keys(BookImportService::COLUMNS) as $header) {
            $ordered[$header] = $merged[$header] ?? '';
        }

        return $ordered;
    }

    public function test_the_template_downloads_as_a_real_spreadsheet(): void
    {
        $response = $this->actingAs($this->admin)->get(route('books.import-template'));

        $response->assertOk();
        $response->assertHeader(
            'content-type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        $this->assertStringContainsString(
            BookImportService::TEMPLATE_FILENAME,
            $response->headers->get('content-disposition')
        );

        $workbook = $this->readDownloadedWorkbook($response->streamedContent());

        $this->assertSame(['Books', 'Instructions'], $workbook->getSheetNames());

        $sheet = $workbook->getSheetByName('Books');
        $header = $sheet->rangeToArray('A1:N1')[0];

        $this->assertSame($this->headers(), $header);

        // Title and Author are the required ones.
        foreach (['Title', 'Author'] as $required) {
            $this->assertTrue(
                BookImportService::COLUMNS[$required]['required'],
                "{$required} should be marked required."
            );
        }

        // The example row tells the user what a filled row looks like.
        $this->assertSame('The River Between', $sheet->getCell('A2')->getValue());

        $this->assertNotEmpty(
            trim((string) $workbook->getSheetByName('Instructions')->getCell('A1')->getValue())
        );
    }

    public function test_a_filled_template_imports(): void
    {
        BookCategory::create(['name' => 'Fiction']);

        $workbook = $this->workbook([
            $this->validRow([
                'Title' => 'Season of Migration to the North',
                'Author' => 'Tayeb Salih',
                'ISBN' => '978-0439055863',
                'Category' => 'Fiction',
                'Publisher' => 'Heinemann',
                'Publication Year' => '1965',
                'Price' => '450.00',
                'Pages' => '256',
                'Copies' => '3',
                'Condition' => 'fair',
                'Shelf Location' => 'A1-04',
            ]),
            $this->validRow([
                'Title' => 'Second Volume',
                'Author' => 'Another Writer',
                'Copies' => '2',
            ]),
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('books.import.store'), ['file' => $this->uploadWorkbook($workbook)]);

        $response->assertRedirect(route('books.import'));

        $result = session('import_result');
        $this->assertSame(2, $result['imported'], 'Reported problems: ' . json_encode($result['errors']));
        $this->assertSame([], $result['errors']);

        $book = Book::where('title', 'Season of Migration to the North')->firstOrFail();

        $this->assertSame('Tayeb Salih', $book->author);
        $this->assertSame('978-0439055863', $book->isbn);
        $this->assertSame('Heinemann', $book->publisher);
        $this->assertSame(1965, $book->publication_year);
        $this->assertSame('450.00', (string) $book->price);
        $this->assertSame(256, $book->pages);
        $this->assertSame(3, $book->quantity);
        // Every copy of a new title starts on the shelf.
        $this->assertSame(3, $book->available_quantity);
        $this->assertSame('fair', $book->condition);
        $this->assertSame('A1-04', $book->shelf_location);
        $this->assertSame('Fiction', optional($book->category)->name);
        $this->assertNotNull($book->added_date);

        // Blank optional values fall back to the documented defaults.
        $second = Book::where('title', 'Second Volume')->firstOrFail();
        $this->assertSame(2, $second->quantity);
        $this->assertSame('good', $second->condition);
        $this->assertNull($second->isbn);
    }

    public function test_a_bad_row_is_reported_and_the_good_rows_still_import(): void
    {
        $workbook = $this->workbook([
            $this->validRow(['Title' => 'Good One']),
            $this->validRow(['Title' => '', 'Author' => 'No Title Here']),
            $this->validRow(['Title' => 'Bad Year', 'Publication Year' => '19x4']),
            $this->validRow(['Title' => 'Bad Copies', 'Copies' => 'many']),
            $this->validRow(['Title' => 'Bad Date', 'Added Date' => '2026-02-30']),
            $this->validRow(['Title' => 'Unknown Category', 'Category' => 'Astrology']),
            $this->validRow(['Title' => 'Good Two']),
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('books.import.store'), ['file' => $this->uploadWorkbook($workbook)]);

        $result = session('import_result');

        $this->assertSame(2, $result['imported'], 'Both valid rows should import.');
        $this->assertSame(2, Book::count());

        $messages = collect($result['errors'])->pluck('message')->implode(' | ');

        $this->assertStringContainsString('"Title" is required.', $messages);
        $this->assertStringContainsString('four digit year', $messages);
        $this->assertStringContainsString('"Copies" must be a whole number', $messages);
        $this->assertStringContainsString('real date', $messages);
        $this->assertStringContainsString('"Category" "Astrology" does not exist', $messages);

        // Every error names the row it came from, so it can be found in Excel.
        $this->assertGreaterThanOrEqual(5, count($result['errors']));
        foreach ($result['errors'] as $error) {
            $this->assertGreaterThanOrEqual(1, $error['row']);
        }
    }

    public function test_duplicate_isbns_are_rejected_within_the_file_and_against_the_catalogue(): void
    {
        Book::create([
            'title' => 'Already Here', 'author' => 'Existing',
            'isbn' => '978-0000000001', 'quantity' => 1, 'available_quantity' => 1,
            'added_date' => now()->toDateString(),
        ]);

        $workbook = $this->workbook([
            $this->validRow(['Title' => 'New Book', 'ISBN' => '978-0000000002']),
            $this->validRow(['Title' => 'Clash In File', 'ISBN' => '978-0000000002']),
            // Case differences are still the same ISBN.
            $this->validRow(['Title' => 'Clash Case', 'ISBN' => '978-0000000002']),
            $this->validRow(['Title' => 'Clash Catalogue', 'ISBN' => '978-0000000001']),
        ]);

        $this->actingAs($this->admin)
            ->post(route('books.import.store'), ['file' => $this->uploadWorkbook($workbook)]);

        $result = session('import_result');

        $this->assertSame(1, $result['imported']);
        // Caught during validation, not left to the unique index to reject.
        $this->assertSame(2, Book::count(), 'Only the pre-existing book and New Book.');
        $this->assertNotNull(Book::where('title', 'New Book')->first());
        $this->assertSame(1, Book::where('isbn', '978-0000000002')->count());

        $messages = collect($result['errors'])->pluck('message')->implode(' | ');

        $this->assertStringContainsString('appears more than once in this file', $messages);
        $this->assertStringContainsString('is already in the catalogue', $messages);
    }

    public function test_the_templates_example_row_is_not_imported_as_a_book(): void
    {
        // clearExample = false leaves the template's own example row in place,
        // which is what happens when someone fills the sheet without deleting it.
        $workbook = $this->workbook([
            $this->validRow(['Title' => 'Real Book']),
        ], false);

        $this->actingAs($this->admin)
            ->post(route('books.import.store'), ['file' => $this->uploadWorkbook($workbook)]);

        $result = session('import_result');

        $this->assertSame(1, $result['imported']);
        $this->assertNull(Book::where('title', 'The River Between')->first());
        $this->assertNotNull(Book::where('title', 'Real Book')->first());
    }

    public function test_a_real_book_that_shares_the_examples_title_is_still_imported(): void
    {
        // The example guard matches every column, not just the title, so a
        // genuine book called "The River Between" is not silently discarded.
        $workbook = $this->workbook([
            $this->validRow([
                'Title' => BookImportService::EXAMPLE_ROW[0],
                'Author' => 'A Different Author',
                'Copies' => '1',
            ]),
        ], false);

        $this->actingAs($this->admin)
            ->post(route('books.import.store'), ['file' => $this->uploadWorkbook($workbook)]);

        $result = session('import_result');

        $this->assertSame(1, $result['imported']);
        $this->assertSame(
            'A Different Author',
            Book::where('title', 'The River Between')->firstOrFail()->author
        );
    }

    public function test_a_missing_or_mismatched_header_is_reported(): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Name Of Book');
        $sheet->setCellValue('B1', 'Writer');
        $sheet->setCellValue('A2', 'Confused Title');
        $sheet->setCellValue('B2', 'Confused Author');

        $this->withoutExceptionHandling()
            ->actingAs($this->admin)
            ->post(route('books.import.store'), ['file' => $this->uploadWorkbook($spreadsheet, 'wrong.xlsx')]);

        $result = session('import_result');
        $messages = collect($result['errors'])->pluck('message')->implode(' | ');

        $this->assertSame(0, $result['imported']);
        $this->assertStringContainsString('missing required column', $messages);
        $this->assertStringContainsString('Unknown column "Name Of Book"', $messages);
    }

    public function test_a_non_spreadsheet_upload_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->post(route('books.import.store'), [
                'file' => UploadedFile::fake()->create('notes.txt', 4, 'text/plain'),
            ])
            ->assertSessionHasErrors('file');

        $this->assertSame(0, Book::count());
    }

    public function test_the_import_screen_offers_the_template_and_explains_the_columns(): void
    {
        BookCategory::create(['name' => 'Fiction']);

        $response = $this->actingAs($this->admin)->get(route('books.import'));

        $response->assertOk();
        $response->assertSee('Import Books');
        $response->assertSee('Download Excel Template');
        $response->assertSee(route('books.import-template'), false);
        $response->assertSee('Fiction');
        $response->assertSee('accept=".xlsx,.csv"', false);
    }

    public function test_importing_requires_the_manage_permission(): void
    {
        // Teacher has library.view but not library.manage, so it is the role
        // that can browse the catalogue but must not change it.
        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');

        $this->actingAs($teacher)
            ->get(route('books.import'))
            ->assertForbidden();

        $this->actingAs($teacher)
            ->get(route('books.import-template'))
            ->assertForbidden();

        $this->actingAs($teacher)
            ->post(route('books.import.store'), [
                'file' => UploadedFile::fake()->createWithContent(
                    'books.csv',
                    "Title,Author\nA Title,A Writer\n"
                ),
            ])
            ->assertForbidden();

        $this->assertSame(0, Book::count());
    }

    private function readDownloadedWorkbook(string $binary): Spreadsheet
    {
        $path = tempnam(sys_get_temp_dir(), 'tpl') . '.xlsx';
        file_put_contents($path, $binary);

        return (new \PhpOffice\PhpSpreadsheet\Reader\Xlsx())->load($path);
    }
}
