<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileCommunicationController extends Controller
{
    /**
     * GET /api/mobile/messages
     *
     * Returns messages for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $messages = Message::where(function ($query) use ($user) {
            $query->where('sender_id', $user->id)
                ->orWhere('receiver_id', $user->id);
        })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($messages);
    }

    /**
     * POST /api/mobile/messages/send
     *
     * Sends a message to another user.
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'recipient_id' => 'required|integer|exists:users,id',
            'content'      => 'required|string|max:2000',
        ]);

        $message = Message::create([
            'sender_id'    => auth()->id(),
            'receiver_id'  => $request->recipient_id,
            'message'      => $request->content,
        ]);

        return response()->json([
            'message' => 'Message sent successfully.',
            'data'    => $message,
        ]);
    }

    /**
     * GET /api/mobile/notifications
     *
     * Returns notifications for the authenticated user.
     */
    public function notifications(Request $request): JsonResponse
    {
        $notifications = Notification::whereHas('recipients', function ($query) use ($request) {
            $query->where('recipient_id', $request->user()->id);
        })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($notifications);
    }
}