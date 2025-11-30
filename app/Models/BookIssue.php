<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookIssue extends Model
{
    public $table = 'book_issues';
    protected $primaryKey = 'issue_id';

    public $fillable = [
        'book_id',
        'member_id',
        'issue_date',
        'due_date',
        'return_date',
        'fine_amount',
        'status',
        'remarks',
        'issued_by',
        'received_by'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
        'fine_amount' => 'decimal:2',
        'status' => 'string'
    ];

    public static array $rules = [
        'book_id' => 'required|exists:books,id',
        'member_id' => 'required|exists:library_members,id',
        'issue_date' => 'required|date',
        'due_date' => 'required|date|after_or_equal:issue_date',
        'return_date' => 'nullable|date',
        'fine_amount' => 'nullable|numeric',
        'status' => 'required|in:issued,returned,overdue,lost',
        'remarks' => 'nullable|string',
        'issued_by' => 'nullable|exists:users,id',
        'received_by' => 'nullable|exists:users,id'
    ];

    public function book(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Book::class, 'book_id');
    }

    public function member(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\LibraryMember::class, 'member_id');
    }

    public function issuer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'issued_by');
    }

    public function receiver(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'received_by');
    }
}
