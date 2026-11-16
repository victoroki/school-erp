<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Notification;
use App\Models\NotificationRecipient;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * PHASE 5 — communication on the REAL backend contract.
 *
 * There are two distinct, genuinely different things here (never merged):
 *
 *   Direct Messages  = `messages` rows, sender_id/receiver_id between USERS.
 *                      Staff-to-staff only (see dmRolesAllowed); Parents and
 *                      Students use the broadcast notices + per-student
 *                      notices instead. Fields are exactly what the table
 *                      has: message_id, sender_id, receiver_id, subject,
 *                      message, is_read, read_at, created_at.
 *
 *   Notifications    = the personal feed over `notifications` (broadcast
 *                      rows by recipient_type + 'specific' rows targeted via
 *                      notification_recipients). Read state lives per
 *                      recipient in notification_recipients — Phase 5 wires
 *                      it for real: rows are fanned out lazily and
 *                      idempotently on first fetch, so the FIRST sync of a
 *                      broadcast notice appears unread (never as a fake
 *                      "all read"), and marking read persists per person.
 *
 * The old fictional /messages/rooms + /messages/room/{id} never existed
 * server-side; threads are DERIVED here from sender/receiver pairs, which is
 * all the data model honestly supports (no typing, presence or receipts).
 */
class MobileCommunicationController extends Controller
{
    /** Roles allowed to use direct messaging (staff personas; portal roles excluded). */
    private const DM_ROLES = ['Owner', 'Super Admin', 'Admin', 'Teacher', 'Accountant'];

    /**
     * GET /api/mobile/messages
     *
     * Paginated own-messages feed (sent OR received), newest first. Every row
     * is mapped so `is_read` means "read by ME" for the caller (a sender's
     * own copies always read=true — the flag is the receiver's state).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $messages = Message::with(['sender:id,name', 'receiver:id,name'])
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $messages->getCollection()->transform(function ($m) use ($user) {
            return [
                'id'            => $m->message_id,
                'sender_id'     => $m->sender_id,
                'sender_name'   => $m->sender?->name ?? 'Unknown',
                'receiver_id'   => $m->receiver_id,
                'receiver_name' => $m->receiver?->name ?? 'Unknown',
                'subject'       => $m->subject,
                'body'          => $m->message,
                'is_read'       => (bool) ($m->receiver_id === $user->id ? $m->is_read : true),
                'created_at'    => $m->created_at?->toIso8601String(),
            ];
        });

        return response()->json($messages);
    }

    /**
     * GET /api/mobile/messages/threads
     *
     * Direct-message threads derived from pairs (the honest shape of the
     * data). One row per counterpart the caller has exchanged messages with,
     * most recent activity first.
     */
    public function threads(Request $request): JsonResponse
    {
        $user = $request->user();

        $rows = Message::selectRaw('message_id, sender_id, receiver_id, message, created_at, is_read')
            ->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)->orWhere('receiver_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->limit(200)
            ->get();

        $threads = [];
        foreach ($rows as $r) {
            $otherId = $r->sender_id === $user->id ? $r->receiver_id : $r->sender_id;
            if (!isset($threads[$otherId])) {
                $threads[$otherId] = [
                    'user_id'         => $otherId,
                    'last_message'    => $r->message,
                    'last_message_at' => $r->created_at?->toIso8601String(),
                    'sent_by_me'      => $r->sender_id === $user->id,
                    'unread_count'    => 0,
                ];
            }
            if ($r->receiver_id === $user->id && !$r->is_read) {
                $threads[$otherId]['unread_count']++;
            }
        }

        $users = User::whereIn('id', array_keys($threads))->pluck('name', 'id');
        foreach ($threads as $id => $t) {
            $threads[$id]['name'] = $users[$id] ?? 'Unknown';
        }

        return response()->json(['threads' => array_values($threads)]);
    }

    /**
     * GET /api/mobile/messages/contacts
     *
     * Who you may DM: staff-role users excluding yourself. Derived from the
     * same rule send() enforces, so the picker can never offer a recipient
     * the server would reject.
     */
    public function contacts(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->assertCanDm($user);

        $contacts = User::where('id', '!=', $user->id)
            ->whereHas('roles', fn ($q) => $q->whereIn('role_name', self::DM_ROLES))
            ->orderBy('name')
            ->limit(200)
            ->get(['id', 'name'])
            ->map(fn ($u) => ['user_id' => $u->id, 'name' => $u->name]);

        return response()->json(['contacts' => $contacts]);
    }

    /**
     * GET /api/mobile/messages/thread/{userId}
     *
     * The conversation between the caller and one counterpart, oldest first
     * (a thread you reply into). Marking the counterpart's messages read is
     * done via POST /messages/read (same endpoint as the inbox), not implied
     * here — opening a thread in the UI sends an explicit read call.
     */
    public function thread(Request $request, $userId): JsonResponse
    {
        $user = $request->user();

        $messages = Message::with(['sender:id,name'])
            ->where(function ($q) use ($user, $userId) {
                $q->where('sender_id', $user->id)->where('receiver_id', (int) $userId);
            })
            ->orWhere(function ($q) use ($user, $userId) {
                $q->where('receiver_id', $user->id)->where('sender_id', (int) $userId);
            })
            ->orderBy('created_at', 'asc')
            ->limit(200)
            ->get();

        return response()->json([
            'other_user_id' => (int) $userId,
            'other_name'    => User::find($userId)?->name ?? 'Unknown',
            'messages'      => $messages->map(fn ($m) => [
                'id'          => $m->message_id,
                'sender_id'   => $m->sender_id,
                'sender_name' => $m->sender?->name ?? 'Unknown',
                'body'        => $m->message,
                'is_read'     => (bool) ($m->receiver_id === $user->id ? $m->is_read : true),
                'created_at'  => $m->created_at?->toIso8601String(),
            ]),
        ]);
    }

    /**
     * POST /api/mobile/messages/send
     *
     * Canonical payload: recipient_id + content (the fields the backend has
     * always validated). STAFF-TO-STAFF ONLY: the sender must hold a staff
     * role and the recipient must be a staff-role user — a Student or Parent
     * token can never DM arbitrary users (STEP 7; enforced here, not in the
     * mobile gates).
     */
    public function send(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->assertCanDm($user);

        $request->validate([
            'recipient_id' => 'required|integer|exists:users,id',
            'content'      => 'required|string|max:2000',
        ]);

        $recipient = User::find($request->recipient_id);
        if ($recipient->id === $user->id || !$this->isStaffUser($recipient)) {
            return response()->json([
                'message' => 'Direct messages are only allowed between staff accounts.',
            ], 403);
        }

        $message = Message::create([
            'sender_id'   => $user->id,
            'receiver_id' => $request->recipient_id,
            'message'     => $request->content,
        ]);

        return response()->json([
            'message' => 'Message sent successfully.',
            'data'    => [
                'id'          => $message->message_id,
                'sender_id'   => $message->sender_id,
                'receiver_id' => $message->receiver_id,
                'body'        => $message->message,
                'created_at'  => $message->created_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * POST /api/mobile/messages/{id}/read
     *
     * Mark ONE received message read — only the RECEIVER may flip the flag
     * (a sender marking their own sent copy is a no-op 200, not an error).
     */
    public function markMessageRead(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        $message = Message::where('message_id', (int) $id)
            ->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)->orWhere('receiver_id', $user->id);
            })
            ->first();

        if (!$message) {
            return response()->json(['message' => 'Message not found.'], 404);
        }

        if ($message->receiver_id === $user->id && !$message->is_read) {
            $message->update(['is_read' => true, 'read_at' => now()]);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * GET /api/mobile/notifications
     *
     * Personal feed: broadcast rows matching ANY of the caller's role
     * audiences (multi-role users get every audience, not just the first —
     * fixed here and in MobileNoticeController) + 'specific' rows the caller
     * was targeted on. Rows are fanned into notification_recipients lazily
     * (idempotent) so is_read/read_at per person actually exist; unread
     * counts are real, never fabricated.
     */
    public function notifications(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->loadMissing('roles');
        $audiences = $this->audienceFor($user);

        $visible = Notification::where(function ($q) use ($audiences, $user) {
                $q->whereIn('recipient_type', array_merge(['all'], $audiences));
            })
            ->orWhereHas('recipients', fn ($q) => $q->where('recipient_id', $user->id))
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        // Lazy, idempotent fan-out: every visible broadcast row gets a pivot
        // row for this user (defaults is_read=false) so the FIRST sync shows
        // it unread and marking read persists.
        $existing = NotificationRecipient::where('recipient_id', $user->id)
            ->whereIn('notification_id', $visible->pluck('notification_id'))
            ->pluck('is_read', 'notification_id');

        $newIds = $visible->pluck('notification_id')->diff($existing->keys());
        if ($newIds->isNotEmpty()) {
            NotificationRecipient::insert(
                $newIds->map(fn ($nid) => [
                    'notification_id' => $nid,
                    'recipient_id'    => $user->id,
                    'is_read'         => false,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ])->all()
            );
            $existing = NotificationRecipient::where('recipient_id', $user->id)
                ->whereIn('notification_id', $visible->pluck('notification_id'))
                ->pluck('is_read', 'notification_id');
        }

        $items = $visible->map(fn ($n) => [
            'id'         => $n->notification_id,
            'title'      => $n->title,
            'body'       => $n->message,
            'type'       => $n->type,
            'is_read'    => (bool) ($existing[$n->notification_id] ?? false),
            'created_at' => $n->created_at?->toIso8601String(),
        ]);

        return response()->json([
            'notifications' => $items,
            'unread_count'  => $items->where('is_read', false)->count(),
        ]);
    }

    /**
     * POST /api/mobile/notifications/{id}/read
     *
     * Mark one notification read for THIS user (pivot row), but only if the
     * notification is actually visible to them. No-op-safe (idempotent).
     */
    public function markNotificationRead(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $user->loadMissing('roles');

        $notification = Notification::find((int) $id);
        if (!$notification) {
            return response()->json(['message' => 'Notification not found.'], 404);
        }

        $audiences = $this->audienceFor($user);
        $visibleToMe = $notification->recipient_type === 'all'
            || in_array($notification->recipient_type, $audiences, true)
            || NotificationRecipient::where('notification_id', $notification->notification_id)
                ->where('recipient_id', $user->id)->exists();

        if (!$visibleToMe) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        NotificationRecipient::updateOrCreate(
            ['notification_id' => $notification->notification_id, 'recipient_id' => $user->id],
            ['is_read' => true, 'read_at' => now()]
        );

        return response()->json(['ok' => true]);
    }

    /**
     * POST /api/mobile/notifications/read-all
     *
     * Mark every unread notification of mine read (pivot update, not a
     * rewrite of the broadcast row).
     */
    public function markAllNotificationsRead(Request $request): JsonResponse
    {
        $user = $request->user();

        $affected = NotificationRecipient::where('recipient_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['ok' => true, 'marked' => $affected]);
    }

    // ── helpers ────────────────────────────────────────────────────────────

    /**
     * Audience buckets for a user from ALL their roles (fixed: the old
     * if/elseif chain gave multi-role users only the first match).
     * Mirrors MobileNoticeController::audienceFor.
     */
    private function audienceFor(User $user): array
    {
        $roles = $user->roles->pluck('role_name')->all();
        $audiences = [];
        if (in_array('Student', $roles, true)) $audiences[] = 'students';
        if (in_array('Parent', $roles, true)) $audiences[] = 'parents';
        if (array_intersect(['Owner', 'Super Admin', 'Admin', 'Teacher', 'Accountant'], $roles)) {
            $audiences[] = 'teachers';
            $audiences[] = 'staff';
        }
        return array_values(array_unique($audiences));
    }

    private function isStaffUser(User $user): bool
    {
        $user->loadMissing('roles');
        return $user->roles->pluck('role_name')->intersect(self::DM_ROLES)->isNotEmpty();
    }

    private function assertCanDm(User $user): void
    {
        if (!$this->isStaffUser($user)) {
            abort(403, 'Direct messages are only available to staff accounts.');
        }
    }
}
