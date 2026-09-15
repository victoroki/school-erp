<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileNoticeController extends Controller
{
    /**
     * GET /api/mobile/notices
     *
     * Returns notifications visible to the current user's role.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('roles');

        $roleNames = $user->roles->pluck('role_name')->toArray();

        // Determine the user's portal type for recipient_type matching.
        $recipientType = 'all';
        if (in_array('Student', $roleNames)) {
            $recipientType = 'students';
        } elseif (in_array('Parent', $roleNames)) {
            $recipientType = 'parents';
        } elseif (in_array('Teacher', $roleNames)) {
            $recipientType = 'teachers';
        } elseif (in_array('Accountant', $roleNames)) {
            $recipientType = 'staff';
        }

        $notices = Notification::where(function ($q) use ($recipientType) {
                $q->where('recipient_type', 'all')
                  ->orWhere('recipient_type', $recipientType);
            })
            ->orderByDesc('created_at')
            ->limit(50)
            ->get([
                'notification_id',
                'title',
                'message',
                'type',
                'recipient_type',
                'sender_id',
                'created_at',
            ]);

        return response()->json($notices->map(fn ($n) => [
            'id'           => $n->notification_id,
            'title'        => $n->title,
            'body'         => $n->message,
            'audience'     => $n->recipient_type,
            'author'       => $n->sender?->name ?? 'System',
            'published_at' => $n->created_at->toIso8601String(),
        ]));
    }
}
