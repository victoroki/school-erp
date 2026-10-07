<?php

namespace App\Services;

use App\Models\AuditTrail;
use App\Models\PurchaseOrder;
use App\Models\Requisition;
use Illuminate\Support\Facades\DB;

/**
 * Requisition approval with automatic purchase-order generation.
 *
 * The procurement flow this implements:
 *
 *   Requisition (Pending) → Approved + Purchase Order (Pending_Approval)
 *                                        ↘ Rejected — nothing else happens
 *
 * Every approved requisition must end up with exactly one linked PO. The PO is
 * generated from the requisition's items (quantity and estimated price) rather
 * than retyped by hand, which removes both the transcription work and the
 * transcription errors. The supplier is deliberately NOT guessed: choosing a
 * vendor is a procurement decision the approver makes on the approval form.
 *
 * Direct PO creation (PurchaseOrderController::store) remains available for
 * purchases that never went through a requisition; the linkage column is
 * nullable for exactly that reason.
 */
class RequisitionApprovalService
{
    /**
     * Approve the requisition and generate its purchase order.
     *
     * @param  Requisition  $requisition
     * @param  int  $supplierId  chosen by the approver
     * @param  string|null  $reason  optional approval note, stored on the PO
     * @return PurchaseOrder the generated, linked purchase order
     *
     * @throws \RuntimeException if the requisition is not in an approvable state
     */
    public function approveAndCreateOrder(Requisition $requisition, int $supplierId, ?string $reason = null): PurchaseOrder
    {
        // Idempotence guard: an approval POST can be double-submitted (double
        // click, retry). A second run must not mint a second PO.
        if ($requisition->status !== 'Pending') {
            throw new \RuntimeException(
                "Requisition {$requisition->requisition_number} is already {$requisition->status} and cannot be approved again."
            );
        }

        // An empty requisition would produce an empty PO — refuse loudly
        // rather than generating a document with no lines.
        $requisition->loadMissing('items');

        if ($requisition->items->isEmpty()) {
            throw new \RuntimeException(
                "Requisition {$requisition->requisition_number} has no line items and cannot be turned into a purchase order."
            );
        }

        [$requisition, $purchaseOrder] = DB::transaction(function () use ($requisition, $supplierId, $reason) {
            $subTotal = 0.0;

            foreach ($requisition->items as $item) {
                $subTotal += (float) $item->quantity_needed * (float) $item->estimated_price;
            }

            // Same VAT treatment as PurchaseOrderController::store (16%).
            $tax = $subTotal * 0.16;

            // Retries with a fresh number if the unique index on po_number
            // refuses a collision, so two simultaneous approvals cannot fail.
            $purchaseOrder = PurchaseOrder::createWithFreshNumber(function ($poNumber) use ($requisition, $supplierId, $subTotal, $tax, $reason) {
                return PurchaseOrder::create([
                    'po_number' => $poNumber,
                    // The audit chain link: this PO came from this requisition.
                    'requisition_id' => $requisition->requisition_id,
                    'supplier_id' => $supplierId,
                    'order_date' => now()->toDateString(),
                    // Deliver by the date the requester asked for, when still in
                    // the future; otherwise the day after ordering.
                    'expected_delivery_date' => $requisition->date_needed && $requisition->date_needed->isFuture()
                        ? $requisition->date_needed->toDateString()
                        : now()->addDay()->toDateString(),
                    'delivery_address' => 'School Main Store',
                    'status' => 'Pending_Approval',
                    'sub_total' => round($subTotal, 2),
                    'tax_amount' => round($tax, 2),
                    'grand_total' => round($subTotal + $tax, 2),
                    'special_instructions' => $reason,
                ]);
            });

            foreach ($requisition->items as $item) {
                $purchaseOrder->items()->create([
                    'item_id' => $item->item_id,
                    'item_name' => $item->item_name,
                    'description' => $item->purpose,
                    'quantity' => $item->quantity_needed,
                    'unit_price' => $item->estimated_price,
                    'total_price' => round($item->quantity_needed * $item->estimated_price, 2),
                ]);
            }

            $requisition->update([
                'status' => 'Approved',
                'approved_by' => auth()->id(),
                'approved_date' => now(),
            ]);

            AuditTrail::log('Purchase Order', 'GENERATED_FROM_REQUISITION', $purchaseOrder->po_id, null, [
                'po_number' => $purchaseOrder->po_number,
                'requisition_id' => $requisition->requisition_id,
                'requisition_number' => $requisition->requisition_number,
                'supplier_id' => $supplierId,
                'grand_total' => $purchaseOrder->grand_total,
            ]);

            return [$requisition, $purchaseOrder];
        });

        AuditTrail::log('Requisition', 'APPROVE', $requisition->requisition_id, ['status' => 'Pending'], $requisition->toArray());

        return $purchaseOrder;
    }

    public function reject(Requisition $requisition, ?string $reason = null): void
    {
        if ($requisition->status !== 'Pending') {
            throw new \RuntimeException(
                "Requisition {$requisition->requisition_number} is already {$requisition->status} and cannot be rejected."
            );
        }

        DB::transaction(function () use ($requisition, $reason) {
            $requisition->update([
                'status' => 'Rejected',
                'rejected_reason' => $reason,
            ]);

            AuditTrail::log('Requisition', 'REJECT', $requisition->requisition_id, ['status' => 'Pending'], $requisition->toArray());
        });
    }

    /**
     * Requisition numbers are unique; the sequence is retried against the
     * unique index so a collision never fails the save.
     */
    public static function nextRequisitionNumber(): string
    {
        return Requisition::generateNumber();
    }
}
