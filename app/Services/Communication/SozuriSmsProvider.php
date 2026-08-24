<?php

namespace App\Services\Communication;

use App\Models\CommunicationSetting;
use App\Models\CommunicationLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SozuriSmsProvider implements SmsProviderInterface
{
    private const API_BASE = 'https://sozuri.net/api/v1';
    private const RETRYABLE_CODES = [429, 500, 503];
    private const MAX_RETRIES = 5;
    private const BACKOFF_BASE = 1.0;
    private const BACKOFF_CAP = 30.0;

    // Recipient-level acceptance statuses that mean the message will never be delivered
    private const REJECTED_STATUSES = [
        'unsupported_number', 'rejected', 'failed', 'invalid_number', 'blacklisted',
    ];

    private string $apiKey;
    private string $project;
    private string $senderId;
    private string $messageType;
    private ?string $authKey;

    public function __construct(?array $credentials = null)
    {
        $creds = $credentials ?? CommunicationSetting::getSmsCredentials() ?? $this->getEnvFallback();
        $this->apiKey = $creds['api_key'] ?? '';
        $this->project = $creds['project'] ?? config('sozuri.project', '');
        $this->senderId = $creds['sender_id'] ?? config('sozuri.sender_id', '');
        $this->messageType = $creds['message_type'] ?? config('sozuri.message_type', 'transactional');
        $this->authKey = $creds['auth_key'] ?? config('sozuri.auth_key');
    }

    public function send(string $phone, string $message): SmsResult
    {
        $phone = PhoneHelper::formatForSms($phone);
        if (! $phone) {
            return SmsResult::failed('Invalid phone number');
        }

        $payload = [
            'project' => $this->project,
            'from' => $this->senderId,
            'to' => $phone,
            'message' => $message,
            'type' => $this->messageType,
            'channel' => 'sms',
        ];

        return $this->sendWithRetry($payload);
    }

    public function sendBulk(array $recipients, string $message): array
    {
        $formatted = array_values(array_unique(array_filter(array_map(fn ($p) => PhoneHelper::formatForSms($p), $recipients))));
        if (empty($formatted)) {
            return [];
        }

        // Sozuri caps at 400 TPS platform-wide; chunk large batches and pace submissions
        $chunks = array_chunk($formatted, 100);
        $results = [];
        foreach ($chunks as $index => $chunk) {
            if ($index > 0) {
                usleep(1_000_000); // ~100 msgs/sec keeps us under the recommended per-project ceiling
            }

            $payload = [
                'project' => $this->project,
                'from' => $this->senderId,
                'to' => implode(',', $chunk),
                'message' => $message,
                'type' => $this->messageType,
                'channel' => 'sms',
            ];

            $bulkResult = $this->sendWithRetry($payload);
            $byRecipient = $this->mapRecipientsFromResponse($bulkResult->raw, $chunk);

            foreach ($chunk as $phone) {
                if ($bulkResult->success && isset($byRecipient[$phone])) {
                    $results[$phone] = $byRecipient[$phone];
                } elseif ($bulkResult->success) {
                    $results[$phone] = SmsResult::failed('No response entry for recipient', $bulkResult->raw);
                } else {
                    $results[$phone] = SmsResult::failed($bulkResult->error, $bulkResult->raw);
                }
            }
        }

        return $results;
    }

    public function testConnection(string $testPhone): SmsResult
    {
        return $this->send(
            $testPhone,
            'Test message from ' . config('app.name') . ' — Sozuri SMS integration is working.'
        );
    }

    public function getAuthKey(): ?string
    {
        return $this->authKey;
    }

    public function verifyCallbackAuth(array $payload): bool
    {
        if (! $this->authKey) {
            return true;
        }

        // Sozuri sends the project Auth Key in the body as authKey
        $provided = $payload['authKey'] ?? $payload['auth_key'] ?? null;

        return is_string($provided) && $provided !== '' && hash_equals($this->authKey, $provided);
    }

    /**
     * Verify the X-Sozuri-Signature HMAC header over the raw callback body.
     * Signature may be a bare hex digest or prefixed with "sha256=".
     */
    public function verifyCallbackSignature(string $rawBody, ?string $signature): bool
    {
        if (! $this->authKey || ! $signature) {
            return false;
        }

        $expected = hash_hmac('sha256', $rawBody, $this->authKey);
        $provided = strtolower(preg_replace('/^sha256=/i', '', trim($signature)) ?? '');

        return $provided !== '' && hash_equals($expected, $provided);
    }

    public function handleDeliveryStatus(array $payload): ?array
    {
        $messageId = $payload['messageId'] ?? null;
        $status = $payload['status'] ?? null;

        if (! $messageId || ! $status) {
            return null;
        }

        // Sample delivery callback: {"status": "success", "type": "bulkDelivery", ...}
        $statusMap = [
            'success' => 'delivered',
            'delivered' => 'delivered',
            'accepted' => 'sent',
            'sent' => 'sent',
            'failed' => 'failed',
            'undeliverable' => 'failed',
            'rejected' => 'failed',
            'expired' => 'failed',
        ];

        $newStatus = $statusMap[strtolower((string) $status)] ?? null;
        if (! $newStatus) {
            Log::info('Sozuri webhook: unmapped status', ['messageId' => $messageId, 'status' => $status]);
            return null;
        }

        $update = ['status' => $newStatus];
        if ($newStatus === 'delivered') {
            $update['delivered_at'] = $this->parseCallbackTimestamp($payload['timestamp'] ?? null);
        }
        if ($newStatus === 'failed') {
            $update['failure_reason'] = "Sozuri DLR: {$status}" . (isset($payload['network']) ? " ({$payload['network']})" : '');
        }

        $log = CommunicationLog::where('provider_message_id', $messageId)->first();
        if (! $log) {
            Log::warning('Sozuri webhook: no matching log for messageId', ['messageId' => $messageId]);
            return null;
        }

        // Don't regress a delivered log back to sent on out-of-order callbacks
        if ($log->status === 'delivered' && $newStatus === 'sent') {
            return null;
        }

        $log->update($update);

        return $update;
    }

    /**
     * Callback timestamps arrive as unix seconds ("1603713484") or date strings.
     */
    private function parseCallbackTimestamp(mixed $timestamp): \Illuminate\Support\Carbon
    {
        try {
            if (is_numeric($timestamp)) {
                return \Illuminate\Support\Carbon::createFromTimestamp((int) $timestamp);
            }
            if ($timestamp) {
                return \Illuminate\Support\Carbon::parse($timestamp);
            }
        } catch (\Throwable $e) {
            Log::warning('Sozuri webhook: unparseable timestamp', ['timestamp' => $timestamp]);
        }

        return now();
    }

    private function sendWithRetry(array $payload): SmsResult
    {
        $attempt = 0;
        $lastError = null;
        $requestId = null;

        while ($attempt < self::MAX_RETRIES) {
            $attempt++;

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->timeout(30)->post(self::API_BASE . '/messaging', $payload);

                $requestId = $response->header('X-Request-Id') ?? $response->json('requestId');

                if ($response->successful()) {
                    $body = $response->json();
                    $recipients = $body['recipients'] ?? [];

                    if (empty($recipients)) {
                        return SmsResult::failed('Response contained no recipients', is_array($body) ? $body : []);
                    }

                    // Match this phone's own entry; fall back to the first entry for single sends
                    $entry = null;
                    foreach ($recipients as $r) {
                        if (isset($r['to']) && $this->normalizePhone($r['to']) === $this->normalizePhone($payload['to'] ?? '')) {
                            $entry = $r;
                            break;
                        }
                    }
                    $entry ??= $recipients[0];

                    $recipientStatus = strtolower((string) ($entry['status'] ?? ''));
                    if (in_array($recipientStatus, self::REJECTED_STATUSES, true)) {
                        return SmsResult::failed(
                            "Recipient not accepted: {$recipientStatus}" . (isset($entry['statusCode']) ? " (statusCode: {$entry['statusCode']})" : ''),
                            $body
                        );
                    }

                    return SmsResult::success(
                        $entry['messageId'] ?? null,
                        null,
                        $body
                    );
                }

                $statusCode = $response->status();
                $errorBody = $response->json() ?? [];
                $errorCode = $errorBody['error_code'] ?? $errorBody['code'] ?? null;
                // Sozuri uses two error envelopes: top-level message (422 auth/validation)
                // and messageData.message (400 balance/processing errors)
                $errorMessage = $errorBody['message']
                    ?? $errorBody['messageData']['message']
                    ?? $errorBody['error']
                    ?? "HTTP {$statusCode}";

                $lastError = $errorMessage;

                if ($statusCode === 400) {
                    $message = strtolower($errorMessage);
                    if (str_contains($message, 'insufficient') || str_contains($message, 'balance')) {
                        Log::error('Sozuri: insufficient balance', [
                            'request_id' => $requestId,
                            'error' => $errorMessage,
                        ]);
                        return SmsResult::failed("Insufficient balance: {$errorMessage}", $errorBody);
                    }

                    return SmsResult::failed("Bad request: {$errorMessage}", $errorBody);
                }

                if (! in_array($statusCode, self::RETRYABLE_CODES)) {
                    Log::error('Sozuri: non-retryable error', [
                        'status' => $statusCode,
                        'error_code' => $errorCode,
                        'error' => $errorMessage,
                        'request_id' => $requestId,
                    ]);
                    return SmsResult::failed("{$errorMessage} (code: {$errorCode}, HTTP {$statusCode})", $errorBody);
                }

                // Retryable error — honour Retry-After if present, else exponential backoff with full jitter
                if ($attempt < self::MAX_RETRIES) {
                    $delay = $this->calculateBackoff($attempt, $this->parseRetryAfter($response->header('Retry-After')));
                    Log::warning('Sozuri: retryable error, retrying', [
                        'attempt' => $attempt,
                        'max' => self::MAX_RETRIES,
                        'delay_ms' => $delay * 1000,
                        'status' => $statusCode,
                        'error_code' => $errorCode,
                        'request_id' => $requestId,
                    ]);
                    usleep((int) ($delay * 1_000_000));
                }
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                $lastError = $e->getMessage();

                if ($attempt < self::MAX_RETRIES) {
                    $delay = $this->calculateBackoff($attempt);
                    Log::warning('Sozuri: connection error, retrying', [
                        'attempt' => $attempt,
                        'delay_ms' => $delay * 1000,
                        'error' => $lastError,
                    ]);
                    usleep((int) ($delay * 1_000_000));
                }
            } catch (\Exception $e) {
                Log::error('Sozuri: unexpected error', [
                    'error' => $e->getMessage(),
                    'attempt' => $attempt,
                ]);
                return SmsResult::failed($e->getMessage());
            }
        }

        // Exhausted all retries — dead-letter for human review
        Log::error('Sozuri: max retries exhausted, dead-lettering', [
            'attempts' => $attempt,
            'last_error' => $lastError,
            'request_id' => $requestId,
            'payload' => ['project' => $payload['project'] ?? '', 'to' => $payload['to'] ?? ''],
        ]);

        return SmsResult::failed("Max retries ({$attempt}) exhausted. Last error: {$lastError}. Request ID: {$requestId}");
    }

    /**
     * Calculate exponential backoff with full jitter.
     * Base 1s, doubling, cap 30s. Honours Retry-After seconds when provided.
     */
    private function calculateBackoff(int $attempt, ?int $retryAfterSeconds = null): float
    {
        if ($retryAfterSeconds !== null && $retryAfterSeconds > 0) {
            $capped = min((float) $retryAfterSeconds, self::BACKOFF_CAP);
            return mt_rand(0, (int) ($capped * 1000)) / 1000.0;
        }

        $exponential = self::BACKOFF_BASE * pow(2, $attempt - 1);
        $capped = min($exponential, self::BACKOFF_CAP);

        return mt_rand(0, (int) ($capped * 1000)) / 1000.0;
    }

    /**
     * Parse a Retry-After header value (seconds or HTTP date). Returns seconds or null.
     */
    private function parseRetryAfter(?string $value): ?int
    {
        if (! $value) {
            return null;
        }

        if (ctype_digit($value)) {
            return (int) $value;
        }

        $ts = strtotime($value);
        return $ts !== false ? max(0, $ts - time()) : null;
    }

    /**
     * Strip non-digits so echoed recipient numbers match what we sent.
     */
    private function normalizePhone(string $phone): string
    {
        return preg_replace('/[^0-9]/', '', $phone) ?? '';
    }

    /**
     * Map per-recipient results from a bulk response, keyed by the phone we sent to.
     *
     * @param array $raw Full response body
     * @param array $chunk Phone numbers included in this request
     * @return SmsResult[]
     */
    private function mapRecipientsFromResponse(array $raw, array $chunk): array
    {
        $entries = [];
        foreach ($raw['recipients'] ?? [] as $r) {
            if (isset($r['to'])) {
                $entries[$this->normalizePhone((string) $r['to'])] = $r;
            }
        }

        $mapped = [];
        foreach ($chunk as $phone) {
            $entry = $entries[$this->normalizePhone($phone)] ?? null;

            if ($entry === null) {
                $mapped[$phone] = SmsResult::failed('No response entry for recipient', $raw);
                continue;
            }

            $recipientStatus = strtolower((string) ($entry['status'] ?? ''));
            if (in_array($recipientStatus, self::REJECTED_STATUSES, true)) {
                $mapped[$phone] = SmsResult::failed(
                    "Recipient not accepted: {$recipientStatus}" . (isset($entry['statusCode']) ? " (statusCode: {$entry['statusCode']})" : ''),
                    $entry
                );
                continue;
            }

            $mapped[$phone] = SmsResult::success($entry['messageId'] ?? null, null, $entry);
        }

        return $mapped;
    }

    private function getEnvFallback(): array
    {
        return [
            'api_key' => config('sozuri.api_key', ''),
            'project' => config('sozuri.project', ''),
            'sender_id' => config('sozuri.sender_id', ''),
            'message_type' => config('sozuri.message_type', 'transactional'),
            'auth_key' => config('sozuri.auth_key'),
        ];
    }
}
