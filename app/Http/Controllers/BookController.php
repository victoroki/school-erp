<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\BookCategory;
use App\Models\AuditTrail;
use App\Repositories\BookRepository;
use App\Services\BookImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use Flash;

class BookController extends AppBaseController
{
    /** @var BookRepository $bookRepository*/
    private $bookRepository;

    /** @var BookImportService $bookImportService */
    private $bookImportService;

    public function __construct(BookRepository $bookRepo, BookImportService $bookImportService)
    {
        $this->bookRepository = $bookRepo;
        $this->bookImportService = $bookImportService;
        $this->middleware('can:library.view')->only(['index', 'show']);
        $this->middleware('can:library.manage')->only(['create', 'store', 'edit', 'update', 'destroy', 'importForm', 'import', 'importTemplate']);
    }

    private function getDropdownData(){
        return[
            'bkCategory' => BookCategory::pluck('name',  'category_id')
        ];
    }

    /**
     * Display a listing of the Book.
     */
    /**
     * Display a listing of the Book.
     */
    public function index(Request $request)
    {
        $query = $this->bookRepository->allQuery()->with('category');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('author', 'like', "%$search%")
                  ->orWhere('isbn', 'like', "%$search%");
            });
        }

        if ($request->has('category_id') && $request->category_id != '') {
            $query->where('category_id', $request->category_id);
        }
        
        if ($request->has('availability') && $request->availability != '') {
            if ($request->availability == 'available') {
                $query->where('available_quantity', '>', 0);
            } elseif ($request->availability == 'out_of_stock') {
                $query->where('available_quantity', '<=', 0);
            }
        }

        $books = $query->paginate(12)->withQueryString();
        
        $categories = BookCategory::pluck('name', 'category_id')->prepend('All Categories', '');

        return view('books.index')
            ->with('books', $books)
            ->with('categories', $categories);
    }

    /**
     * Show the form for creating a new Book.
     */
    public function create()
    {
        $dropdowndata = $this->getDropdownData();
        return view('books.create', $dropdowndata);
    }

    /**
     * Store a newly created Book in storage.
     */
    public function store(CreateBookRequest $request)
    {
        $input = $request->all();

        $book = $this->bookRepository->create($input);

        AuditTrail::log('Book', 'CREATE', $book->book_id, null, $book->toArray());

        Flash::success('Book saved successfully.');

        return redirect(route('books.index'));
    }

    /**
     * Display the specified Book.
     */
    public function show($id)
    {
        $book = \App\Models\Book::with('category')->find($id);

        if (empty($book)) {
            Flash::error('Book not found');

            return redirect(route('books.index'));
        }

        return view('books.show')->with('book', $book);
    }

    /**
     * Show the form for editing the specified Book.
     */
    public function edit($id)
    {
        $book = $this->bookRepository->find($id);
        $dropdowndata = $this->getDropdownData();

        if (empty($book)) {
            Flash::error('Book not found');

            return redirect(route('books.index'));
        }

        return view('books.edit', array_merge(['book' => $book], $dropdowndata));
    }

    /**
     * Update the specified Book in storage.
     */
    public function update($id, UpdateBookRequest $request)
    {
        $book = $this->bookRepository->find($id);

        if (empty($book)) {
            Flash::error('Book not found');

            return redirect(route('books.index'));
        }

        $oldData = $book->toArray();
        $book = $this->bookRepository->update($request->all(), $id);

        AuditTrail::log('Book', 'UPDATE', $book->book_id, $oldData, $book->toArray());

        Flash::success('Book updated successfully.');

        return redirect(route('books.index'));
    }

    /**
     * Remove the specified Book from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $book = $this->bookRepository->find($id);

        if (empty($book)) {
            Flash::error('Book not found');

            return redirect(route('books.index'));
        }

        $oldData = $book->toArray();
        $this->bookRepository->delete($id);

        AuditTrail::log('Book', 'DELETE', $id, $oldData, null);

        Flash::success('Book deleted successfully.');

        return redirect(route('books.index'));
    }

    /**
     * Show the bulk import screen.
     */
    public function importForm()
    {
        return view('books.import', [
            'categories' => BookCategory::orderBy('name')->pluck('name'),
            'templateName' => BookImportService::TEMPLATE_FILENAME,
        ]);
    }

    /**
     * Download the import template.
     */
    public function importTemplate()
    {
        $spreadsheet = $this->bookImportService->buildTemplate();
        $filename = BookImportService::TEMPLATE_FILENAME;

        // Generated from BookImportService::COLUMNS, so there is no template
        // file on disk that could drift out of step with the importer.
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        return response()->streamDownload(function () use ($writer, $spreadsheet) {
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Import books from a spreadsheet.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv|max:5120',
        ], [
            'file.mimes' => 'Upload an .xlsx or .csv file. If your file is .xls, re-save it as .xlsx first.',
        ]);

        $path = $request->file('file')->getRealPath();
        $extension = strtolower($request->file('file')->getClientOriginalExtension());

        try {
            $spreadsheet = $extension === 'csv'
                ? IOFactory::createReader('Csv')->load($path)
                : (new XlsxReader())->setReadDataOnly(true)->load($path);
        } catch (\Throwable $e) {
            Flash::error('That file could not be read as a spreadsheet. Re-download the template and try again.');

            return redirect(route('books.import'));
        }

        $result = $this->bookImportService->import($spreadsheet);
        $spreadsheet->disconnectWorksheets();

        if ($result['imported'] > 0) {
            AuditTrail::log('Book', 'IMPORT', null, null, [
                'imported' => $result['imported'],
                'skipped' => count($result['errors']),
                'file' => $request->file('file')->getClientOriginalName(),
            ]);
        }

        // The report is passed through the session because a bulk import can
        // produce hundreds of row errors, which is far too much to put in a
        // flash message.
        return redirect(route('books.import'))->with('import_result', $result);
    }
}
