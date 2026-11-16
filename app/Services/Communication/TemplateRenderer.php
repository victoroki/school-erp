<?php

namespace App\Services\Communication;

class TemplateRenderer
{
    /**
     * Render a template body by replacing {placeholder} tokens with context values.
     *
     * @param string $templateBody The template body with {placeholders}
     * @param array $context Associative array of placeholder values
     * @return string The rendered body
     */
    public static function render(string $templateBody, array $context): string
    {
        $replacements = self::buildReplacements($context);

        $rendered = $templateBody;
        foreach ($replacements as $key => $value) {
            $rendered = str_replace('{' . $key . '}', $value, $rendered);
        }

        return $rendered;
    }

    /**
     * Get the available placeholder keys for a given context.
     */
    public static function getAvailablePlaceholders(): array
    {
        return [
            'student_name' => 'Full name of the student',
            'student_first_name' => 'Student first name',
            'student_class' => 'Student class and section',
            'parent_name' => 'Parent/guardian full name',
            'parent_first_name' => 'Parent first name',
            'subject_name' => 'Subject name (exams)',
            'marks' => 'Marks obtained (exams)',
            'grade' => 'Grade/score (exams)',
            'remarks' => 'Teacher remarks (exams)',
            'incident_date' => 'Date of incident (medical/disciplinary)',
            'incident_type' => 'Type of incident (disciplinary)',
            'incident_description' => 'Incident description',
            'treatment' => 'Treatment given (medical)',
            'symptoms' => 'Symptoms (medical)',
            'action_taken' => 'Action taken (disciplinary)',
            'fee_balance' => 'Outstanding fee balance',
            'fee_total' => 'Total fee amount',
            'fee_paid' => 'Amount paid',
            'attendance_date' => 'Date of absence',
            'attendance_status' => 'Attendance status',
            'school_name' => 'School name',
            'term' => 'Current academic term',
        ];
    }

    private static function buildReplacements(array $context): array
    {
        $schoolName = config('app.name', 'School');

        $replacements = [
            'school_name' => $schoolName,
            'student_name' => $context['student_name'] ?? '',
            'student_first_name' => $context['student_first_name'] ?? '',
            'student_class' => $context['student_class'] ?? '',
            'parent_name' => $context['parent_name'] ?? '',
            'parent_first_name' => $context['parent_first_name'] ?? '',
            'subject_name' => $context['subject_name'] ?? '',
            'marks' => $context['marks'] ?? '',
            'grade' => $context['grade'] ?? '',
            'remarks' => $context['remarks'] ?? '',
            'incident_date' => $context['incident_date'] ?? '',
            'incident_type' => $context['incident_type'] ?? '',
            'incident_description' => $context['incident_description'] ?? '',
            'treatment' => $context['treatment'] ?? '',
            'symptoms' => $context['symptoms'] ?? '',
            'action_taken' => $context['action_taken'] ?? '',
            'fee_balance' => $context['fee_balance'] ?? '',
            'fee_total' => $context['fee_total'] ?? '',
            'fee_paid' => $context['fee_paid'] ?? '',
            'attendance_date' => $context['attendance_date'] ?? '',
            'attendance_status' => $context['attendance_status'] ?? '',
            'term' => $context['term'] ?? '',
        ];

        return $replacements;
    }
}
