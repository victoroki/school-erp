<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use App\Models\SmsTemplate;
use App\Models\TemplateCategory;
use Illuminate\Database\Seeder;

/**
 * The starter template library a school needs before it can use Communication
 * → Compose.
 *
 * This seeds sms_templates, email_templates and template_categories — the tables
 * behind the "SMS Templates" and "Email Templates" menu items. They shipped
 * empty, so the Compose screen opened with an empty dropdown and a school had
 * nothing to send until it typed every message from scratch.
 *
 * (The other table, communication_templates, is a separate trigger-driven system
 * for automatic sends. CommunicationTemplateSeeder covers that one. The two do
 * not share a table and a row here is never read by the auto-notifier.)
 *
 * Placeholder rules — these are not free text. See TemplateRenderer:
 *
 *  1. The syntax is a single brace: {student_name}. Not {{ }}, not %name%.
 *  2. Only the 22 keys in TemplateRenderer::buildReplacements() are substituted.
 *     Any other token is sent to the recipient verbatim, braces and all, because
 *     render() only loops over that fixed list.
 *  3. A whitelisted key with no value in the context renders as an empty string,
 *     so a token never leaks as literal text — but it does leave a gap.
 *  4. Compose fills student_name and student_class for the student groups (All
 *     Students, Class, Class Section). The "All Parents" and "All Staff" groups
 *     carry no per-person keys at all, so a template aimed at everyone can only
 *     use {school_name}. Send it a {student_name} template and every parent gets
 *     a message with a hole where the name should be.
 *
 * Rule 4 is why the announcements below carry no name and the per-child notices
 * do. SMS bodies stay within one GSM segment (160 characters) so a single
 * message is billed as one segment instead of two.
 *
 * Idempotent: DatabaseSeedIntegrityTest runs DatabaseSeeder twice, so every row
 * is written with updateOrCreate on a stable key. usage_count is deliberately not
 * written — re-seeding must not reset a school's usage counters.
 */
class SchoolMessageTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCategories();

        foreach ($this->smsTemplates() as $template) {
            SmsTemplate::updateOrCreate(
                ['title' => $template['title'], 'category' => $template['category']],
                $template
            );
        }

        foreach ($this->emailTemplates() as $template) {
            EmailTemplate::updateOrCreate(
                ['title' => $template['title'], 'category' => $template['category']],
                $template
            );
        }
    }

    /**
     * The groupings shared by both libraries. The column is a plain nullable
     * string on each template table, but seeding template_categories as well
     * gives the category screens something to list.
     */
    private function seedCategories(): void
    {
        $categories = [
            ['name' => 'General Announcement', 'icon' => 'fas fa-bullhorn', 'color' => 'primary'],
            ['name' => 'Academic Progress', 'icon' => 'fas fa-graduation-cap', 'color' => 'info'],
            ['name' => 'Fees & Finance', 'icon' => 'fas fa-file-invoice-dollar', 'color' => 'success'],
            ['name' => 'Attendance', 'icon' => 'fas fa-user-clock', 'color' => 'warning'],
            ['name' => 'Medical & Welfare', 'icon' => 'fas fa-heartbeat', 'color' => 'danger'],
            ['name' => 'Discipline', 'icon' => 'fas fa-exclamation-triangle', 'color' => 'dark'],
            ['name' => 'Emergency & Safety', 'icon' => 'fas fa-exclamation-circle', 'color' => 'danger'],
        ];

        foreach ($categories as $category) {
            TemplateCategory::updateOrCreate(
                ['name' => $category['name']],
                $category + [
                    'type' => 'Both',
                    'description' => null,
                ]
            );
        }
    }

    /**
     * SMS templates, grouped by the audience that can be selected in Compose.
     *
     * The "General Announcement", "Fees & Finance" and "Emergency & Safety"
     * entries are broadcast-safe: {school_name} is the only key available when
     * the recipient group is All Parents or All Staff.
     *
     * @return array<int, array<string, string>>
     */
    private function smsTemplates(): array
    {
        $school = '{school_name}';

        return [
            // ── General Announcement (broadcast) ──────────────────────────
            [
                'title' => 'Term Holiday Notice',
                'category' => 'General Announcement',
                'content' => 'School closes for the holiday as per the academic calendar. Please collect your child\'s report card and return all school property. - ' . $school,
                'variables' => 'school_name',
                'status' => 'active',
            ],
            [
                'title' => 'Term Resuming',
                'category' => 'General Announcement',
                'content' => 'Term resumes as per the school calendar. Please report with your child\'s full kit and fees settled. - ' . $school,
                'variables' => 'school_name',
                'status' => 'active',
            ],
            [
                'title' => 'PTA Meeting Notice',
                'category' => 'General Announcement',
                'content' => 'You are invited to the PTA meeting. Kindly attend and bring any questions or concerns. Your presence is valued. - ' . $school,
                'variables' => 'school_name',
                'status' => 'active',
            ],
            [
                'title' => 'Sports Day Reminder',
                'category' => 'General Announcement',
                'content' => 'Reminder: inter-house sports day. All students in full sports kit and house colours. Report at 8:00am. - ' . $school,
                'variables' => 'school_name',
                'status' => 'active',
            ],
            [
                'title' => 'Staff Meeting Notice',
                'category' => 'General Announcement',
                'content' => 'All staff are expected for the meeting. Kindly confirm attendance to the office. - ' . $school,
                'variables' => 'school_name',
                'status' => 'active',
            ],

            // ── Emergency & Safety (broadcast) ───────────────────────────
            [
                'title' => 'Emergency School Closure',
                'category' => 'Emergency & Safety',
                'content' => 'School is closed today due to an emergency. Please keep your child at home. We will confirm once normal. - ' . $school,
                'variables' => 'school_name',
                'status' => 'active',
            ],
            [
                'title' => 'Transport Delay Alert',
                'category' => 'Emergency & Safety',
                'content' => 'The school bus is delayed due to heavy traffic. We will update once it is on the way. Thank you for your patience. - ' . $school,
                'variables' => 'school_name',
                'status' => 'active',
            ],

            // ── Fees & Finance (broadcast) ───────────────────────────────
            [
                'title' => 'Fee Reminder',
                'category' => 'Fees & Finance',
                'content' => 'Dear Parent, fees for this term are due. Kindly clear your balance or contact the bursar for a payment plan. - ' . $school,
                'variables' => 'school_name',
                'status' => 'active',
            ],
            [
                'title' => 'Final Fee Arrears Notice',
                'category' => 'Fees & Finance',
                'content' => 'Dear Parent, there is an outstanding balance on your child\'s account. Please settle it to avoid exam exclusion. - ' . $school,
                'variables' => 'school_name',
                'status' => 'active',
            ],

            // ── Attendance (per child) ───────────────────────────────────
            [
                'title' => 'Absence Alert',
                'category' => 'Attendance',
                'content' => 'Dear Parent, {student_name} (' . $this->classToken() . ') was absent today without notice. Please confirm and contact us. - ' . $school,
                'variables' => 'student_name, student_class, school_name',
                'status' => 'active',
            ],
            [
                'title' => 'Extended Absence Alert',
                'category' => 'Attendance',
                'content' => 'Dear Parent, {student_name} (' . $this->classToken() . ') has been absent for several days. Please contact the school. - ' . $school,
                'variables' => 'student_name, student_class, school_name',
                'status' => 'active',
            ],

            // ── Academic Progress (per child) ────────────────────────────
            [
                'title' => 'Exam Results Released',
                'category' => 'Academic Progress',
                'content' => 'Dear Parent, results for {student_name} (' . $this->classToken() . ') are ready. Log in to the parent portal to view the report card. - ' . $school,
                'variables' => 'student_name, student_class, school_name',
                'status' => 'active',
            ],
            [
                'title' => 'Exam Schedule Reminder',
                'category' => 'Academic Progress',
                'content' => 'Reminder: exams for {student_name} (' . $this->classToken() . ') begin as per the timetable. Revise and report early. - ' . $school,
                'variables' => 'student_name, student_class, school_name',
                'status' => 'active',
            ],
            [
                'title' => 'Outstanding Coursework',
                'category' => 'Academic Progress',
                'content' => 'Dear Parent, {student_name} (' . $this->classToken() . ') has outstanding coursework. Please support completion this week. - ' . $school,
                'variables' => 'student_name, student_class, school_name',
                'status' => 'active',
            ],
            [
                'title' => 'Commendation',
                'category' => 'Academic Progress',
                'content' => 'Congratulations to {student_name} (' . $this->classToken() . ') on outstanding performance this term. Keep it up! - ' . $school,
                'variables' => 'student_name, student_class, school_name',
                'status' => 'active',
            ],

            // ── Medical & Welfare (per child) ────────────────────────────
            [
                'title' => 'Medical Incident Alert',
                'category' => 'Medical & Welfare',
                'content' => 'Dear Parent, {student_name} (' . $this->classToken() . ') had a medical incident today. The nurse attended. Please call for details. - ' . $school,
                'variables' => 'student_name, student_class, school_name',
                'status' => 'active',
            ],

            // ── Discipline (per child) ────────────────────────────────────
            [
                'title' => 'Disciplinary Notice',
                'category' => 'Discipline',
                'content' => 'Dear Parent, {student_name} (' . $this->classToken() . ') was spoken to about conduct. Please call to discuss. - ' . $school,
                'variables' => 'student_name, student_class, school_name',
                'status' => 'active',
            ],
            [
                'title' => 'Suspension Notice',
                'category' => 'Discipline',
                'content' => 'Dear Parent, {student_name} (' . $this->classToken() . ') will be suspended as per school rules. Please call to discuss. - ' . $school,
                'variables' => 'student_name, student_class, school_name',
                'status' => 'active',
            ],
        ];
    }

    /**
     * Email templates. Same categories and the same placeholder rules, but these
     * can carry the detail an SMS cannot, so the bodies are laid out as short
     * plain-text paragraphs. The mailer is SmtpEmailProvider, which calls
     * Mail::raw() — plain text only, no HTML view is rendered for a body.
     *
     * @return array<int, array<string, string>>
     */
    private function emailTemplates(): array
    {
        $school = '{school_name}';
        $class = $this->classToken();

        $signOff = "Kind regards,\nOffice of the Head Teacher\n" . $school;

        return [
            [
                'title' => 'Term Holiday Notice',
                'category' => 'General Announcement',
                'subject' => 'School Holiday Notice',
                'content' => $this->emailBody(
                    'Dear Parent,',
                    "Thank you for your support this term. School will close for the holiday as per the published academic calendar.\n\n"
                    . "Before the break, please ensure your child:\n"
                    . "  - collects their report card\n"
                    . "  - returns all school property, including library books and sports gear\n"
                    . "  - completes any outstanding coursework\n\n"
                    . 'Classes resume as per the calendar. We wish you and your family a restful break.',
                    $signOff
                ),
                'variables' => 'school_name',
                'status' => 'active',
            ],
            [
                'title' => 'Term Resuming',
                'category' => 'General Announcement',
                'subject' => 'Term Resuming - School Calendar',
                'content' => $this->emailBody(
                    'Dear Parent,',
                    "Term resumes as per the school calendar. We look forward to welcoming the learners back.\n\n"
                    . "Please confirm before the first day that your child has:\n"
                    . "  - a full school kit and the correct uniform\n"
                    . "  - any outstanding fees settled, or a payment plan agreed with the bursar\n"
                    . "  - returned school property borrowed during the previous term",
                    $signOff
                ),
                'variables' => 'school_name',
                'status' => 'active',
            ],
            [
                'title' => 'PTA Meeting Notice',
                'category' => 'General Announcement',
                'subject' => 'Invitation to the PTA Meeting',
                'content' => $this->emailBody(
                    'Dear Parent,',
                    "You are invited to the Parent-Teacher Association meeting.\n\n"
                    . "The meeting is an opportunity to review the term's performance, discuss the coming term and raise any concerns. "
                    . "Your presence is valued, and you are welcome to send written points ahead of time if you cannot attend in person.",
                    $signOff
                ),
                'variables' => 'school_name',
                'status' => 'active',
            ],
            [
                'title' => 'Emergency School Closure',
                'category' => 'Emergency & Safety',
                'subject' => 'School Closure Notice',
                'content' => $this->emailBody(
                    'Dear Parent,',
                    "School is closed today due to an emergency.\n\n"
                    . "Please keep your child at home and do not send them to school. "
                    . "We will send a further message as soon as we are able to confirm the position, and lessons will be rescheduled accordingly.",
                    $signOff
                ),
                'variables' => 'school_name',
                'status' => 'active',
            ],
            [
                'title' => 'Transport Delay Alert',
                'category' => 'Emergency & Safety',
                'subject' => 'School Transport Delay',
                'content' => $this->emailBody(
                    'Dear Parent,',
                    "The school bus is delayed due to heavy traffic on the route.\n\n"
                    . "The vehicle is safe and in the care of the usual driver and attendant. "
                    . "We will send another update once it is on its way. Thank you for your patience.",
                    $signOff
                ),
                'variables' => 'school_name',
                'status' => 'active',
            ],
            [
                'title' => 'Fee Reminder',
                'category' => 'Fees & Finance',
                'subject' => 'Fee Payment Reminder',
                'content' => $this->emailBody(
                    'Dear Parent,',
                    "This is a reminder that school fees for the current term are due.\n\n"
                    . "The most up-to-date balance for your account is shown in the parent portal. "
                    . "If payment in full is a hardship, please contact the bursar's office to agree a payment plan before the deadline.",
                    $signOff
                ),
                'variables' => 'school_name',
                'status' => 'active',
            ],
            [
                'title' => 'Final Fee Arrears Notice',
                'category' => 'Fees & Finance',
                'subject' => 'Outstanding Fee Balance - Action Required',
                'content' => $this->emailBody(
                    'Dear Parent,',
                    "Our records show an outstanding balance on your child's account.\n\n"
                    . "Under the school's fee policy, an account in arrears may result in your child being withheld from sitting end-of-term examinations. "
                    . "Please settle the balance, or contact the bursar's office, before the deadline given below.\n\n"
                    . 'If you believe this is a billing error, reply to this message and we will review the account.',
                    $signOff
                ),
                'variables' => 'school_name',
                'status' => 'active',
            ],
            [
                'title' => 'Absence Alert',
                'category' => 'Attendance',
                'subject' => 'Absence of {student_name}',
                'content' => $this->emailBody(
                    'Dear Parent,',
                    "We are writing to let you know that {student_name} (" . $class . ") was recorded absent today without notice.\n\n"
                    . "If this was not expected, please contact the school office so we can mark the attendance correctly. "
                    . "Repeated unexplained absences affect a learner's progress and, in the case of medical absence, may require supporting documentation.",
                    $signOff
                ),
                'variables' => 'student_name, student_class, school_name',
                'status' => 'active',
            ],
            [
                'title' => 'Extended Absence Alert',
                'category' => 'Attendance',
                'subject' => 'Extended Absence of {student_name}',
                'content' => $this->emailBody(
                    'Dear Parent,',
                    "{student_name} (" . $class . ") has now been absent for several consecutive school days without notice.\n\n"
                    . "Please contact the school office today. If there is illness or a family emergency behind the absence, we would like to record it properly and arrange any catch-up work your child needs.",
                    $signOff
                ),
                'variables' => 'student_name, student_class, school_name',
                'status' => 'active',
            ],
            [
                'title' => 'Exam Results Released',
                'category' => 'Academic Progress',
                'subject' => 'Exam Results for {student_name}',
                'content' => $this->emailBody(
                    'Dear Parent,',
                    "The exam results for {student_name} (" . $class . ") have been released.\n\n"
                    . "The full report card is available in the parent portal, where you can see each subject's marks and the teacher's remarks. "
                    . "Please sign the report card and return it to the form teacher. We are happy to arrange a conversation about the results if you would like one.",
                    $signOff
                ),
                'variables' => 'student_name, student_class, school_name',
                'status' => 'active',
            ],
            [
                'title' => 'Exam Schedule Reminder',
                'category' => 'Academic Progress',
                'subject' => 'Exam Timetable Reminder',
                'content' => $this->emailBody(
                    'Dear Parent,',
                    "This is a reminder that the exam timetable for {student_name} (" . $class . ") takes effect shortly.\n\n"
                    . "The timetable is on the notice board and in the parent portal. Please help your child revise, get to school early on each exam day, and eat a proper breakfast beforehand.",
                    $signOff
                ),
                'variables' => 'student_name, student_class, school_name',
                'status' => 'active',
            ],
            [
                'title' => 'Outstanding Coursework',
                'category' => 'Academic Progress',
                'subject' => 'Outstanding Coursework for {student_name}',
                'content' => $this->emailBody(
                    'Dear Parent,',
                    "{student_name} (" . $class . ") has coursework that has not been submitted.\n\n"
                    . "Please encourage your child to complete it this week. Deadlines missed on continuous assessment work affect the final grade for the term. "
                    . "If there is a difficulty completing a piece of work, let the subject teacher know before the deadline rather than after it.",
                    $signOff
                ),
                'variables' => 'student_name, student_class, school_name',
                'status' => 'active',
            ],
            [
                'title' => 'Medical Incident Alert',
                'category' => 'Medical & Welfare',
                'subject' => 'Medical Incident involving {student_name}',
                'content' => $this->emailBody(
                    'Dear Parent,',
                    "{student_name} (" . $class . ") had a medical incident at school today and was seen by the school nurse.\n\n"
                    . "Your child is well and has been settled back into the class where possible. "
                    . "Please call the school office as soon as you can so we can talk you through what happened and confirm whether any follow-up is needed.\n\n"
                    . "We would also appreciate it if you could update us about any medical conditions or allergies we should be aware of.",
                    $signOff
                ),
                'variables' => 'student_name, student_class, school_name',
                'status' => 'active',
            ],
            [
                'title' => 'Disciplinary Notice',
                'category' => 'Discipline',
                'subject' => 'Conduct matter concerning {student_name}',
                'content' => $this->emailBody(
                    'Dear Parent,',
                    "We need to raise a matter of conduct concerning {student_name} (" . $class . ").\n\n"
                    . "The class teacher has spoken to your child and recorded the matter. We would like to speak with you so that we approach it together and agree on any support your child needs. "
                    . "Please call the school office at your earliest convenience.",
                    $signOff
                ),
                'variables' => 'student_name, student_class, school_name',
                'status' => 'active',
            ],
            [
                'title' => 'Suspension Notice',
                'category' => 'Discipline',
                'subject' => 'Suspension of {student_name}',
                'content' => $this->emailBody(
                    'Dear Parent,',
                    "In line with the school's conduct policy, {student_name} (" . $class . ") will be suspended for a period to allow the matter to be resolved.\n\n"
                    . "The period of suspension and the return date are recorded in the school's records. "
                    . "Please call the school office to discuss the matter and to arrange any catch-up work your child will miss.",
                    $signOff
                ),
                'variables' => 'student_name, student_class, school_name',
                'status' => 'active',
            ],
        ];
    }

    /**
     * Assembles a plain-text email body. The mailer is SmtpEmailProvider, which
     * calls Mail::raw(), so there is no HTML view to render and the line breaks
     * below are what the recipient actually sees.
     */
    private function emailBody(string $salutation, string $body, string $signOff): string
    {
        return implode("\n\n", [$salutation, $body, $signOff]);
    }

    private function classToken(): string
    {
        return '{student_class}';
    }
}
