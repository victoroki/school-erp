<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSectionRequest;
use App\Http\Requests\UpdateSectionRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\SectionRepository;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Flash;

class SectionController extends AppBaseController
{
    /** @var SectionRepository $sectionRepository*/
    private $sectionRepository;

    public function __construct(SectionRepository $sectionRepo)
    {
        $this->sectionRepository = $sectionRepo;

        $this->middleware('auth');
        $this->middleware('can:academics.view')->only(['index', 'show']);
        $this->middleware('can:academics.settings.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Display a listing of the Section.
     */
    public function index(Request $request)
    {
        $allSections = $this->sectionRepository->with(['Schoolclass'])->get();

        // Group sections by Class Name
        $sections = $allSections->groupBy(function($section) {
             return $section->schoolClass ? $section->schoolClass->name : 'Unassigned';
        });

        return view('sections.index')
            ->with('sections', $sections);
    }

    /**
     * Show the form for creating a new Section.
     */
    public function create()
    {
        // Get classes for dropdown - adjust the model name based on your actual class model
        $classes = \App\Models\SchoolClass::pluck('name', 'class_id');
        
        return view('sections.create')
            ->with('classes', $classes);
    }

    /**
     * Store a newly created Section in storage.
     */
    public function store(CreateSectionRequest $request)
    {
        $input = $request->all();

        $section = $this->sectionRepository->create($input);

        AuditTrail::log('Section', 'CREATE', $section->section_id, null, $section->toArray());

        Flash::success('Section saved successfully.');

        return redirect(route('sections.index'));
    }

    /**
     * Display the specified Section.
     */
    public function show($id)
    {
        $section = $this->sectionRepository->find($id);

        if (empty($section)) {
            Flash::error('Section not found');

            return redirect(route('sections.index'));
        }

        return view('sections.show')->with('section', $section);
    }

    /**
     * Show the form for editing the specified Section.
     */
    public function edit($id)
    {
        $section = $this->sectionRepository->find($id);

        if (empty($section)) {
            Flash::error('Section not found');

            return redirect(route('sections.index'));
        }

        // Get classes for dropdown - adjust the model name based on your actual class model
        $classes = \App\Models\SchoolClass::pluck('name', 'class_id');

        return view('sections.edit')
            ->with('section', $section)
            ->with('classes', $classes);
    }

    /**
     * Update the specified Section in storage.
     */
    public function update($id, UpdateSectionRequest $request)
    {
        $section = $this->sectionRepository->find($id);

        if (empty($section)) {
            Flash::error('Section not found');

            return redirect(route('sections.index'));
        }

        $oldData = $section->toArray();
        $section = $this->sectionRepository->update($request->all(), $id);

        AuditTrail::log('Section', 'UPDATE', $section->section_id, $oldData, $section->toArray());

        Flash::success('Section updated successfully.');

        return redirect(route('sections.index'));
    }

    /**
     * Remove the specified Section from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $section = $this->sectionRepository->find($id);

        if (empty($section)) {
            Flash::error('Section not found');

            return redirect(route('sections.index'));
        }

        $oldData = $section->toArray();
        $this->sectionRepository->delete($id);

        AuditTrail::log('Section', 'DELETE', $id, $oldData, null);

        Flash::success('Section deleted successfully.');

        return redirect(route('sections.index'));
    }
}
