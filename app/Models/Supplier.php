<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    public $table = 'suppliers';
    
    protected $primaryKey = 'supplier_id';

    public $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'address'
    ];

    protected $casts = [
        'name' => 'string',
        'contact_person' => 'string',
        'email' => 'string',
        'phone' => 'string',
        'address' => 'string'
    ];

    public static array $rules = [
        'name' => 'required|string|max:100',
        'contact_person' => 'nullable|string|max:100',
        'email' => 'nullable|string|max:100',
        'phone' => 'required|string|max:20',
        'address' => 'nullable|string|max:65535',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    
}