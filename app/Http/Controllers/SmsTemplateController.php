<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSmsTemplateRequest;
use App\Http\Requests\UpdateSmsTemplateRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\SmsTemplate;
use App\Models\TemplateCategory;
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

    private function getDropdownData()
    {
        return [
            'categories' => TemplateCategory::where('type', 'SMS')->orWhere('type', 'Both')->pluck('name', 'name')->toArray()
        ];
    }

    /**
     * Display a listing of the SmsTemplate.
     */
    public function index(Request $request)
    {
        $query = SmsTemplate::query();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $smsTemplates = $query->paginate(10);
        $dropdownData = $this->getDropdownData();

        return view('sms_templates.index', compact('smsTemplates', 'dropdownData'));
    }

    /**
     * Show the form for creating a new SmsTemplate.
     */
    public function create()
    {
        $dropdownData = $this->getDropdownData();
        return view('sms_templates.create', $dropdownData);
    }

    /**
     * Store a newly created SmsTemplate in storage.
     */
    public function store(CreateSmsTemplateRequest $request)
    {
        $input = $request->all();
        $input['created_by'] = auth()->id();

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

        $dropdownData = $this->getDropdownData();
        return view('sms_templates.edit', array_merge(['smsTemplate' => $smsTemplate], $dropdownData));
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
