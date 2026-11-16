<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationConfirmed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $pendingConfirmationId,
        public int $confirmedByUserId
    ) {}
}
