<?php

namespace App\Listeners;

use App\Events\NotificationTriggered;
use App\Jobs\SendSingleNotification;
use App\Models\CommunicationLog;
use App\Models\CommunicationTrigger;
use App\Models\ParentNotificationPreference;
use App\Models\Parents;
use App\Models\PendingConfirmation;
use App\Services\Communication\PhoneHelper;
use App\Services\Communication\TemplateRenderer;

class QueueAutoNotification
{
    public function handle(NotificationTriggered $event): void
    {
        $triggerType = $event->triggerType;

        if (!CommunicationTrigger::isEnabled($triggerType)) {
            return;
        }

        if (CommunicationTrigger::requiresConfirmation($triggerType)) {
            $this->createPendingConfirmations($event);
            return;
        }

        $dispatcher = app(\App\Services\Communication\NotificationDispatcher::class);
        $dispatcher->dispatchToParents(
            $triggerType,
            $event->studentId,
            $event->triggerModel,
            $event->triggerId,
            $event->context
        );
    }

    private function createPendingConfirmations(NotificationTriggered $event): void
    {
        $student = \App\Models\Student::with('parents')->find($event->studentId);
        if (!$student || $student->parents->isEmpty()) {
            return;
        }

        $template = \App\Models\CommunicationTemplate::forTrigger($event->triggerType)->first();
        if (!$template) {
            return;
        }

        $studentName = $student->full_name;
        $studentClass = $student->currentEnrollment?->classSection?->schoolClass?->name ?? '';

        foreach ($student->parents as $parent) {
            $channel = $template->channel === 'both' ? 'sms' : $template->channel;
            $contact = $channel === 'sms' ? $parent->phone : $parent->email;
            if (!$contact) continue;

            $renderedBody = TemplateRenderer::render($template->body, array_merge($event->context, [
                'student_name' => $studentName,
                'student_first_name' => $student->first_name,
                'student_class' => $studentClass,
                'parent_name' => trim($parent->first_name . ' ' . $parent->last_name),
                'parent_first_name' => $parent->first_name,
            ]));

            PendingConfirmation::create([
                'trigger_type' => $event->triggerType,
                'trigger_id' => $event->triggerId,
                'trigger_model' => $event->triggerModel,
                'recipient_type' => 'parent',
                'recipient_id' => $parent->parent_id,
                'contact' => $channel === 'sms' ? PhoneHelper::formatForSms($contact) : $contact,
                'recipient_name' => trim($parent->first_name . ' ' . $parent->last_name),
                'student_name' => $studentName,
                'channel' => $channel,
                'subject' => $template->subject,
                'rendered_body' => $renderedBody,
                'status' => 'pending',
            ]);
        }
    }
}
