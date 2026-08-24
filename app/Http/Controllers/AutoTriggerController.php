<?php

namespace App\Http\Controllers;

use App\Models\CommunicationTemplate;
use App\Models\CommunicationTrigger;
use Illuminate\Http\Request;
use Flash;

class AutoTriggerController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:communication.view')->only(['index']);
        $this->middleware('can:communication.manage')->only(['edit', 'update', 'toggle']);
    }

    public function index()
    {
        $triggers = CommunicationTrigger::with('defaultTemplate')->get();
        $templates = CommunicationTemplate::active()->get();

        return view('communication.triggers.index', compact('triggers', 'templates'));
    }

    public function edit($id)
    {
        $trigger = CommunicationTrigger::findOrFail($id);
        $templates = CommunicationTemplate::active()->get();

        return view('communication.triggers.edit', compact('trigger', 'templates'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'is_enabled' => 'required|boolean',
            'requires_confirmation' => 'required|boolean',
            'default_template_id' => 'nullable|exists:communication_templates,id',
            'channel' => 'required|in:sms,email,both',
        ]);

        $trigger = CommunicationTrigger::findOrFail($id);
        $trigger->update($request->only([
            'is_enabled',
            'requires_confirmation',
            'default_template_id',
            'channel',
        ]));

        Flash::success('Trigger updated successfully.');
        return redirect(route('communication.triggers.index'));
    }

    public function toggle(Request $request, $id)
    {
        $trigger = CommunicationTrigger::findOrFail($id);
        $trigger->update(['is_enabled' => $request->boolean('is_enabled')]);

        return response()->json([
            'success' => true,
            'is_enabled' => $trigger->fresh()->is_enabled,
        ]);
    }
}
