<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Parents;
use App\Models\StudentParentRelationship;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileParentController extends Controller
{
    /**
     * GET /api/mobile/parent/children
     *
     * Returns the list of children associated with the authenticated parent.
     */
    public function children(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->hasRole('Parent')) {
            return response()->json(['error' => 'Only parents can access this endpoint.'], 403);
        }

        $parent = Parents::where('user_id', $user->id)->first();
        if (!$parent) {
            return response()->json(['error' => 'Parent record not found.'], 404);
        }

        $children = StudentParentRelationship::where('parent_id', $parent->parent_id)
            ->with('student')
            ->get()
            ->map(fn($rel) => [
                'student_id' => $rel->student_id,
                'name'       => trim(($rel->student?->first_name ?? '') . ' ' . ($rel->student?->last_name ?? '')),
                'admission_no' => $rel->student?->admission_no,
            ]);

        return response()->json([
            'parent_name' => $user->name,
            'children'    => $children,
        ]);
    }
}