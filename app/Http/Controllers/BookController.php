<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\BookCategory;
use App\Repositories\BookRepository;
use Illuminate\Http\Request;
use Flash;

class BookController extends AppBaseController
{
    /** @var BookRepository $bookRepository*/
    private $bookRepository;

    public function __construct(BookRepository $bookRepo)
    {
        $this->bookRepository = $bookRepo;
    }

    private function getDropdownData(){
        return[
            'bkCategory' => BookCategory::pluck('name',  'category_id')
        ];
    }

    /**
     * Display a listing of the Book.
     */
    public function index(Request $request)
    {
        $books = $this->bookRepository->allQuery()->with('category')->paginate(10);

        return view('books.index')
            ->with('books', $books);
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

        Flash::success('Book saved successfully.');

        return redirect(route('books.index'));
    }

    /**
     * Display the specified Book.
     */
    public function show($id)
    {
        $book = $this->bookRepository->find($id);

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

        $book = $this->bookRepository->update($request->all(), $id);

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

        $this->bookRepository->delete($id);

        Flash::success('Book deleted successfully.');

        return redirect(route('books.index'));
    }
}
