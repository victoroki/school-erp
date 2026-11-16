<?php

namespace App\Exceptions;

/**
 * A recoverable domain error raised by HostelAllocationService (room full,
 * gender mismatch, duplicate allocation, missing record, ...).
 *
 * Thrown inside the service transactions so the partial write rolls back, and
 * caught by the controllers to render a user-facing Flash error instead of a
 * server failure. Keeps occupancy counters and allocation rows in step.
 */
class HostelAllocationException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
