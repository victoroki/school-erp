<?php

namespace App\Exceptions;

/**
 * A domain error from the mobile fee endpoints that maps directly to a JSON
 * error response (status + message + optional context) WITHOUT being logged
 * as a server failure. Thrown inside the collect() transaction so the money
 * write rolls back, and caught by the controller to render the response.
 */
class MobileFeeException extends \Exception
{
    /** @param array<string,mixed> $context */
    public function __construct(
        string $message,
        public readonly int $status = 422,
        public readonly array $context = [],
    ) {
        parent::__construct($message);
    }
}
