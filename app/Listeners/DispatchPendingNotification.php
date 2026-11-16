<?php

namespace App\Listeners;

use App\Events\NotificationConfirmed;
use App\Models\PendingConfirmation;
use App\Services\Communication\NotificationDispatcher;

class DispatchPendingNotification
{
    public function handle(NotificationConfirmed $event): void
    {
        $pending = PendingConfirmation::find($event->pendingConfirmationId);
        if (!$pending || $pending->status !== 'pending') {
            return;
        }

        $dispatcher = app(NotificationDispatcher::class);
        $dispatcher->dispatchPending($pending, $event->confirmedByUserId);
    }
}
