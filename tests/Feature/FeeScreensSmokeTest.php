<?php

namespace Tests\Feature;

use App\Models\FeeCategory;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 2 screen verification.
 *
 * The audit found four fee screens that were broken or incomplete, and three of
 * them had NO test coverage at all — which is why they stayed broken. This suite
 * renders each one and checks the specific thing that was wrong:
 *
 *  - the Payment Method drill-down threw "Undefined variable $grandTotal" (500)
 *    and the $byDay data it passed was never rendered;
 *  - the Receipt Register had no print/export control, and its export endpoint
 *    loaded a view that did not exist and had no route;
 *  - the Discount Summary had no coverage whatsoever.
 */
class FeeScreensSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected User $accountant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);

        $this->accountant = User::factory()->create(['name' => 'Screens Accountant', 'email' => 'screens.' . uniqid() . '@test.local']);
        $this->accountant->roles()->sync(Role::where('role_name', 'Accountant')->pluck('role_id'));
        $this->accountant->load('roles.permissions');
    }

    public function test_payment_method_report_renders_the_grouped_view(): void
    {
        $this->actingAs($this->accountant)
            ->get(route('fees.reports.payment-method'))
            ->assertOk()
            ->assertSee('Collection by Method');
    }

    /**
     * The report's own drill-down link used to 500 because the detail branch of
     * the controller did not pass $grandTotal, and the daily breakdown it did
     * pass was never rendered.
     */
    public function test_payment_method_drilldown_renders_the_daily_breakdown(): void
    {
        $this->actingAs($this->accountant)
            ->get(route('fees.reports.payment-method', ['detail' => 1, 'payment_method' => 'cash']))
            ->assertOk()
            ->assertSee('Daily Trend')
            ->assertSee('Grand Total');
    }

    public function test_receipt_register_renders_with_a_print_export_control(): void
    {
        $response = $this->actingAs($this->accountant)
            ->get(route('fees.reports.receipt-register'))
            ->assertOk();

        $response->assertSee('Print / Export PDF');

        // The control must point at the real export route.
        $response->assertSee(route('fees.reports.export.receipt-register.pdf'), false);
    }

    /** The export endpoint must actually produce a PDF (it used to be dead code). */
    public function test_receipt_register_export_returns_a_pdf(): void
    {
        $response = $this->actingAs($this->accountant)
            ->get(route('fees.reports.export.receipt-register.pdf'))
            ->assertOk();

        // The export may be returned as a normal response or streamed, depending
        // on how the PDF facade builds it — read whichever one it is, because
        // TestResponse::streamedContent() fails outright on a non-streamed reply.
        $body = $response->baseResponse instanceof \Symfony\Component\HttpFoundation\StreamedResponse
            ? $response->streamedContent()
            : (string) $response->getContent();

        // A rendered PDF starts with the %PDF magic number.
        $this->assertStringStartsWith('%PDF', $body);
    }

    /** The Discount Summary had no test coverage at all. */
    public function test_discount_summary_renders(): void
    {
        $this->actingAs($this->accountant)
            ->get(route('fees.reports.discount-summary'))
            ->assertOk();
    }

    public function test_expected_revenue_and_assignment_status_render(): void
    {
        $this->actingAs($this->accountant)
            ->get(route('fees.reports.expected-revenue'))
            ->assertOk();

        $this->actingAs($this->accountant)
            ->get(route('fees.reports.assignment-status'))
            ->assertOk();
    }

    public function test_arrears_and_collections_reports_render(): void
    {
        $this->actingAs($this->accountant)
            ->get(route('fees.arrears.index'))
            ->assertOk();

        $this->actingAs($this->accountant)
            ->get(route('fees.reports.collections'))
            ->assertOk();
    }

    /**
     * Fee Category edit used to post to `fee-categories.update`, but the resource
     * is named `feeCategories`, so opening Edit threw RouteNotFoundException and
     * a category could never be edited once created.
     */
    public function test_fee_category_edit_workflow_saves_and_shows_the_new_value(): void
    {
        $category = FeeCategory::create(['name' => 'Before Edit', 'type' => 'mandatory']);

        $this->actingAs($this->accountant)
            ->get(route('feeCategories.edit', $category->category_id))
            ->assertOk()
            ->assertSee('Before Edit');

        $this->actingAs($this->accountant)
            ->patch(route('feeCategories.update', $category->category_id), [
                'name' => 'After Edit',
                'code' => 'AFTER',
                'type' => 'optional',
                'status' => 'active',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('fee_categories', [
            'category_id' => $category->category_id,
            'name' => 'After Edit',
        ]);

        $this->actingAs($this->accountant)
            ->get(route('feeCategories.edit', $category->category_id))
            ->assertOk()
            ->assertSee('After Edit');
    }

    /**
     * The two actions that move or void money must ask before they run.
     *
     * "Remind me" is not authorization — the backend guard is asserted by
     * FeeAuthorizationTest — but a single stray click must not pay money out or
     * void a receipt.
     */
    public function test_refund_completion_asks_for_confirmation(): void
    {
        $view = file_get_contents(resource_path('views/fee_management/refunds/show.blade.php'));

        // The Complete (money out) action posts to fees.refunds.complete.
        $this->assertStringContainsString('fees.refunds.complete', $view);
        $this->assertGreaterThanOrEqual(
            2,
            substr_count($view, 'confirm('),
            'Both Reject and the money-moving Complete action must ask for confirmation.'
        );
    }

    public function test_adjustment_approval_and_rejection_ask_for_confirmation(): void
    {
        $view = file_get_contents(resource_path('views/fee_management/adjustments/show.blade.php'));

        $this->assertStringContainsString('fees.adjustments.approve', $view);
        $this->assertStringContainsString('fees.adjustments.reject', $view);
        $this->assertGreaterThanOrEqual(
            2,
            substr_count($view, 'confirm('),
            'Approving rewrites a student\'s fee and rejecting cancels the request: both must ask first.'
        );
    }

    /**
     * Editing a category must not require renaming it.
     *
     * FeeCategory::$rules marks name as `unique:fee_categories,name`, and the
     * update request reused those rules verbatim — so submitting the form
     * with the name unchanged failed validation and a category's type,
     * status or description could never be edited on its own.
     */
    public function test_fee_category_can_be_edited_without_changing_its_name(): void
    {
        $category = FeeCategory::create(['name' => 'Unchanged Name', 'type' => 'mandatory']);

        $this->actingAs($this->accountant)
            ->patch(route('feeCategories.update', $category->category_id), [
                'name' => 'Unchanged Name',
                'type' => 'optional',
                'status' => 'inactive',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('feeCategories.index'));

        $this->assertDatabaseHas('fee_categories', [
            'category_id' => $category->category_id,
            'name' => 'Unchanged Name',
            'type' => 'optional',
        ]);
    }

    /**
     * Payment reversal voids a receipt, so it must ask for confirmation too.
     */
    public function test_payment_reversal_asks_for_confirmation(): void
    {
        $view = file_get_contents(resource_path('views/fee_management/reverse_payment.blade.php'));

        $this->assertStringContainsString('confirm(', $view);
    }
}
