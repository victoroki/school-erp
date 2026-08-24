<?php

namespace App\Services\Communication;

class SmsResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $providerMessageId = null,
        public readonly ?string $error = null,
        public readonly ?float $cost = null,
        public readonly array $raw = [],
    ) {}

    public static function success(?string $providerMessageId, ?float $cost = null, array $raw = []): self
    {
        return new self(success: true, providerMessageId: $providerMessageId, cost: $cost, raw: $raw);
    }

    public static function failed(string $error, array $raw = []): self
    {
        return new self(success: false, error: $error, raw: $raw);
    }
}
