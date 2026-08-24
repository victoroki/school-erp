<?php

namespace App\Http\Controllers;

use App\Models\CommunicationLog;
use App\Models\CommunicationSetting;
use App\Models\CommunicationTrigger;
use App\Models\EmailTemplate;
use App\Models\PendingConfirmation;
use App\Models\SentMessage;
use App\Models\SmsTemplate;
use Illuminate\Http\Request;

class CommunicationDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:communication.view');
    }

    public function index()
    {
        $dailyLimitSetting = CommunicationSetting::where('settings_key', 'daily_sms_limit')->first();
        $dailyLimit = (int) ($dailyLimitSetting?->credentials['limit'] ?? 500);

        $stats = [
            'total_sms_sent' => SentMessage::where('message_type', 'SMS')->where('status', 'Sent')->count(),
            'total_email_sent' => SentMessage::where('message_type', 'Email')->where('status', 'Sent')->count(),
            'total_templates' => SmsTemplate::count() + EmailTemplate::count(),
            'failed_messages' => SentMessage::where('status', 'Failed')->count(),
            'recent_messages' => SentMessage::with('sender')->orderBy('created_at', 'desc')->limit(5)->get(),
            'popular_sms_templates' => SmsTemplate::orderBy('usage_count', 'desc')->limit(3)->get(),
            'popular_email_templates' => EmailTemplate::orderBy('usage_count', 'desc')->limit(3)->get(),
            'pending_count' => PendingConfirmation::pending()->count(),
            'daily_cost' => CommunicationLog::whereDate('created_at', today())->where('status', 'sent')->sum('cost'),
            'daily_sms_count' => CommunicationLog::whereDate('created_at', today())->where('status', 'sent')->where('channel', 'sms')->count(),
            'daily_limit' => $dailyLimit,
            'active_triggers' => CommunicationTrigger::where('is_enabled', true)->count(),
        ];

        return view('communication.dashboard', compact('stats'));
    }
}
