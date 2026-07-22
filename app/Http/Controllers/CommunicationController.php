<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\EmailTemplate;
use App\Models\SentMessage;
use App\Models\SmsTemplate;
use App\Models\Student;
use App\Models\Parents;
use App\Models\Staff;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Http\Request;
use Flash;
use App\Jobs\SendBulkMessage;

class CommunicationController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:communication.view')->only(['index', 'show', 'sentMessages']);
        $this->middleware('can:communication.manage')->only(['compose', 'send', 'store']);
    }

    public function compose(Request $request)
    {
        $smsTemplates = SmsTemplate::where('status', 'active')->get();
        $emailTemplates = EmailTemplate::where('status', 'active')->get();
        $classes = SchoolClass::pluck('name', 'class_id');
        $sections = Section::pluck('name', 'section_id');

        $selectedTemplate = null;
        if ($request->filled('template_id')) {
            if ($request->type == 'SMS') {
                $selectedTemplate = SmsTemplate::find($request->template_id);
            } else {
                $selectedTemplate = EmailTemplate::find($request->template_id);
            }
        }

        return view('communication.compose', compact('smsTemplates', 'emailTemplates', 'classes', 'sections', 'selectedTemplate'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'message_type' => 'required|in:SMS,Email',
            'content' => 'required',
            'recipient_group' => 'required',
        ]);

        $sentMessage = SentMessage::create([
            'message_type' => $request->message_type,
            'template_id' => $request->template_id,
            'subject' => $request->subject,
            'content' => $request->content,
            'recipient_type' => $request->recipient_group,
            'sent_by' => auth()->id(),
            'status' => 'Sending',
        ]);

        // Dispatch Job
        SendBulkMessage::dispatch($sentMessage, $request->all());

        if ($request->template_id) {
            if ($request->message_type == 'SMS') {
                SmsTemplate::find($request->template_id)->increment('usage_count');
            } else {
                EmailTemplate::find($request->template_id)->increment('usage_count');
            }
        }

        Flash::success('Message sending initiated. You can track progress in Message History.');

        return redirect(route('communication.history.index'));
    }

    public function history()
    {
        $history = SentMessage::with('sender')->orderBy('created_at', 'desc')->paginate(10);
        return view('communication.history.index', compact('history'));
    }

    public function showHistory($id)
    {
        $message = SentMessage::with(['sender', 'recipients'])->find($id);
        if (empty($message)) {
            Flash::error('Message not found');
            return redirect(route('communication.history.index'));
        }

        return view('communication.history.show', compact('message'));
    }

    public function getTemplate($type, $id)
    {
        if ($type == 'SMS') {
            $template = SmsTemplate::find($id);
        } else {
            $template = EmailTemplate::find($id);
        }

        return response()->json($template);
    }
}
