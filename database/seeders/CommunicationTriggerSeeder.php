<?php

namespace Database\Seeders;

use App\Models\CommunicationTrigger;
use Illuminate\Database\Seeder;

class CommunicationTriggerSeeder extends Seeder
{
    public function run(): void
    {
        $triggers = [
            [
                'trigger_type' => 'medical_incident',
                'name' => 'Medical Incident Alert',
                'description' => 'Notify parents when a medical incident is recorded for their child',
                'is_enabled' => true,
                'requires_confirmation' => true,
                'channel' => 'sms',
            ],
            [
                'trigger_type' => 'disciplinary',
                'name' => 'Disciplinary Notice',
                'description' => 'Notify parents of disciplinary actions involving their child',
                'is_enabled' => false,
                'requires_confirmation' => true,
                'channel' => 'sms',
            ],
            [
                'trigger_type' => 'exam_published',
                'name' => 'Exam Results Published',
                'description' => 'Notify parents when exam results are published',
                'is_enabled' => false,
                'requires_confirmation' => false,
                'channel' => 'sms',
            ],
            [
                'trigger_type' => 'fee_reminder',
                'name' => 'Fee Payment Reminder',
                'description' => 'Send scheduled reminders for outstanding fee balances',
                'is_enabled' => false,
                'requires_confirmation' => false,
                'channel' => 'sms',
            ],
            [
                'trigger_type' => 'attendance_absence',
                'name' => 'Attendance Absence Alert',
                'description' => 'Notify parents when their child is marked absent',
                'is_enabled' => false,
                'requires_confirmation' => false,
                'channel' => 'sms',
            ],
            [
                'trigger_type' => 'manual',
                'name' => 'Manual Message',
                'description' => 'On-demand messages sent by staff via Compose',
                'is_enabled' => true,
                'requires_confirmation' => false,
                'channel' => 'sms',
            ],
        ];

        foreach ($triggers as $trigger) {
            CommunicationTrigger::updateOrCreate(
                ['trigger_type' => $trigger['trigger_type']],
                $trigger
            );
        }
    }
}
