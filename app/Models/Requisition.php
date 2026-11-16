<?php

namespace App\Models;

use App\Models\Concerns\GeneratesDailySequenceNumber;
use Illuminate\Database\Eloquent\Model;

class Requisition extends Model
{
    use GeneratesDailySequenceNumber;

    public $table = 'requisitions';

    protected $primaryKey = 'requisition_id';

    protected static function sequenceColumn(): string
    {
        return 'requisition_number';
    }

    protected static function sequencePrefix(): string
    {
        return 'REQ-';
    }

    /**
     * REQ-YYYYMMDD-NNN. Sequential and retried against the unique index
     * rather than random, so two requisitions raised at the same instant can
     * never share a number.
     */
    public static function generateNumber(): string
    {
        return static::nextSequenceNumber();
    }

    public $fillable = [
        'requisition_number',
        'requested_by',
        'department_id',
        'date_needed',
        'priority',
        'total_cost',
        'status',
        'justification',
        'approved_by',
        'approved_date',
        'rejected_reason',
        'fulfilled_date'
    ];

    protected $casts = [
        'requisition_number' => 'string',
        'requested_by' => 'integer',
        'department_id' => 'integer',
        'date_needed' => 'date',
        'priority' => 'string',
        'total_cost' => 'decimal:2',
        'status' => 'string',
        'justification' => 'string',
        'approved_by' => 'integer',
        'approved_date' => 'datetime',
        'rejected_reason' => 'string',
        'fulfilled_date' => 'datetime'
    ];

    public static array $rules = [
        'requisition_number' => 'required|string|unique:requisitions,requisition_number',
        'requested_by' => 'required|exists:users,id',
        'department_id' => 'required|exists:departments,department_id',
        'date_needed' => 'required|date|after_or_equal:today',
        'priority' => 'required|in:Low,Medium,High,Urgent',
        'status' => 'required|in:Pending,Approved,Rejected,Partially_Fulfilled,Fulfilled,Cancelled',
        'justification' => 'required|string|max:1000'
    ];

    // Relationships
    public function requestedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'requested_by');
    }

    public function department()
    {
        return $this->belongsTo(\App\Models\Department::class, 'department_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(\App\Models\RequisitionItem::class, 'requisition_id');
    }

    /**
     * Purchase orders generated from this requisition. Currently at most one
     * (created at approval time), but a hasMany keeps the door open for split
     * orders across suppliers without another migration.
     */
    public function purchaseOrders()
    {
        return $this->hasMany(\App\Models\PurchaseOrder::class, 'requisition_id', 'requisition_id');
    }

    // Accessor methods
    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'Pending' => 'badge-warning',
            'Approved' => 'badge-success',
            'Rejected' => 'badge-danger',
            'Partially_Fulfilled' => 'badge-info',
            'Fulfilled' => 'badge-dark',
            'Cancelled' => 'badge-secondary'
        ];

        $status = str_replace('_', ' ', $this->status);
        $badge = $badges[$this->status] ?? 'badge-secondary';

        return "<span class=\"badge {$badge}\">{$status}</span>";
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->status === 'Pending';
    }

    public function getIsApprovedAttribute(): bool
    {
        return $this->status === 'Approved';
    }

    public function getIsRejectedAttribute(): bool
    {
        return $this->status === 'Rejected';
    }
}