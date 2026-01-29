<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Requisition;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventoryService
{
    /**
     * Add stock to an inventory item
     */
    public function addStock(array $data): InventoryTransaction
    {
        $item = InventoryItem::findOrFail($data['item_id']);

        // Update item quantity
        $item->increment('quantity', $data['quantity']);

        // Create transaction record
        $transaction = InventoryTransaction::create([
            'item_id' => $data['item_id'],
            'transaction_type' => 'purchase', // Using purchase for stock in
            'quantity' => $data['quantity'],
            'transaction_date' => $data['transaction_date'] ?? now(),
            'remarks' => $data['remarks'] ?? '',
            'handled_by' => $data['handled_by'] ?? null,
            'issued_to' => null // Not issued to anyone for stock in
        ]);

        return $transaction;
    }

    /**
     * Issue stock from inventory
     */
    public function issueStock(array $data): InventoryTransaction
    {
        $item = InventoryItem::findOrFail($data['item_id']);

        // Check if sufficient quantity is available
        if ($item->quantity < $data['quantity']) {
            throw new \Exception("Insufficient stock. Available: {$item->quantity}, Requested: {$data['quantity']}");
        }

        // Update item quantity
        $item->decrement('quantity', $data['quantity']);

        // Create transaction record
        $transaction = InventoryTransaction::create([
            'item_id' => $data['item_id'],
            'transaction_type' => 'issue',
            'quantity' => $data['quantity'],
            'transaction_date' => $data['transaction_date'] ?? now(),
            'remarks' => $data['remarks'] ?? '',
            'handled_by' => $data['handled_by'] ?? null,
            'issued_to' => $data['issued_to'] ?? null
        ]);

        return $transaction;
    }

    /**
     * Adjust stock (for damaged, lost, or physical count corrections)
     */
    public function adjustStock(array $data): InventoryTransaction
    {
        $item = InventoryItem::findOrFail($data['item_id']);

        // Calculate difference
        $difference = $data['actual_quantity'] - $item->quantity;

        // Update item quantity to match actual quantity
        $item->update(['quantity' => $data['actual_quantity']]);

        // Create transaction record
        $transaction = InventoryTransaction::create([
            'item_id' => $data['item_id'],
            'transaction_type' => 'adjustment',
            'quantity' => abs($difference),
            'transaction_date' => $data['transaction_date'] ?? now(),
            'remarks' => $data['remarks'] ?? '',
            'handled_by' => $data['handled_by'] ?? null,
            'issued_to' => null
        ]);

        return $transaction;
    }

    /**
     * Create a new requisition
     */
    public function createRequisition(array $data): Requisition
    {
        $requisitionNumber = $this->generateRequisitionNumber();

        $requisition = Requisition::create([
            'requisition_number' => $requisitionNumber,
            'requested_by' => $data['requested_by'],
            'department_id' => $data['department_id'],
            'date_needed' => $data['date_needed'],
            'priority' => $data['priority'],
            'justification' => $data['justification'],
            'status' => 'Pending'
        ]);

        // Create requisition items
        foreach ($data['items'] as $itemData) {
            $requisition->items()->create([
                'item_id' => $itemData['item_id'] ?? null,
                'item_name' => $itemData['item_name'],
                'quantity_needed' => $itemData['quantity_needed'],
                'estimated_price' => $itemData['estimated_price'],
                'purpose' => $itemData['purpose'] ?? ''
            ]);
        }

        // Update total cost
        $requisition->update([
            'total_cost' => $requisition->items->sum(function($item) {
                return $item->quantity_needed * $item->estimated_price;
            })
        ]);

        return $requisition;
    }

    /**
     * Approve a requisition
     */
    public function approveRequisition(int $requisitionId, int $approvedById): Requisition
    {
        $requisition = Requisition::findOrFail($requisitionId);

        if ($requisition->status !== 'Pending') {
            throw new \Exception('Requisition is not in pending status');
        }

        $requisition->update([
            'status' => 'Approved',
            'approved_by' => $approvedById,
            'approved_date' => now()
        ]);

        return $requisition;
    }

    /**
     * Reject a requisition
     */
    public function rejectRequisition(int $requisitionId, int $approvedById, string $reason): Requisition
    {
        $requisition = Requisition::findOrFail($requisitionId);

        if ($requisition->status !== 'Pending') {
            throw new \Exception('Requisition is not in pending status');
        }

        $requisition->update([
            'status' => 'Rejected',
            'approved_by' => $approvedById,
            'approved_date' => now(),
            'rejected_reason' => $reason
        ]);

        return $requisition;
    }

    /**
     * Create a purchase order
     */
    public function createPurchaseOrder(array $data): PurchaseOrder
    {
        $poNumber = $this->generatePONumber();

        $purchaseOrder = PurchaseOrder::create([
            'po_number' => $poNumber,
            'supplier_id' => $data['supplier_id'],
            'order_date' => $data['order_date'] ?? now(),
            'expected_delivery_date' => $data['expected_delivery_date'],
            'delivery_address' => $data['delivery_address'] ?? config('app.delivery_address', ''),
            'terms_conditions' => $data['terms_conditions'] ?? '',
            'special_instructions' => $data['special_instructions'] ?? '',
            'status' => $data['status'] ?? 'Draft'
        ]);

        // Calculate totals and create items
        $subTotal = 0;
        foreach ($data['items'] as $itemData) {
            $totalPrice = $itemData['quantity'] * $itemData['unit_price'];
            $subTotal += $totalPrice;

            $purchaseOrder->items()->create([
                'item_id' => $itemData['item_id'] ?? null,
                'item_name' => $itemData['item_name'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'],
                'total_price' => $totalPrice,
                'description' => $itemData['description'] ?? ''
            ]);
        }

        // Calculate tax and grand total
        $taxAmount = ($subTotal * ($data['tax_rate'] ?? 0)) / 100;
        $deliveryCharges = $data['delivery_charges'] ?? 0;
        $grandTotal = $subTotal + $taxAmount + $deliveryCharges;

        $purchaseOrder->update([
            'sub_total' => $subTotal,
            'tax_amount' => $taxAmount,
            'delivery_charges' => $deliveryCharges,
            'grand_total' => $grandTotal
        ]);

        return $purchaseOrder;
    }

    /**
     * Receive items for a purchase order
     */
    public function receivePurchaseOrderItems(int $poId, array $receivedItems, int $receivedById): PurchaseOrder
    {
        $purchaseOrder = PurchaseOrder::findOrFail($poId);

        if (!in_array($purchaseOrder->status, ['Sent', 'Partially_Received'])) {
            throw new \Exception('Purchase order is not ready for receiving');
        }

        foreach ($receivedItems as $itemId => $quantity) {
            $poItem = $purchaseOrder->items()->where('po_item_id', $itemId)->firstOrFail();
            
            // Update received quantity
            $poItem->increment('quantity_received', $quantity);
            $poItem->update(['received_at' => now()]);

            // If item exists in inventory, update stock
            if ($poItem->item_id) {
                $inventoryItem = InventoryItem::findOrFail($poItem->item_id);
                
                // Add to stock
                $inventoryItem->increment('quantity', $quantity);

                // Create transaction record
                InventoryTransaction::create([
                    'item_id' => $inventoryItem->item_id,
                    'transaction_type' => 'purchase',
                    'quantity' => $quantity,
                    'transaction_date' => now(),
                    'remarks' => "Received from PO: {$purchaseOrder->po_number}",
                    'handled_by' => $receivedById
                ]);
            }
        }

        // Update PO status
        $totalItems = $purchaseOrder->items->count();
        $receivedItemsCount = $purchaseOrder->items->filter(function($item) {
            return $item->quantity_received > 0;
        })->count();

        if ($receivedItemsCount == $totalItems) {
            $status = 'Fully_Received';
        } else {
            $status = 'Partially_Received';
        }

        $purchaseOrder->update([
            'status' => $status,
            'received_by' => $receivedById,
            'received_date' => now()
        ]);

        return $purchaseOrder;
    }

    /**
     * Generate requisition number
     */
    private function generateRequisitionNumber(): string
    {
        $year = date('Y');
        $lastRequisition = Requisition::orderBy('requisition_id', 'desc')->first();
        $number = $lastRequisition ? ((int)substr($lastRequisition->requisition_number, -4)) + 1 : 1;
        
        return sprintf("REQ-%s-%04d", $year, $number);
    }

    /**
     * Generate PO number
     */
    private function generatePONumber(): string
    {
        $year = date('Y');
        $lastPO = PurchaseOrder::orderBy('po_id', 'desc')->first();
        $number = $lastPO ? ((int)substr($lastPO->po_number, -4)) + 1 : 1;
        
        return sprintf("PO-%s-%04d", $year, $number);
    }

    /**
     * Get low stock alert items
     */
    public function getLowStockAlerts(): \Illuminate\Database\Eloquent\Collection
    {
        return InventoryItem::whereColumn('quantity', '<=', 'minimum_quantity')
            ->where('minimum_quantity', '>', 0)
            ->with(['category', 'supplier'])
            ->get();
    }

    /**
     * Get out of stock items
     */
    public function getOutOfStockItems(): \Illuminate\Database\Eloquent\Collection
    {
        return InventoryItem::where('quantity', 0)
            ->with(['category', 'supplier'])
            ->get();
    }
}