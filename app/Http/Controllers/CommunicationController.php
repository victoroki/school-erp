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
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Flash;
use App\Jobs\SendBulkMessage;

class CommunicationController extends Controller
{
    public function __construct()
    {
        // This guard previously listed 'index', 'show' and 'sentMessages' —
        // none of which exist on this controller. As a result
        // can:communication.view protected nothing at all, and the message
        // history, individual message detail (recipient names and numbers),
        // template bodies and recipient counts were readable by ANY
        // authenticated user, including Teachers and the parent portal.
        //
        // Method names below match the real actions, and the permissions match
        // what config/menu.php already advertises for the same screens.
        $this->middleware('can:communication.view')->only([
            'history',
            'showHistory',
        ]);

        // Compose-screen helpers: template payloads and recipient counts.
        $this->middleware('can:communication.manage')->only([
            'compose',
            'send',
            'getTemplate',
            'getRecipientCount',
        ]);
    }

    public function compose(Request $request)
    {
        $smsTemplates = SmsTemplate::where('status', 'active')->get();
        $emailTemplates = EmailTemplate::where('status', 'active')->get();
        $classes = SchoolClass::pluck('name', 'class_id');
        $sections = Section::pluck('name', 'section_id');

        // Build a keyed collection of ClassSection records for the section filter dropdown.
        $classSections = \App\Models\ClassSection::with(['schoolClass', 'section'])
            ->get()
            ->mapWithKeys(function ($cs) {
                $label = ($cs->schoolClass->name ?? 'Class') . ' - ' . ($cs->section->name ?? 'Section');
                return [$cs->class_section_id => $label];
            });

        $selectedTemplate = null;
        if ($request->filled('template_id')) {
            if ($request->type == 'SMS') {
                $selectedTemplate = SmsTemplate::find($request->template_id);
            } else {
                $selectedTemplate = EmailTemplate::find($request->template_id);
            }
        }

        return view('communication.compose', compact('smsTemplates', 'emailTemplates', 'classes', 'sections', 'classSections', 'selectedTemplate'));
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

        AuditTrail::log('Communication', 'SEND', $sentMessage->id, null, [
            'message_type' => $sentMessage->message_type,
            'recipient_group' => $sentMessage->recipient_type,
            'template_id' => $sentMessage->template_id,
        ]);

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

    public function getRecipientCount(Request $request)
    {
        $group = $request->input('recipient_group');
        $classId = $request->input('class_id');
        $classSectionId = $request->input('class_section_id');
        $count = 0;

        switch ($group) {
            case 'All Students':
                $count = Student::where('status', 'active')->count();
                break;
            case 'All Parents':
                $count = Parents::count();
                break;
            case 'All Staff':
                $count = Staff::count();
                break;
            case 'Class':
                if ($classId) {
                    $count = \App\Models\StudentClassEnrollment::whereHas('classSection', function ($q) use ($classId) {
                        $q->where('class_id', $classId);
                    })->count();
                }
                break;
            case 'Class Section':
                if ($classSectionId) {
                    $count = \App\Models\StudentClassEnrollment::where('class_section_id', $classSectionId)->count();
                }
                break;
        }

        return response()->json(['count' => $count]);
    }
}
