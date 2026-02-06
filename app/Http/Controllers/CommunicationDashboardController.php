<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use App\Models\SentMessage;
use App\Models\SmsTemplate;
use Illuminate\Http\Request;

class CommunicationDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_sms_sent' => SentMessage::where('message_type', 'SMS')->where('status', 'Sent')->count(),
            'total_email_sent' => SentMessage::where('message_type', 'Email')->where('status', 'Sent')->count(),
            'total_templates' => SmsTemplate::count() + EmailTemplate::count(),
            'failed_messages' => SentMessage::where('status', 'Failed')->count(),
            'recent_messages' => SentMessage::with('sender')->orderBy('created_at', 'desc')->limit(5)->get(),
            'popular_sms_templates' => SmsTemplate::orderBy('usage_count', 'desc')->limit(3)->get(),
            'popular_email_templates' => EmailTemplate::orderBy('usage_count', 'desc')->limit(3)->get(),
        ];

        return view('communication.dashboard', compact('stats'));
    }
}
