<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookCategory;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Bulk book import from a spreadsheet.
 *
 * The library had no way in except one form at a time, so cataloguing a few
 * hundred titles meant a few hundred page loads. This reads the same columns
 * the create form uses and reports problems per row.
 *
 * Rows are validated individually: a bad row is reported and skipped while the
 * rest of the file still imports. Rejecting a 300-row file because of one
 * typo is not a useful failure mode for a catalogue entry task.
 */
class BookImportService
{
    public const TEMPLATE_FILENAME = 'books-import-template.xlsx';

    /**
     * The template's columns, in order. This is the single source of truth for
     * the template, the header check and the importer, so the three can never
     * drift apart.
     */
    public const COLUMNS = [
        'Title' => ['required' => true, 'max' => 255],
        'Author' => ['required' => true, 'max' => 255],
        'ISBN' => ['required' => false, 'max' => 20],
        'Category' => ['required' => false],
        'Publisher' => ['required' => false, 'max' => 100],
        'Publication Year' => ['required' => false, 'type' => 'year'],
        'Edition' => ['required' => false, 'max' => 50],
        'Price' => ['required' => false, 'type' => 'decimal'],
        'Pages' => ['required' => false, 'type' => 'integer'],
        'Copies' => ['required' => false, 'type' => 'integer', 'default' => 1],
        'Condition' => ['required' => false, 'type' => 'enum', 'options' => ['good', 'fair', 'poor']],
        'Shelf Location' => ['required' => false, 'max' => 50],
        'Added Date' => ['required' => false, 'type' => 'date'],
        'Description' => ['required' => false],
    ];

    /** Guards against a runaway sheet exhausting memory or flooding the page. */
    private const MAX_ROWS = 2000;

    /**
     * The worked example shipped in row 2 of the template.
     *
     * Kept as a constant because the importer has to recognise it. It is
     * matched on every column, not just the title: someone who forgets to
     * delete the example would otherwise publish a real catalogue entry, and
     * matching on the title alone would also swallow a genuine book that
     * happens to share it.
     */
    public const EXAMPLE_ROW = [
        'The River Between',
        'Ngugi wa Thiong\'o',
        '978-0439055863',
        'Fiction',
        'Heinemann',
        '1965',
        '1st',
        '450.00',
        '256',
        '3',
        'good',
        'A1-04',
        '2026-01-15',
        'A novel about a community along a river.',
    ];

    /**
     * Build the downloadable template.
     */
    public function buildTemplate(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Books');

        $headers = array_keys(self::COLUMNS);
        $lastColumn = Coordinate::stringFromColumnIndex(count($headers));

        foreach ($headers as $index => $header) {
            $coordinate = Coordinate::stringFromColumnIndex($index + 1) . '1';

            // TYPE_STRING keeps a header like "Publication Year" from being
            // guessed at, and stops anything starting with = becoming a formula.
            $sheet->setCellValueExplicit($coordinate, $header, DataType::TYPE_STRING);

            $sheet->getStyle($coordinate)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle($coordinate)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('2F5496');
            $sheet->getStyle($coordinate)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // One worked example, so the expected shape is obvious without
        // opening the instructions sheet.
        foreach ($headers as $index => $header) {
            $value = self::EXAMPLE_ROW[$index] ?? '';

            $coordinate = Coordinate::stringFromColumnIndex($index + 1) . '2';
            $sheet->setCellValueExplicit($coordinate, $value, DataType::TYPE_STRING);
            $sheet->getStyle($coordinate)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('F2F2F2');
        }

        $sheet->getStyle('A1:' . $lastColumn . '1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('2F5496');
        $sheet->getStyle('A1:' . $lastColumn . '1')->getFont()
            ->setBold(true)->getColor()->setRGB('FFFFFF');

        foreach (['Title', 'Author'] as $requiredHeader) {
            $column = array_search($requiredHeader, $headers, true);
            $letter = Coordinate::stringFromColumnIndex($column + 1);
            $sheet->getStyle($letter . '1')->getFont()->getColor()->setRGB('FFD966');
        }

        $sheet->freezePane('A2');

        $widths = [
            'A' => 34, 'B' => 24, 'C' => 18, 'D' => 18, 'E' => 20, 'F' => 16,
            'G' => 12, 'H' => 12, 'I' => 10, 'J' => 10, 'K' => 12, 'L' => 16,
            'M' => 14, 'N' => 40,
        ];
        foreach ($widths as $letter => $width) {
            $sheet->getColumnDimension($letter)->setAutoSize(false);
            $sheet->getColumnDimension($letter)->setWidth($width);
        }

        // Constrain Condition to the values the database accepts, so a typo
        // becomes a visible Excel error instead of a row-level import failure.
        $conditionColumn = array_search('Condition', $headers, true);
        $validation = new DataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setShowErrorMessage(true);
        $validation->setShowInputMessage(true);
        $validation->setErrorTitle('Invalid condition');
        $validation->setError('Use good, fair or poor.');
        $validation->setPromptTitle('Condition');
        $validation->setPrompt('good, fair or poor');
        $validation->setFormula1('"' . implode(',', self::COLUMNS['Condition']['options']) . '"');
        $conditionLetter = Coordinate::stringFromColumnIndex($conditionColumn + 1);
        $sheet->setDataValidation(
            $conditionLetter . '3:' . $conditionLetter . (self::MAX_ROWS + 1),
            $validation
        );

        $spreadsheet->createSheet()->setTitle('Instructions');
        $instructions = $spreadsheet->getSheetByName('Instructions');
        $instructions->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $instructions->setCellValue('A1', 'How to import books');

        $rows = [
            '',
            'Fill in the "Books" sheet, starting at row 3. Row 2 is a worked example — delete it or overwrite it.',
            'Do not rename, reorder or remove the header row. Columns are matched by name, so order does not matter.',
            'Delete any extra sheets before uploading.',
            '',
            'Required fields',
            '  Title   - the book title. Required.',
            '  Author  - the author. Required.',
            '',
            'Optional fields',
            '  ISBN              - up to 20 characters. Must be unique across the sheet and the catalogue.',
            '  Category          - must match an existing category name exactly (case-insensitive).',
            '  Publication Year  - a four digit year, e.g. 1998.',
            '  Price             - a number, e.g. 450 or 450.00.',
            '  Pages             - a whole number.',
            '  Copies            - how many physical copies. Defaults to 1 when left blank.',
            '  Condition         - good, fair or poor. Defaults to good.',
            '  Added Date        - YYYY-MM-DD. Defaults to today.',
            '',
            'How errors are handled',
            '  Each row is checked on its own. A row with a problem is skipped and listed on the',
            '  results screen with the reason; every other row is still imported. Re-upload the file',
            '  after fixing only the rows that were reported.',
            '',
            'Limits',
            '  Up to ' . self::MAX_ROWS . ' books per file. Split larger catalogues across several files.',
        ];

        $line = 2;
        foreach ($rows as $text) {
            $instructions->setCellValueExplicit('A' . $line, $text, DataType::TYPE_STRING);
            $line++;
        }

        $instructions->getColumnDimension('A')->setWidth(96);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * Import a workbook.
     *
     * @return array{imported: int, errors: array<int, array{row: int, message: string}>, total: int}
     */
    public function import(Spreadsheet $spreadsheet): array
    {
        $sheet = $spreadsheet->getActiveSheet();
        $errors = [];
        $imported = 0;
        $total = 0;

        // The getHighestData* accessors read the live cell collection, whereas
        // getHighestRow()/getHighestColumn() return a cached dimension. The
        // cached value under-reports a sheet that was modified after it was
        // built — as the template is when its example row is removed — and
        // rows at the end of the file are then silently dropped.
        $highestColumn = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());
        $highestDataRow = $sheet->getHighestDataRow();

        // Map column letter => canonical header name.
        $headerMap = [];
        for ($i = 1; $i <= $highestColumn; $i++) {
            $header = trim((string) $sheet->getCell(Coordinate::stringFromColumnIndex($i) . '1')->getFormattedValue());
            if ($header === '') { continue; }

            $canonical = $this->matchHeader($header);
            if ($canonical === null) {
                $errors[] = ['row' => 1, 'message' => "Unknown column \"{$header}\" in the header row. Download the template to see the expected columns."];
                continue;
            }
            $headerMap[Coordinate::stringFromColumnIndex($i)] = $canonical;
        }

        $missing = array_diff(['Title', 'Author'], $headerMap);
        if ($missing) {
            $errors[] = ['row' => 1, 'message' => 'The header row is missing required column(s): ' . implode(', ', $missing) . '.'];
            return ['imported' => 0, 'errors' => $errors, 'total' => 0];
        }

        $categories = BookCategory::pluck('category_id', 'name')
            ->mapWithKeys(fn ($id, $name) => [mb_strtolower(trim($name)) => $id])
            ->all();

        $existingIsbns = Book::whereNotNull('isbn')
            ->where('isbn', '<>', '')
            ->pluck('isbn')
            ->map(fn ($isbn) => mb_strtolower(trim($isbn)))
            ->flip()
            ->all();

        $lastRow = min($highestDataRow, self::MAX_ROWS + 1);
        $seenIsbns = [];

        for ($row = 2; $row <= $lastRow; $row++) {
            $record = $this->readRow($sheet, $row, $headerMap);

            // Ignore the template's example row and any trailing blanks.
            if ($this->isBlank($record)) { continue; }
            if ($this->isExampleRow($record)) { continue; }

            $total++;
            $rowErrors = $this->validate($record, $categories, $existingIsbns, $seenIsbns);

            if ($rowErrors) {
                foreach ($rowErrors as $message) {
                    $errors[] = ['row' => $row, 'message' => $message];
                }
                continue;
            }

            Book::create($this->toBookAttributes($record, $categories));
            $imported++;
        }

        if ($highestDataRow > self::MAX_ROWS + 1) {
            $errors[] = [
                'row' => self::MAX_ROWS + 2,
                'message' => 'Only the first ' . self::MAX_ROWS . ' rows were read. Split the file and upload the rest separately.',
            ];
        }

        return ['imported' => $imported, 'errors' => $errors, 'total' => $total];
    }

    private function matchHeader(string $header): ?string
    {
        foreach (array_keys(self::COLUMNS) as $candidate) {
            if (mb_strtolower($candidate) === mb_strtolower($header)) {
                return $candidate;
            }
        }

        return null;
    }

    private function readRow(Worksheet $sheet, int $row, array $headerMap): array
    {
        $record = [];

        foreach ($headerMap as $letter => $canonical) {
            $value = $sheet->getCell($letter . $row)->getFormattedValue();
            $record[$canonical] = is_string($value) ? trim($value) : $value;
        }

        return $record;
    }

    private function isBlank(array $record): bool
    {
        foreach ($record as $value) {
            if ($value !== null && $value !== '') { return false; }
        }

        return true;
    }

    /**
     * True when a row is still the template's untouched example.
     *
     * Every column has to match. Comparing only the title would discard a real
     * book that shares it; comparing all of them is enough to catch the case
     * this exists for — the example was never edited, so it was never meant to
     * be imported.
     */
    private function isExampleRow(array $record): bool
    {
        $expected = array_combine(array_keys(self::COLUMNS), self::EXAMPLE_ROW);

        if ($expected === false) { return false; }

        foreach ($expected as $header => $value) {
            $actual = $record[$header] ?? null;
            if ((string) $actual !== (string) $value) { return false; }
        }

        return true;
    }

    private function validate(array $record, $categories, array $existingIsbns, array &$seenIsbns): array
    {
        $errors = [];

        foreach (self::COLUMNS as $header => $meta) {
            $value = $record[$header] ?? null;

            if (($meta['required'] ?? false) && ($value === null || $value === '')) {
                $errors[] = "\"{$header}\" is required.";
                continue;
            }

            if ($value === null || $value === '') { continue; }

            if (isset($meta['max']) && is_string($value) && mb_strlen($value) > $meta['max']) {
                $errors[] = "\"{$header}\" is longer than {$meta['max']} characters.";
            }

            switch ($meta['type'] ?? null) {
                case 'year':
                    if (! preg_match('/^\d{4}$/', (string) $value) || (int) $value < 1000 || (int) $value > (int) date('Y') + 1) {
                        $errors[] = "\"{$header}\" must be a four digit year.";
                    }
                    break;

                case 'integer':
                    if (! preg_match('/^\d+$/', (string) $value)) {
                        $errors[] = "\"{$header}\" must be a whole number.";
                    } elseif ((int) $value < 0) {
                        $errors[] = "\"{$header}\" cannot be negative.";
                    }
                    break;

                case 'decimal':
                    if (! is_numeric($value) || (float) $value < 0) {
                        $errors[] = "\"{$header}\" must be a positive number.";
                    }
                    break;

                case 'date':
                    if (! $this->isRealDate((string) $value)) {
                        $errors[] = "\"{$header}\" must be a real date in YYYY-MM-DD format.";
                    }
                    break;

                case 'enum':
                    if (! in_array(mb_strtolower((string) $value), $meta['options'], true)) {
                        $errors[] = "\"{$header}\" must be one of: " . implode(', ', $meta['options']) . '.';
                    }
                    break;
            }
        }

        $category = $record['Category'] ?? null;
        if ($category !== null && $category !== '' && ! isset($categories[mb_strtolower(trim($category))])) {
            $errors[] = "\"Category\" \"{$category}\" does not exist. Create it first, or leave the column blank.";
        }

        $isbn = $record['ISBN'] ?? null;
        if ($isbn !== null && $isbn !== '') {
            $key = mb_strtolower($isbn);

            // books.isbn is uniquely indexed, so a clash would abort the whole
            // import with a UniqueConstraintViolationException. Checking first
            // turns it into a normal row-level message and keeps the rest of the
            // file going.
            if (isset($seenIsbns[$key])) {
                $errors[] = "\"ISBN\" {$isbn} appears more than once in this file.";
            } else {
                $seenIsbns[$key] = true;
            }

            if (isset($existingIsbns[$key])) {
                $errors[] = "\"ISBN\" {$isbn} is already in the catalogue.";
            }
        }

        return $errors;
    }

    private function isRealDate(string $value): bool
    {
        $date = Carbon::createFromFormat('Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value;
    }

    private function toBookAttributes(array $record, $categories): array
    {
        $category = $record['Category'] ?? null;
        $copies = ($record['Copies'] ?? '') === '' ? 1 : (int) $record['Copies'];
        $condition = ($record['Condition'] ?? '') === '' ? 'good' : mb_strtolower($record['Condition']);

        return [
            'title' => $record['Title'],
            'author' => $record['Author'],
            'isbn' => ($record['ISBN'] ?? '') === '' ? null : $record['ISBN'],
            'category_id' => ($category === null || $category === '')
                ? null
                : $categories[mb_strtolower(trim($category))],
            'publisher' => $this->nullable($record['Publisher'] ?? null),
            'publication_year' => ($record['Publication Year'] ?? '') === '' ? null : (int) $record['Publication Year'],
            'edition' => $this->nullable($record['Edition'] ?? null),
            'price' => ($record['Price'] ?? '') === '' ? null : (float) $record['Price'],
            'pages' => ($record['Pages'] ?? '') === '' ? null : (int) $record['Pages'],
            'condition' => $condition,
            'quantity' => $copies,
            // A newly catalogued title has every copy on the shelf.
            'available_quantity' => $copies,
            'shelf_location' => $this->nullable($record['Shelf Location'] ?? null),
            'added_date' => ($record['Added Date'] ?? '') === ''
                ? Carbon::now()->toDateString()
                : $record['Added Date'],
            'description' => $this->nullable($record['Description'] ?? null),
        ];
    }

    private function nullable(?string $value): ?string
    {
        return ($value === null || $value === '') ? null : $value;
    }
}
