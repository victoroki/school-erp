<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalProfileController extends Controller
{
    /**
     * Show the portal user's profile.
     */
    public function index()
    {
        $user = Auth::user();

        $profile = match ($user->user_type) {
            'parent'  => $user->parent,
            'student' => $user->student,
            default   => null,
        };

        return view('portal.profile', compact('user', 'profile'));
    }

    /**
     * Update the portal user's profile (limited fields).
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'phone'    => 'nullable|string|max:20',
            'email'    => 'nullable|email|max:255',
        ]);

        $user->fill($validated)->save();

        AuditTrail::log('Portal', 'PROFILE UPDATE', $user->id, null, $validated);

        return back()->with('success', 'Profile updated successfully.');
    }
}
