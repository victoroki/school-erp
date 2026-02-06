<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmergencyContact extends Model
{
    use HasFactory;

    protected $primaryKey = 'emergency_contact_id';

    protected $fillable = [
        'student_id',
        'name',
        'relationship',
        'phone',
        'phone_2',
        'email',
        'address',
        'priority',
        'is_authorized_pickup'
    ];

    protected $casts = [
        'is_authorized_pickup' => 'boolean',
        'priority' => 'integer'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
