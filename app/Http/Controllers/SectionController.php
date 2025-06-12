<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSectionRequest;
use App\Http\Requests\UpdateSectionRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\SectionRepository;
use Illuminate\Http\Request;
use Flash;

class SectionController extends AppBaseController
{
    /** @var SectionRepository $sectionRepository*/
    private $sectionRepository;

    public function __construct(SectionRepository $sectionRepo)
    {
        $this->sectionRepository = $sectionRepo;
    }

    /**
     * Display a listing of the Section.
     */
    public function index(Request $request)
    {
        $sections = $this->sectionRepository->paginate(10);

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
        $classes = \App\Models\SchoolClass::pluck('name', 'id');

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

        $section = $this->sectionRepository->update($request->all(), $id);

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

        $this->sectionRepository->delete($id);

        Flash::success('Section deleted successfully.');

        return redirect(route('sections.index'));
    }
}