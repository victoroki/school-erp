<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateBookCategoryRequest;
use App\Http\Requests\UpdateBookCategoryRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\BookCategoryRepository;
use Illuminate\Http\Request;
use Flash;

class BookCategoryController extends AppBaseController
{
    /** @var BookCategoryRepository $bookCategoryRepository*/
    private $bookCategoryRepository;

    public function __construct(BookCategoryRepository $bookCategoryRepo)
    {
        $this->bookCategoryRepository = $bookCategoryRepo;
    }

    /**
     * Display a listing of the BookCategory.
     */
    public function index(Request $request)
    {
        $bookCategories = $this->bookCategoryRepository->paginate(10);

        return view('book_categories.index')
            ->with('bookCategories', $bookCategories);
    }

    /**
     * Show the form for creating a new BookCategory.
     */
    public function create()
    {
        return view('book_categories.create');
    }

    /**
     * Store a newly created BookCategory in storage.
     */
    public function store(CreateBookCategoryRequest $request)
    {
        $input = $request->all();

        $bookCategory = $this->bookCategoryRepository->create($input);

        Flash::success('Book Category saved successfully.');

        return redirect(route('bookCategories.index'));
    }

    /**
     * Display the specified BookCategory.
     */
    public function show($id)
    {
        $bookCategory = $this->bookCategoryRepository->find($id);

        if (empty($bookCategory)) {
            Flash::error('Book Category not found');

            return redirect(route('bookCategories.index'));
        }

        return view('book_categories.show')->with('bookCategory', $bookCategory);
    }

    /**
     * Show the form for editing the specified BookCategory.
     */
    public function edit($id)
    {
        $bookCategory = $this->bookCategoryRepository->find($id);

        if (empty($bookCategory)) {
            Flash::error('Book Category not found');

            return redirect(route('bookCategories.index'));
        }

        return view('book_categories.edit')->with('bookCategory', $bookCategory);
    }

    /**
     * Update the specified BookCategory in storage.
     */
    public function update($id, UpdateBookCategoryRequest $request)
    {
        $bookCategory = $this->bookCategoryRepository->find($id);

        if (empty($bookCategory)) {
            Flash::error('Book Category not found');

            return redirect(route('bookCategories.index'));
        }

        $bookCategory = $this->bookCategoryRepository->update($request->all(), $id);

        Flash::success('Book Category updated successfully.');

        return redirect(route('bookCategories.index'));
    }

    /**
     * Remove the specified BookCategory from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $bookCategory = $this->bookCategoryRepository->find($id);

        if (empty($bookCategory)) {
            Flash::error('Book Category not found');

            return redirect(route('bookCategories.index'));
        }

        $this->bookCategoryRepository->delete($id);

        Flash::success('Book Category deleted successfully.');

        return redirect(route('bookCategories.index'));
    }
}
