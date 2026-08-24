<?php

namespace Database\Seeders;

use App\Models\CommunicationTemplate;
use Illuminate\Database\Seeder;

class CommunicationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Medical Incident Alert',
                'trigger_type' => 'medical_incident',
                'channel' => 'sms',
                'subject' => 'Medical Incident - {student_name}',
                'body' => 'Dear {parent_first_name}, {student_first_name} had a medical incident on {incident_date}. Symptoms: {symptoms}. Treatment: {treatment}. Please contact the school for details. - {school_name}',
                'is_active' => true,
                'is_critical' => true,
            ],
            [
                'name' => 'Disciplinary Notice',
                'trigger_type' => 'disciplinary',
                'channel' => 'sms',
                'subject' => 'Disciplinary Notice - {student_name}',
                'body' => 'Dear {parent_first_name}, {student_name} ({student_class}) was involved in a disciplinary matter on {incident_date}. Type: {incident_type}. Action taken: {action_taken}. - {school_name}',
                'is_active' => true,
                'is_critical' => true,
            ],
            [
                'name' => 'Exam Results Published',
                'trigger_type' => 'exam_published',
                'channel' => 'sms',
                'subject' => 'Exam Results - {student_name}',
                'body' => 'Dear {parent_first_name}, {student_name} ({student_class}) exam results are out. Subject: {subject_name}, Marks: {marks}, Grade: {grade}. Remarks: {remarks}. - {school_name}',
                'is_active' => true,
                'is_critical' => false,
            ],
            [
                'name' => 'Fee Payment Reminder',
                'trigger_type' => 'fee_reminder',
                'channel' => 'sms',
                'subject' => 'Fee Reminder - {student_name}',
                'body' => 'Dear {parent_first_name}, {student_name} has an outstanding balance of KES {fee_balance}. Please clear by end of term. - {school_name}',
                'is_active' => true,
                'is_critical' => false,
            ],
            [
                'name' => 'Attendance Absence Alert',
                'trigger_type' => 'attendance_absence',
                'channel' => 'sms',
                'subject' => 'Absence Alert - {student_name}',
                'body' => 'Dear {parent_first_name}, {student_name} was marked {attendance_status} on {attendance_date}. Please contact the school if this was not expected. - {school_name}',
                'is_active' => true,
                'is_critical' => false,
            ],
        ];

        foreach ($templates as $template) {
            CommunicationTemplate::updateOrCreate(
                ['trigger_type' => $template['trigger_type'], 'name' => $template['name']],
                $template
            );
        }
    }
}
