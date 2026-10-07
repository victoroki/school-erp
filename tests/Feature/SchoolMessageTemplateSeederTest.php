<?php

namespace Tests\Feature;

use App\Models\EmailTemplate;
use App\Models\SmsTemplate;
use App\Models\TemplateCategory;
use App\Services\Communication\TemplateRenderer;
use Database\Seeders\SchoolMessageTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The seeded SMS/Email template library.
 *
 * These two tables shipped empty, so the Compose screen offered nothing to send
 * until a school typed every message by hand. The seeder is the fix, and it has
 * to keep three promises for a template to be safe to actually send:
 *
 *  - every placeholder it uses is one TemplateRenderer can substitute
 *  - an SMS body fits in one 160-character GSM segment, so it bills as one
 *  - running it twice does not duplicate rows or reset usage counters
 */
class SchoolMessageTemplateSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SchoolMessageTemplateSeeder::class);
    }

    /** The only tokens render() will substitute. Anything else is sent verbatim. */
    private function supportedPlaceholders(): array
    {
        return array_keys(TemplateRenderer::getAvailablePlaceholders());
    }

    // ─── The library actually exists ──────────────────────────────────────

    public function test_both_libraries_are_seeded(): void
    {
        $this->assertGreaterThanOrEqual(15, SmsTemplate::count(), 'No SMS templates were seeded.');
        $this->assertGreaterThanOrEqual(15, EmailTemplate::count(), 'No email templates were seeded.');
    }

    public function test_every_seeded_template_is_active_so_compose_can_offer_it(): void
    {
        // compose.blade.php filters on status = 'active'; a draft row is
        // invisible there, which is how an empty dropdown happens.
        $this->assertSame(0, SmsTemplate::where('status', '!=', 'active')->count());
        $this->assertSame(0, EmailTemplate::where('status', '!=', 'active')->count());
    }

    public function test_every_seeded_template_has_a_title_a_category_and_a_body(): void
    {
        foreach (SmsTemplate::all() as $t) {
            $this->assertNotEmpty($t->title, 'An SMS template has no title.');
            $this->assertNotEmpty($t->content, "SMS template '{$t->title}' has no content.");
            $this->assertNotEmpty($t->category, "SMS template '{$t->title}' has no category.");
            $this->assertLessThanOrEqual(
                100,
                mb_strlen($t->title),
                "SMS template title exceeds the varchar(100) column."
            );
        }

        foreach (EmailTemplate::all() as $t) {
            $this->assertNotEmpty($t->title);
            $this->assertNotEmpty($t->content);
            $this->assertNotEmpty($t->category);
            $this->assertNotEmpty($t->subject, "Email template '{$t->title}' has no subject.");
            $this->assertLessThanOrEqual(255, mb_strlen($t->subject));
        }
    }

    public function test_template_categories_are_seeded_for_both_channels(): void
    {
        $categories = TemplateCategory::pluck('name');

        $this->assertTrue($categories->contains('General Announcement'));
        $this->assertTrue($categories->contains('Fees & Finance'));
        $this->assertTrue($categories->contains('Medical & Welfare'));

        foreach (TemplateCategory::all() as $category) {
            $this->assertSame('Both', $category->type, "'{$category->name}' should apply to SMS and email.");
        }
    }

    public function test_every_template_category_is_one_that_was_seeded(): void
    {
        $known = TemplateCategory::pluck('name');

        foreach (SmsTemplate::pluck('category')->merge(EmailTemplate::pluck('category')) as $category) {
            $this->assertTrue(
                $known->contains($category),
                "Template category '{$category}' has no matching row in template_categories."
            );
        }
    }

    // ─── Placeholders must be real, or they go out as literal braces ──────

    public function test_no_template_uses_a_placeholder_the_renderer_cannot_substitute(): void
    {
        $supported = $this->supportedPlaceholders();

        foreach (SmsTemplate::all()->concat(EmailTemplate::all()) as $template) {
            preg_match_all('/\{([a-z_]+)\}/', $template->content, $matches);
            preg_match_all('/\{([a-z_]+)\}/', (string) $template->subject, $subjectMatches);

            foreach (array_merge($matches[1], $subjectMatches[1]) as $token) {
                $this->assertContains(
                    $token,
                    $supported,
                    "Template '{$template->title}' uses {{{$token}}}, which TemplateRenderer does not "
                    . 'substitute. It would be sent to the recipient as literal text.'
                );
            }
        }
    }

    public function test_no_template_uses_the_placeholder_names_the_ui_advertises_that_do_not_work(): void
    {
        // compose.blade.php and the template forms tell the user these are valid.
        // They are not: {name} is the recipient-array key, and there is no {date}
        // or bare {class} in the renderer's list.
        $bogus = ['name', 'class', 'date', 'exam_name', 'result_summary', 'approved_at'];

        foreach (SmsTemplate::all()->concat(EmailTemplate::all()) as $template) {
            preg_match_all('/\{([a-z_]+)\}/', $template->content, $matches);

            foreach ($matches[1] as $token) {
                $this->assertNotContains(
                    $token,
                    $bogus,
                    "Template '{$template->title}' uses the non-functional {{{$token}}}."
                );
            }
        }
    }

    public function test_broadcast_templates_only_use_placeholders_available_to_every_recipient_group(): void
    {
        // SendBulkMessage fills student_name/student_class for All Students,
        // Class and Class Section only. The All Parents and All Staff groups
        // carry no per-person keys, so those two keys render as an empty string
        // and the parent gets a message with a hole in it.
        $studentOnly = ['student_name', 'student_first_name', 'student_class'];

        foreach (SmsTemplate::all()->concat(EmailTemplate::all()) as $template) {
            $isBroadcast = in_array($template->category, [
                'General Announcement',
                'Fees & Finance',
                'Emergency & Safety',
            ], true);

            if (! $isBroadcast) {
                continue;
            }

            preg_match_all('/\{([a-z_]+)\}/', $template->content, $matches);

            foreach ($studentOnly as $token) {
                $this->assertNotContains(
                    $token,
                    $matches[1],
                    "Broadcast template '{$template->title}' uses {{{$token}}}, which the All Parents "
                    . 'and All Staff recipient groups do not supply.'
                );
            }

            $declared = array_filter(array_map('trim', explode(',', (string) $template->variables)));

            $this->assertContains(
                'school_name',
                $declared,
                "Broadcast template '{$template->title}' should at least use {school_name}, the one key "
                . 'every recipient group resolves.'
            );
        }
    }

    public function test_the_declared_variables_column_matches_the_placeholders_actually_used(): void
    {
        // variables is shown in the SMS/Email template list, so it has to be
        // truthful rather than decorative.
        foreach (SmsTemplate::all()->concat(EmailTemplate::all()) as $template) {
            preg_match_all('/\{([a-z_]+)\}/', $template->content, $matches);

            $declared = array_filter(array_map('trim', explode(',', (string) $template->variables)));

            $this->assertNotEmpty($declared, "Template '{$template->title}' declares no variables.");

            foreach (array_unique($matches[1]) as $token) {
                $this->assertContains(
                    $token,
                    $declared,
                    "Template '{$template->title}' uses {{{$token}}} but does not list it in variables."
                );
            }

            foreach ($declared as $token) {
                $this->assertContains(
                    $token,
                    $matches[1],
                    "Template '{$template->title}' lists {{{$token}}} in variables but never uses it."
                );
            }
        }
    }

    // ─── SMS has to fit in one segment ────────────────────────────────────

    public function test_every_sms_body_fits_a_single_160_character_gsm_segment(): void
    {
        // Kenya SMS is billed per segment. One shilling over the limit and the
        // message costs double, for every recipient. Nothing in the app enforces
        // this: sms_templates.content is an unbounded text column and neither
        // provider inspects length, so it has to be held at seed time.
        foreach (SmsTemplate::all() as $template) {
            $length = mb_strlen($template->content);

            $this->assertLessThanOrEqual(
                160,
                $length,
                "SMS template '{$template->title}' is {$length} characters, so it bills as "
                . ceil($length / 160) . ' segments.'
            );
        }
    }

    // ─── Rendering actually works ─────────────────────────────────────────

    public function test_a_per_child_template_renders_for_a_student_recipient(): void
    {
        $template = SmsTemplate::where('title', 'Absence Alert')->firstOrFail();

        $rendered = TemplateRenderer::render($template->content, [
            'student_name' => 'Amina Wanjiru',
            'student_class' => 'Grade 4 East',
        ]);

        $this->assertStringContainsString('Amina Wanjiru', $rendered);
        $this->assertStringContainsString('Grade 4 East', $rendered);
        $this->assertStringNotContainsString('{', $rendered, 'An unsubstituted token survived rendering.');
    }

    public function test_a_broadcast_template_renders_cleanly_for_a_parent_recipient(): void
    {
        // The All Parents group supplies no per-person keys at all.
        $template = SmsTemplate::where('title', 'Fee Reminder')->firstOrFail();

        $rendered = TemplateRenderer::render($template->content, []);

        $this->assertStringNotContainsString('{', $rendered, 'A broadcast template left a raw token behind.');
        $this->assertStringContainsString('fees for this term are due', $rendered);
    }

    public function test_an_email_body_survives_rendering_and_keeps_its_layout(): void
    {
        $template = EmailTemplate::where('title', 'Absence Alert')->firstOrFail();

        $rendered = TemplateRenderer::render($template->content, [
            'student_name' => 'Amina Wanjiru',
            'student_class' => 'Grade 4 East',
        ]);

        $this->assertStringNotContainsString('{', $rendered);
        $this->assertStringContainsString('Amina Wanjiru', $rendered);
        // Mail::raw() sends exactly this, so the paragraph breaks must survive.
        $this->assertStringContainsString("\n\n", $rendered);
        $this->assertStringContainsString("\n\nKind regards,", $rendered);
    }

    // ─── Seeding must be repeatable ───────────────────────────────────────

    public function test_reseeding_does_not_duplicate_or_clobber_templates(): void
    {
        $sms = SmsTemplate::count();
        $email = EmailTemplate::count();
        $categories = TemplateCategory::count();

        $this->seed(SchoolMessageTemplateSeeder::class);

        $this->assertSame($sms, SmsTemplate::count(), 'Reseeding duplicated SMS templates.');
        $this->assertSame($email, EmailTemplate::count(), 'Reseeding duplicated email templates.');
        $this->assertSame($categories, TemplateCategory::count(), 'Reseeding duplicated categories.');
    }

    public function test_reseeding_does_not_reset_usage_counters(): void
    {
        $template = SmsTemplate::where('title', 'Fee Reminder')->firstOrFail();
        DB::table('sms_templates')->where('template_id', $template->template_id)->update(['usage_count' => 7]);

        $this->seed(SchoolMessageTemplateSeeder::class);

        $this->assertSame(
            7,
            (int) SmsTemplate::find($template->template_id)->usage_count,
            'Reseeding wiped a usage counter the school had built up.'
        );
    }

    public function test_reseeding_brings_an_edited_template_back_to_the_shipped_wording(): void
    {
        $template = SmsTemplate::where('title', 'Emergency School Closure')->firstOrFail();
        $template->update(['content' => 'edited by the school']);

        $this->seed(SchoolMessageTemplateSeeder::class);

        $this->assertStringContainsString(
            'School is closed today',
            SmsTemplate::find($template->template_id)->content,
            'The seeder no longer restores its own shipped wording.'
        );
    }
}
