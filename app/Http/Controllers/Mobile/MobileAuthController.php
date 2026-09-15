<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class MobileAuthController extends Controller
{
    /**
     * POST /api/mobile/auth/login
     *
     * Authenticate with email (or admission number) + password.
     *
     * Students can log in using their admission number (e.g. ADM2026/001)
     * as the "email" field. The controller resolves it to the correct user
     * account. All other users log in with their email address.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);

        $identifier = $request->email;
        $user  = null;

        // 1. Try direct email lookup first.
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $identifier)->first();
        }

        // 2. Try phone number lookup.
        if (! $user && $this->isPhoneNumber($identifier)) {
            $user = User::where('phone_number', $identifier)->first();
        }

        // 3. Try admission number resolution.
        if (! $user && $this->isAdmissionNumber($identifier)) {
            $user = $this->resolveUserFromAdmission($identifier);
        }

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['This account has been deactivated.'],
            ]);
        }

        // Eager-load roles.permissions so the mobile client receives a
        // fully hydrated bootstrap on the next request.
        $user->load('roles.permissions');

        // Revoke any previous mobile tokens for this user (single-session
        // per device policy — the new login replaces the old one).
        $user->tokens()->where('name', 'mobile')->each(fn ($token) => $token->delete());

        $accessExpiry  = config('mobile.access_token_minutes', 45);
        $refreshExpiry = config('mobile.refresh_token_days', 90);

        $accessToken = $user->createToken(
            'mobile',
            ['mobile:access'],
            now()->addMinutes($accessExpiry)
        );

        $refreshToken = $user->createToken(
            'mobile-refresh',
            ['mobile:refresh'],
            now()->addDays($refreshExpiry)
        );

        return response()->json([
            'access_token'  => $accessToken->plainTextToken,
            'refresh_token' => $refreshToken->plainTextToken,
            'expires_in'    => $accessExpiry * 60,
        ]);
    }

    /**
     * Check if the input looks like a phone number.
     * Simple check for numeric values or common phone formats.
     */
    private function isPhoneNumber(string $value): bool
    {
        // Matches common Kenyan formats: 07..., +254..., 254...
        return (bool) preg_match('/^(\+254|0|254)\d{9}$/', $value);
    }

    /**
     * Check if the input looks like an admission number.
     * Matches patterns like ADM2026/001, ADM2025/101, or just numeric IDs.
     */
    private function isAdmissionNumber(string $value): bool
    {
        return (bool) preg_match('/^ADM\d{4}\/\d{2,}$/i', $value);
    }

    /**
     * Resolve a Student user from an admission number.
     * Checks both the students.user_id link and the email convention
     * used by StudentUserSeeder (adm2026-001@student.local).
     */
    private function resolveUserFromAdmission(string $admissionNo): ?User
    {
        // Path 1: Direct student->user_id lookup.
        $student = Student::where('admission_no', $admissionNo)->first();
        if ($student && $student->user_id) {
            return User::find($student->user_id);
        }

        // Path 2: Email convention from StudentUserSeeder.
        $email = strtolower(str_replace('/', '-', $admissionNo)) . '@student.local';
        return User::where('email', $email)->first();
    }

    /**
     * POST /api/mobile/auth/refresh
     *
     * Exchange a valid refresh token for a new access + refresh token pair.
     * Implements a sliding session: each refresh extends the window.
     */
    public function refresh(Request $request): JsonResponse
    {
        $request->validate([
            'refresh_token' => 'required|string',
        ]);

        // The plainTextToken sent by the client is "{id}|{raw}".
        // We hash the raw part and look it up in the database.
        $parts = explode('|', $request->refresh_token, 2);
        if (count($parts) !== 2) {
            return response()->json(['message' => 'Invalid refresh token format.'], 401);
        }

        $rawToken   = $parts[1];
        $tokenHash  = hash('sha256', $rawToken);

        $token = $request->user()->tokens()
            ->where('name', 'mobile-refresh')
            ->where('token', $tokenHash)
            ->first();

        if (! $token) {
            return response()->json(['message' => 'Invalid refresh token.'], 401);
        }

        // Check expiry.
        if ($token->expires_at && $token->expires_at->isPast()) {
            $token->delete();
            return response()->json(['message' => 'Refresh token expired.'], 401);
        }

        // Rotate: delete the old refresh token, issue a new pair.
        $token->delete();

        $user = $request->user();
        $user->load('roles.permissions');

        $accessExpiry  = config('mobile.access_token_minutes', 45);
        $refreshExpiry = config('mobile.refresh_token_days', 90);

        $newAccess = $user->createToken(
            'mobile',
            ['mobile:access'],
            now()->addMinutes($accessExpiry)
        );

        $newRefresh = $user->createToken(
            'mobile-refresh',
            ['mobile:refresh'],
            now()->addDays($refreshExpiry)
        );

        return response()->json([
            'access_token'  => $newAccess->plainTextToken,
            'refresh_token' => $newRefresh->plainTextToken,
            'expires_in'    => $accessExpiry * 60,
        ]);
    }

    /**
     * POST /api/mobile/auth/logout
     *
     * Revoke all mobile tokens for the current user.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()
            ->where('name', 'mobile')
            ->delete();

        $request->user()->tokens()
            ->where('name', 'mobile-refresh')
            ->delete();

        return response()->json(['ok' => true]);
    }
}
