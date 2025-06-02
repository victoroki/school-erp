<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSmsTemplateRequest;
use App\Http\Requests\UpdateSmsTemplateRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\SmsTemplateRepository;
use Illuminate\Http\Request;
use Flash;

class SmsTemplateController extends AppBaseController
{
    /** @var SmsTemplateRepository $smsTemplateRepository*/
    private $smsTemplateRepository;

    public function __construct(SmsTemplateRepository $smsTemplateRepo)
    {
        $this->smsTemplateRepository = $smsTemplateRepo;
    }

    /**
     * Display a listing of the SmsTemplate.
     */
    public function index(Request $request)
    {
        $smsTemplates = $this->smsTemplateRepository->paginate(10);

        return view('sms_templates.index')
            ->with('smsTemplates', $smsTemplates);
    }

    /**
     * Show the form for creating a new SmsTemplate.
     */
    public function create()
    {
        return view('sms_templates.create');
    }

    /**
     * Store a newly created SmsTemplate in storage.
     */
    public function store(CreateSmsTemplateRequest $request)
    {
        $input = $request->all();

        $smsTemplate = $this->smsTemplateRepository->create($input);

        Flash::success('Sms Template saved successfully.');

        return redirect(route('smsTemplates.index'));
    }

    /**
     * Display the specified SmsTemplate.
     */
    public function show($id)
    {
        $smsTemplate = $this->smsTemplateRepository->find($id);

        if (empty($smsTemplate)) {
            Flash::error('Sms Template not found');

            return redirect(route('smsTemplates.index'));
        }

        return view('sms_templates.show')->with('smsTemplate', $smsTemplate);
    }

    /**
     * Show the form for editing the specified SmsTemplate.
     */
    public function edit($id)
    {
        $smsTemplate = $this->smsTemplateRepository->find($id);

        if (empty($smsTemplate)) {
            Flash::error('Sms Template not found');

            return redirect(route('smsTemplates.index'));
        }

        return view('sms_templates.edit')->with('smsTemplate', $smsTemplate);
    }

    /**
     * Update the specified SmsTemplate in storage.
     */
    public function update($id, UpdateSmsTemplateRequest $request)
    {
        $smsTemplate = $this->smsTemplateRepository->find($id);

        if (empty($smsTemplate)) {
            Flash::error('Sms Template not found');

            return redirect(route('smsTemplates.index'));
        }

        $smsTemplate = $this->smsTemplateRepository->update($request->all(), $id);

        Flash::success('Sms Template updated successfully.');

        return redirect(route('smsTemplates.index'));
    }

    /**
     * Remove the specified SmsTemplate from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $smsTemplate = $this->smsTemplateRepository->find($id);

        if (empty($smsTemplate)) {
            Flash::error('Sms Template not found');

            return redirect(route('smsTemplates.index'));
        }

        $this->smsTemplateRepository->delete($id);

        Flash::success('Sms Template deleted successfully.');

        return redirect(route('smsTemplates.index'));
    }
}
