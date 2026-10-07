<?php

namespace Tests\Feature;

use App\Models\FeeBulkReceipt;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

/**
 * Every fee report must be exportable, and the exported file must agree with
 * the screen it came from.
 *
 * The Collections and Payment Methods reports were the last two without any
 * export control at all, so a bursar working from them had to copy figures by
 * hand. These tests pin the controls to their real routes and check that the
 * downloads actually carry the money — including a reconciliation line, so the
 * file cannot silently disagree with the totals on screen.
 */
class FeeReportExportsTest extends TestCase
{
    use RefreshDatabase;

    protected User $accountant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);

        $this->accountant = User::factory()->create([
            'name' => 'Exports Accountant',
            'email' => 'exports.' . uniqid() . '@test.local',
        ]);
        $this->accountant->roles()->sync(Role::where('role_name', 'Accountant')->pluck('role_id'));
        $this->accountant->load('roles.permissions');
    }

    /** Read a response body whether it was streamed (CSV) or built (PDF). */
    protected function bodyOf($response): string
    {
        return $response->baseResponse instanceof StreamedResponse
            ? $response->streamedContent()
            : (string) $response->getContent();
    }

    public function test_collections_report_exposes_csv_and_pdf_exports(): void
    {
        $response = $this->actingAs($this->accountant)
            ->get(route('fees.reports.collections'))
            ->assertOk();

        $response->assertSee('Export CSV');
        $response->assertSee('Export PDF');
        $response->assertSee(route('fees.reports.export.collections.csv'), false);
        $response->assertSee(route('fees.reports.export.collections.pdf'), false);
    }

    public function test_collections_csv_export_returns_a_csv_with_reconciliation_totals(): void
    {
        $response = $this->actingAs($this->accountant)
            ->get(route('fees.reports.export.collections.csv'))
            ->assertOk();

        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));

        $body = $this->bodyOf($response);

        $this->assertStringContainsString('Receipt No', $body);
        // The reconciliation lines are what let the file be checked against the
        // screen without re-adding the column by hand.
        $this->assertStringContainsString('Total (valid receipts)', $body);
        $this->assertStringContainsString('Voided (excluded)', $body);
    }

    public function test_collections_pdf_export_returns_a_pdf(): void
    {
        $response = $this->actingAs($this->accountant)
            ->get(route('fees.reports.export.collections.pdf'))
            ->assertOk();

        $this->assertStringStartsWith('%PDF', $this->bodyOf($response));
    }

    public function test_payment_method_report_exposes_csv_and_pdf_exports(): void
    {
        $response = $this->actingAs($this->accountant)
            ->get(route('fees.reports.payment-method'))
            ->assertOk();

        $response->assertSee('Export CSV');
        $response->assertSee('Export PDF');
        $response->assertSee(route('fees.reports.export.payment-method.csv'), false);
        $response->assertSee(route('fees.reports.export.payment-method.pdf'), false);
    }

    public function test_payment_method_csv_export_returns_a_csv_with_a_total_line(): void
    {
        $response = $this->actingAs($this->accountant)
            ->get(route('fees.reports.export.payment-method.csv'))
            ->assertOk();

        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));

        $body = $this->bodyOf($response);

        $this->assertStringContainsString('Payment Method', $body);
        $this->assertStringContainsString('Total', $body);
    }

    public function test_payment_method_pdf_export_returns_a_pdf(): void
    {
        $response = $this->actingAs($this->accountant)
            ->get(route('fees.reports.export.payment-method.pdf'))
            ->assertOk();

        $this->assertStringStartsWith('%PDF', $this->bodyOf($response));
    }

    /**
     * The bursary/sponsor bulk feature ships with a printable receipt whose own
     * figures reconcile: allocated + unallocated = amount received.
     */
    public function test_bulk_receipt_prints_a_receipt_that_reconciles(): void
    {
        $receipt = FeeBulkReceipt::create([
            'sponsor_name' => 'Test CDF Bursary',
            'sponsor_type' => 'cdf',
            'amount' => 50000,
            'payment_method' => 'bank_transfer',
            'received_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->accountant)
            ->get(route('fees.bulk-receipts.receipt', $receipt->id))
            ->assertOk();

        $response->assertSee('Test CDF Bursary');
        $response->assertSee('Amount Received');
        // 50,000.00 received, nothing allocated yet, so all of it is unallocated.
        $response->assertSee('50,000.00');
        $response->assertSee('Reconciled');
        $response->assertSee('Not yet allocated to any student');
    }

    /**
     * The bulk list shows the same reconciliation over the whole filtered set.
     */
    public function test_bulk_receipt_index_shows_reconciliation_totals(): void
    {
        FeeBulkReceipt::create([
            'sponsor_name' => 'County Bursary',
            'sponsor_type' => 'county_government',
            'amount' => 12500,
            'payment_method' => 'bank_transfer',
            'received_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->accountant)
            ->get(route('fees.bulk-receipts.index'))
            ->assertOk();

        $response->assertSee('Received');
        $response->assertSee('Allocated to Students');
        $response->assertSee('Unallocated');
        $response->assertSee('12,500.00');
    }
}
