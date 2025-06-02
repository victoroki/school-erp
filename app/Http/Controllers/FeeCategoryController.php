<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateFeeCategoryRequest;
use App\Http\Requests\UpdateFeeCategoryRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\FeeCategoryRepository;
use Illuminate\Http\Request;
use Flash;

class FeeCategoryController extends AppBaseController
{
    /** @var FeeCategoryRepository $feeCategoryRepository*/
    private $feeCategoryRepository;

    public function __construct(FeeCategoryRepository $feeCategoryRepo)
    {
        $this->feeCategoryRepository = $feeCategoryRepo;
    }

    /**
     * Display a listing of the FeeCategory.
     */
    public function index(Request $request)
    {
        $feeCategories = $this->feeCategoryRepository->paginate(10);

        return view('fee_categories.index')
            ->with('feeCategories', $feeCategories);
    }

    /**
     * Show the form for creating a new FeeCategory.
     */
    public function create()
    {
        return view('fee_categories.create');
    }

    /**
     * Store a newly created FeeCategory in storage.
     */
    public function store(CreateFeeCategoryRequest $request)
    {
        $input = $request->all();

        $feeCategory = $this->feeCategoryRepository->create($input);

        Flash::success('Fee Category saved successfully.');

        return redirect(route('feeCategories.index'));
    }

    /**
     * Display the specified FeeCategory.
     */
    public function show($id)
    {
        $feeCategory = $this->feeCategoryRepository->find($id);

        if (empty($feeCategory)) {
            Flash::error('Fee Category not found');

            return redirect(route('feeCategories.index'));
        }

        return view('fee_categories.show')->with('feeCategory', $feeCategory);
    }

    /**
     * Show the form for editing the specified FeeCategory.
     */
    public function edit($id)
    {
        $feeCategory = $this->feeCategoryRepository->find($id);

        if (empty($feeCategory)) {
            Flash::error('Fee Category not found');

            return redirect(route('feeCategories.index'));
        }

        return view('fee_categories.edit')->with('feeCategory', $feeCategory);
    }

    /**
     * Update the specified FeeCategory in storage.
     */
    public function update($id, UpdateFeeCategoryRequest $request)
    {
        $feeCategory = $this->feeCategoryRepository->find($id);

        if (empty($feeCategory)) {
            Flash::error('Fee Category not found');

            return redirect(route('feeCategories.index'));
        }

        $feeCategory = $this->feeCategoryRepository->update($request->all(), $id);

        Flash::success('Fee Category updated successfully.');

        return redirect(route('feeCategories.index'));
    }

    /**
     * Remove the specified FeeCategory from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $feeCategory = $this->feeCategoryRepository->find($id);

        if (empty($feeCategory)) {
            Flash::error('Fee Category not found');

            return redirect(route('feeCategories.index'));
        }

        $this->feeCategoryRepository->delete($id);

        Flash::success('Fee Category deleted successfully.');

        return redirect(route('feeCategories.index'));
    }
}
