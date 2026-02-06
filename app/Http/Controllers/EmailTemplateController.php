<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateEmailTemplateRequest;
use App\Http\Requests\UpdateEmailTemplateRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\EmailTemplate;
use App\Models\TemplateCategory;
use App\Repositories\EmailTemplateRepository;
use Illuminate\Http\Request;
use Flash;

class EmailTemplateController extends AppBaseController
{
    /** @var EmailTemplateRepository $emailTemplateRepository*/
    private $emailTemplateRepository;

    public function __construct(EmailTemplateRepository $emailTemplateRepo)
    {
        $this->emailTemplateRepository = $emailTemplateRepo;
    }

    private function getDropdownData()
    {
        return [
            'categories' => TemplateCategory::where('type', 'Email')->orWhere('type', 'Both')->pluck('name', 'name')->toArray()
        ];
    }

    /**
     * Display a listing of the EmailTemplate.
     */
    public function index(Request $request)
    {
        $query = EmailTemplate::query();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $emailTemplates = $query->paginate(10);
        $dropdownData = $this->getDropdownData();

        return view('email_templates.index', compact('emailTemplates', 'dropdownData'));
    }

    /**
     * Show the form for creating a new EmailTemplate.
     */
    public function create()
    {
        $dropdownData = $this->getDropdownData();
        return view('email_templates.create', $dropdownData);
    }

    /**
     * Store a newly created EmailTemplate in storage.
     */
    public function store(CreateEmailTemplateRequest $request)
    {
        $input = $request->all();
        $input['created_by'] = auth()->id();

        $emailTemplate = $this->emailTemplateRepository->create($input);

        Flash::success('Email Template saved successfully.');

        return redirect(route('emailTemplates.index'));
    }

    /**
     * Display the specified EmailTemplate.
     */
    public function show($id)
    {
        $emailTemplate = $this->emailTemplateRepository->find($id);

        if (empty($emailTemplate)) {
            Flash::error('Email Template not found');

            return redirect(route('emailTemplates.index'));
        }

        return view('email_templates.show')->with('emailTemplate', $emailTemplate);
    }

    /**
     * Show the form for editing the specified EmailTemplate.
     */
    public function edit($id)
    {
        $emailTemplate = $this->emailTemplateRepository->find($id);

        if (empty($emailTemplate)) {
            Flash::error('Email Template not found');

            return redirect(route('emailTemplates.index'));
        }

        $dropdownData = $this->getDropdownData();
        return view('email_templates.edit', array_merge(['emailTemplate' => $emailTemplate], $dropdownData));
    }

    /**
     * Update the specified EmailTemplate in storage.
     */
    public function update($id, UpdateEmailTemplateRequest $request)
    {
        $emailTemplate = $this->emailTemplateRepository->find($id);

        if (empty($emailTemplate)) {
            Flash::error('Email Template not found');

            return redirect(route('emailTemplates.index'));
        }

        $emailTemplate = $this->emailTemplateRepository->update($request->all(), $id);

        Flash::success('Email Template updated successfully.');

        return redirect(route('emailTemplates.index'));
    }

    /**
     * Remove the specified EmailTemplate from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $emailTemplate = $this->emailTemplateRepository->find($id);

        if (empty($emailTemplate)) {
            Flash::error('Email Template not found');

            return redirect(route('emailTemplates.index'));
        }

        $this->emailTemplateRepository->delete($id);

        Flash::success('Email Template deleted successfully.');

        return redirect(route('emailTemplates.index'));
    }
}
