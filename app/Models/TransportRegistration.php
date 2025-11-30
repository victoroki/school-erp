<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportRegistration extends Model
{
    public $table = 'transport_registrations';
    
    protected $primaryKey = 'registration_id';

    public $fillable = [
        'student_id',
        'route_id',
        'stop_id',
        'fee_amount',
        'payment_status',
        'academic_year_id'
    ];

    protected $casts = [
        'student_id' => 'integer',
        'route_id' => 'integer',
        'stop_id' => 'integer',
        'fee_amount' => 'decimal:2',
        'payment_status' => 'string',
        'academic_year_id' => 'integer'
    ];

    public static array $rules = [
        'student_id' => 'nullable|exists:students,student_id',
        'route_id' => 'nullable|exists:routes,route_id',
        'stop_id' => 'nullable|exists:route_stops,stop_id',
        'fee_amount' => 'required|numeric|min:0',
        'payment_status' => 'nullable|in:paid,unpaid,partially_paid',
        'academic_year_id' => 'nullable|exists:academic_years,academic_year_id',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    /**
     * Get the student that owns the transport registration.
     */
    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Student::class, 'student_id', 'student_id');
    }

    /**
     * Get the route that owns the transport registration.
     */
    public function route(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Route::class, 'route_id', 'route_id');
    }

    /**
     * Get the stop that owns the transport registration.
     */
    public function stop(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\RouteStop::class, 'stop_id', 'stop_id');
    }

    /**
     * Get the academic year that owns the transport registration.
     */
    public function academicYear(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\AcademicYear::class, 'academic_year_id', 'academic_year_id');
    }

    /**
     * Check if adding a student would exceed the vehicle capacity for this route.
     *
     * @param int $routeId
     * @param int $academicYearId
     * @return bool
     */
    public static function wouldExceedCapacity(int $routeId, int $academicYearId): bool
    {
        // Get the transport assignment for this route
        $assignment = \App\Models\TransportAssignment::where('route_id', $routeId)->first();
        
        if (!$assignment) {
            return false; // No assignment means no capacity constraint
        }
        
        // Get the vehicle for this assignment
        $vehicle = $assignment->vehicle;
        
        if (!$vehicle) {
            return false; // No vehicle means no capacity constraint
        }
        
        // Count current registrations for this route and academic year
        $currentRegistrations = self::where('route_id', $routeId)
            ->where('academic_year_id', $academicYearId)
            ->count();
        
        // Check if adding another student would exceed capacity
        return $currentRegistrations >= $vehicle->seating_capacity;
    }
}