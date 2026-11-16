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
     * Returns broadcast notices visible to ANY of the current user's role
     * audiences. PHASE 5 fix: the old if/elseif chain gave a multi-role user
     * (e.g. a Teacher who is also a Parent) only the FIRST match's audience;
     * now every role bucket plus 'all' is included.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->loadMissing('roles');

        $roleNames = $user->roles->pluck('role_name')->all();

        // Every audience the caller's roles map to (mirrors
        // MobileCommunicationController::audienceFor — one rule, two readers).
        $audiences = [];
        if (in_array('Student', $roleNames, true)) $audiences[] = 'students';
        if (in_array('Parent', $roleNames, true)) $audiences[] = 'parents';
        if (array_intersect(['Owner', 'Super Admin', 'Admin', 'Teacher', 'Accountant'], $roleNames)) {
            $audiences[] = 'teachers';
            $audiences[] = 'staff';
        }

        $notices = Notification::whereIn('recipient_type', array_values(array_unique(array_merge(['all'], $audiences))))
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
            'type'         => $n->type,
            'audience'     => $n->recipient_type,
            'author'       => $n->sender?->name ?? 'System',
            'published_at' => $n->created_at->toIso8601String(),
        ]));
    }
}
