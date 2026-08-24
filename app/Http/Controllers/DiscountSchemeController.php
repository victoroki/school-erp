<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateDiscountSchemeRequest;
use App\Http\Requests\UpdateDiscountSchemeRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\DiscountSchemeRepository;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Flash;

class DiscountSchemeController extends AppBaseController
{
    /** @var DiscountSchemeRepository $discountSchemeRepository*/
    private $discountSchemeRepository;

    public function __construct(DiscountSchemeRepository $discountSchemeRepo)
    {
        $this->discountSchemeRepository = $discountSchemeRepo;
        $this->middleware('can:fees.view')->only(['index', 'show']);
        $this->middleware('can:fees.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Display a listing of the DiscountScheme.
     */
    public function index(Request $request)
    {
        $discountSchemes = $this->discountSchemeRepository->paginate(10);

        return view('discount_schemes.index')
            ->with('discountSchemes', $discountSchemes);
    }

    /**
     * Show the form for creating a new DiscountScheme.
     */
    public function create()
    {
        $academicYears = \App\Models\AcademicYear::pluck('name', 'academic_year_id');
        $feeCategories = \App\Models\FeeCategory::pluck('name', 'category_id');
        
        return view('discount_schemes.create')
            ->with('academicYears', $academicYears)
            ->with('feeCategories', $feeCategories);
    }

    /**
     * Store a newly created DiscountScheme in storage.
     */
    public function store(CreateDiscountSchemeRequest $request)
    {
        $input = $request->all();

        $discountScheme = $this->discountSchemeRepository->create($input);

        AuditTrail::log('Discount Scheme', 'CREATE', $discountScheme->id, null, $discountScheme->toArray());

        Flash::success('Discount Scheme saved successfully.');

        return redirect(route('fees.discounts.index'));
    }

    /**
     * Display the specified DiscountScheme.
     */
    public function show($id)
    {
        $discountScheme = $this->discountSchemeRepository->find($id);

        if (empty($discountScheme)) {
            Flash::error('Discount Scheme not found');

            return redirect(route('fees.discounts.index'));
        }

        return view('discount_schemes.show')->with('discountScheme', $discountScheme);
    }

    /**
     * Show the form for editing the specified DiscountScheme.
     */
    public function edit($id)
    {
        $discountScheme = $this->discountSchemeRepository->find($id);

        if (empty($discountScheme)) {
            Flash::error('Discount Scheme not found');

            return redirect(route('fees.discounts.index'));
        }

        $academicYears = \App\Models\AcademicYear::pluck('name', 'academic_year_id');
        $feeCategories = \App\Models\FeeCategory::pluck('name', 'category_id');

        return view('discount_schemes.edit')
            ->with('discountScheme', $discountScheme)
            ->with('academicYears', $academicYears)
            ->with('feeCategories', $feeCategories);
    }

    /**
     * Update the specified DiscountScheme in storage.
     */
    public function update($id, UpdateDiscountSchemeRequest $request)
    {
        $discountScheme = $this->discountSchemeRepository->find($id);

        if (empty($discountScheme)) {
            Flash::error('Discount Scheme not found');

            return redirect(route('fees.discounts.index'));
        }

        $oldData = $discountScheme->toArray();
        $discountScheme = $this->discountSchemeRepository->update($request->all(), $id);

        AuditTrail::log('Discount Scheme', 'UPDATE', $discountScheme->id, $oldData, $discountScheme->toArray());

        Flash::success('Discount Scheme updated successfully.');

        return redirect(route('fees.discounts.index'));
    }

    /**
     * Remove the specified DiscountScheme from storage.
     */
    public function destroy($id)
    {
        $discountScheme = $this->discountSchemeRepository->find($id);

        if (empty($discountScheme)) {
            Flash::error('Discount Scheme not found');

            return redirect(route('fees.discounts.index'));
        }

        $oldData = $discountScheme->toArray();
        $this->discountSchemeRepository->delete($id);

        AuditTrail::log('Discount Scheme', 'DELETE', $id, $oldData, null);

        Flash::success('Discount Scheme deleted successfully.');

        return redirect(route('fees.discounts.index'));
    }
}
