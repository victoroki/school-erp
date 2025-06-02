<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateEmailTemplateRequest;
use App\Http\Requests\UpdateEmailTemplateRequest;
use App\Http\Controllers\AppBaseController;
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

    /**
     * Display a listing of the EmailTemplate.
     */
    public function index(Request $request)
    {
        $emailTemplates = $this->emailTemplateRepository->paginate(10);

        return view('email_templates.index')
            ->with('emailTemplates', $emailTemplates);
    }

    /**
     * Show the form for creating a new EmailTemplate.
     */
    public function create()
    {
        return view('email_templates.create');
    }

    /**
     * Store a newly created EmailTemplate in storage.
     */
    public function store(CreateEmailTemplateRequest $request)
    {
        $input = $request->all();

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

        return view('email_templates.edit')->with('emailTemplate', $emailTemplate);
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
