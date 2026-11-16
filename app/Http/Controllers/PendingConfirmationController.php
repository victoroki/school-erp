<?php

namespace App\Http\Controllers;

use App\Events\NotificationConfirmed;
use App\Models\PendingConfirmation;
use Illuminate\Http\Request;
use Flash;

class PendingConfirmationController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:communication.send');
    }

    public function index(Request $request)
    {
        $pending = PendingConfirmation::pending()
            ->with([])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $counts = [
            'total' => PendingConfirmation::pending()->count(),
            'sms' => PendingConfirmation::pending()->where('channel', 'sms')->count(),
            'email' => PendingConfirmation::pending()->where('channel', 'email')->count(),
        ];

        return view('communication.pending.index', compact('pending', 'counts'));
    }

    public function confirm($id)
    {
        $pending = PendingConfirmation::findOrFail($id);
        if ($pending->status !== 'pending') {
            Flash::error('This confirmation has already been processed.');
            return redirect(route('communication.pending.index'));
        }

        NotificationConfirmed::dispatch($pending->id, auth()->id());

        Flash::success('Notification sent successfully.');
        return redirect(route('communication.pending.index'));
    }

    public function bulkConfirm(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:pending_confirmations,id',
        ]);

        $count = 0;
        foreach ($request->ids as $id) {
            $pending = PendingConfirmation::find($id);
            if ($pending && $pending->status === 'pending') {
                NotificationConfirmed::dispatch($pending->id, auth()->id());
                $count++;
            }
        }

        Flash::success("{$count} notifications sent successfully.");
        return redirect(route('communication.pending.index'));
    }

    public function discard($id)
    {
        $pending = PendingConfirmation::findOrFail($id);
        $pending->discard();

        Flash::success('Notification discarded.');
        return redirect(route('communication.pending.index'));
    }

    public function bulkDiscard(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:pending_confirmations,id',
        ]);

        PendingConfirmation::whereIn('id', $request->ids)
            ->where('status', 'pending')
            ->update(['status' => 'discarded']);

        Flash::success('Selected notifications discarded.');
        return redirect(route('communication.pending.index'));
    }
}
