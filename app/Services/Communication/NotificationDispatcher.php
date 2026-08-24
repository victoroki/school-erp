<?php

namespace App\Services\Communication;

use App\Models\CommunicationLog;
use App\Models\CommunicationSetting;
use App\Models\CommunicationTemplate;
use App\Models\CommunicationTrigger;
use App\Models\ParentNotificationPreference;
use App\Models\Parents;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationDispatcher
{
    private ?SmsProviderInterface $smsProvider;
    private ?EmailProviderInterface $emailProvider;
    private int $dailyLimit;

    public function __construct(
        ?SmsProviderInterface $smsProvider = null,
        ?EmailProviderInterface $emailProvider = null
    ) {
        $this->smsProvider = $smsProvider;
        $this->emailProvider = $emailProvider;
        $this->dailyLimit = $this->resolveDailyLimit();
    }

    /**
     * Dispatch notifications to parents of a student for a given trigger.
     */
    public function dispatchToParents(
        string $triggerType,
        int $studentId,
        string $triggerModel,
        int $triggerId,
        array $context
    ): array {
        $results = ['sent' => 0, 'skipped' => 0, 'failed' => 0];

        if (!CommunicationTrigger::isEnabled($triggerType)) {
            return $results;
        }

        $student = Student::with('parents')->find($studentId);
        if (!$student || $student->parents->isEmpty()) {
            return $results;
        }

        $template = $this->resolveTemplate($triggerType, $student);
        if (!$template) {
            Log::warning('No template for trigger', ['trigger_type' => $triggerType]);
            return $results;
        }

        if (!$this->checkDailyLimit()) {
            Log::warning('Daily SMS limit reached', ['limit' => $this->dailyLimit]);
            return $results;
        }

        foreach ($student->parents as $parent) {
            $channel = $template->channel === 'both' ? 'sms' : $template->channel;

            if (!$this->shouldSend($triggerType, $parent, $channel)) {
                $results['skipped']++;
                continue;
            }

            if ($this->isDuplicate($triggerType, $triggerId, $parent->parent_id)) {
                $results['skipped']++;
                continue;
            }

            $contact = $channel === 'sms' ? PhoneHelper::formatForSms($parent->phone) : $parent->email;
            if (!$contact) {
                $results['skipped']++;
                continue;
            }

            $renderedBody = TemplateRenderer::render($template->body, array_merge($context, [
                'parent_name' => trim($parent->first_name . ' ' . $parent->last_name),
                'parent_first_name' => $parent->first_name,
            ]));

            $log = CommunicationLog::create([
                'trigger_type' => $triggerType,
                'trigger_id' => $triggerId,
                'trigger_model' => $triggerModel,
                'template_id' => $template->id,
                'channel' => $channel,
                'recipient_type' => 'parent',
                'recipient_id' => $parent->parent_id,
                'contact' => $contact,
                'contact_masked' => PhoneHelper::mask($contact),
                'subject' => $template->subject,
                'body' => $renderedBody,
                'status' => 'pending',
            ]);

            $sent = $this->sendNotification($channel, $contact, $renderedBody, $template->subject);
            if ($sent) {
                $log->update(['status' => 'sent', 'sent_at' => now()]);
                $results['sent']++;
            } else {
                $log->update(['status' => 'failed', 'failure_reason' => 'Provider send failed']);
                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Dispatch a single notification from a pending confirmation.
     */
    public function dispatchPending(PendingConfirmation $pending, int $userId): bool
    {
        $sent = $this->sendNotification(
            $pending->channel,
            $pending->contact,
            $pending->rendered_body,
            $pending->subject
        );

        if ($sent) {
            $pending->confirm($userId);
            return true;
        }

        return false;
    }

    /**
     * Check if a duplicate notification was already sent today.
     */
    private function isDuplicate(string $triggerType, ?int $triggerId, int $recipientId): bool
    {
        return CommunicationLog::where('trigger_type', $triggerType)
            ->where('trigger_id', $triggerId)
            ->where('recipient_id', $recipientId)
            ->whereDate('created_at', today())
            ->exists();
    }

    /**
     * Check opt-out preferences. Critical messages bypass opt-outs.
     */
    private function shouldSend(string $triggerType, Parents $parent, string $channel): bool
    {
        $template = CommunicationTemplate::forTrigger($triggerType)->first();
        if ($template && $template->is_critical) {
            return true;
        }

        $prefs = ParentNotificationPreference::where('parent_id', $parent->parent_id)->first();
        if (!$prefs) {
            return true;
        }

        if ($channel === 'sms' && $prefs->sms_opt_out) {
            return false;
        }
        if ($channel === 'email' && $prefs->email_opt_out) {
            return false;
        }

        $optOutTypes = $prefs->opt_out_types ?? [];
        if (in_array($triggerType, $optOutTypes)) {
            return false;
        }

        return true;
    }

    private function checkDailyLimit(): bool
    {
        $todayCount = CommunicationLog::whereDate('created_at', today())
            ->where('status', 'sent')
            ->count();

        return $todayCount < $this->dailyLimit;
    }

    private function resolveTemplate(string $triggerType, Student $student): ?CommunicationTemplate
    {
        $trigger = CommunicationTrigger::where('trigger_type', $triggerType)->first();
        if (!$trigger) {
            return null;
        }

        if ($trigger->default_template_id) {
            return CommunicationTemplate::find($trigger->default_template_id);
        }

        return CommunicationTemplate::forTrigger($triggerType)->first();
    }

    private function sendNotification(string $channel, string $contact, string $body, ?string $subject = null): bool
    {
        if ($channel === 'sms') {
            return $this->sendSms($contact, $body);
        }

        return $this->sendEmail($contact, $subject ?? 'Notification from ' . config('app.name'), $body);
    }

    private function sendSms(string $phone, string $message): bool
    {
        if (!$this->smsProvider) {
            $this->smsProvider = app(SmsProviderInterface::class);
        }

        $result = $this->smsProvider->send($phone, $message);
        return $result->success;
    }

    private function sendEmail(string $to, string $subject, string $body): bool
    {
        if (!$this->emailProvider) {
            $this->emailProvider = app(EmailProviderInterface::class);
        }

        return $this->emailProvider->send($to, $subject, $body);
    }

    private function resolveDailyLimit(): int
    {
        $setting = CommunicationSetting::where('settings_key', 'daily_sms_limit')->first();
        return (int) ($setting?->credentials['limit'] ?? 500);
    }
}
