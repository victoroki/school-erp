<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HostelRoom;
use App\Models\HostelAllocation;
use Illuminate\Support\Facades\Auth;

class MobileHostelController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Parent or Student gets their allocations
        if ($user->hasRole('Parent') || $user->hasRole('Student')) {
            $studentIds = [];
            if ($user->hasRole('Parent')) {
                $studentIds = collect($user->parents->students ?? [])->pluck('student_id')->toArray();
            } else {
                $studentIds = [$user->student->student_id ?? 0];
            }
            
            $allocations = HostelAllocation::with(['room', 'hostel', 'student'])
                ->whereIn('student_id', $studentIds)
                ->get();
                
            return response()->json([
                'data' => $allocations->map(function($a) {
                    return [
                        'type' => 'my_allocation',
                        'student_name' => $a->student ? ($a->student->first_name . ' ' . $a->student->last_name) : 'Unknown',
                        'room_no' => $a->room ? $a->room->room_number : 'Unknown',
                        'hostel' => $a->hostel ? $a->hostel->hostel_name : 'Unknown',
                        'admitted_on' => $a->allocation_date ? $a->allocation_date->format('Y-m-d') : null,
                        'bed_no' => $a->bed_number ? 'Bed ' . $a->bed_number : 'Unassigned',
                    ];
                })
            ]);
        }

        // For Admins/Teachers, return all rooms occupancy stats
        $rooms = HostelRoom::with('hostel')->get();
        return response()->json([
            'data' => $rooms->map(function($r) {
                return [
                    'type' => 'room_info',
                    'id' => $r->room_id,
                    'hostel' => $r->hostel ? $r->hostel->hostel_name : 'Unknown',
                    'room_no' => $r->room_number,
                    'capacity' => $r->capacity,
                    'occupied' => $r->occupied ?? 0,
                    'gender' => $r->hostel ? $r->hostel->hostel_type : 'Mixed'
                ];
            })
        ]);
    }
}
