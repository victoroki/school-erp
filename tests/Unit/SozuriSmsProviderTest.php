<?php

namespace Tests\Unit;

use App\Models\CommunicationLog;
use App\Models\CommunicationSetting;
use App\Services\Communication\SozuriSmsProvider;
use App\Services\Communication\SmsResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SozuriSmsProviderTest extends TestCase
{
    use RefreshDatabase;

    private array $testCredentials = [
        'api_key' => 'test-api-key-123',
        'project' => 'test-project',
        'sender_id' => 'TestSchool',
        'message_type' => 'transactional',
        'auth_key' => null,
    ];

    private function makeProvider(?array $credentials = null): SozuriSmsProvider
    {
        return new SozuriSmsProvider($credentials ?? $this->testCredentials);
    }

    /** @test */
    public function send_success_returns_success_result_with_message_id(): void
    {
        Http::fake([
            'sozuri.net/api/v1/messaging' => Http::response([
                'recipients' => [
                    ['messageId' => 'msg-abc-123', 'to' => '+254722000000', 'status' => 'accepted'],
                ],
            ], 200, ['X-Request-Id' => 'req-001']),
        ]);

        $provider = $this->makeProvider();
        $result = $provider->send('+254722000000', 'Hello from school');

        $this->assertTrue($result->success);
        $this->assertEquals('msg-abc-123', $result->providerMessageId);
        $this->assertNull($result->error);
    }

    /** @test */
    public function send_invalid_phone_returns_failure(): void
    {
        $provider = $this->makeProvider();
        $result = $provider->send('', 'Hello');

        $this->assertFalse($result->success);
        $this->assertEquals('Invalid phone number', $result->error);
    }

    /** @test */
    public function send_400_bad_request_returns_non_retryable_failure(): void
    {
        Http::fake([
            'sozuri.net/api/v1/messaging' => Http::response([
                'error_code' => 'VALIDATION_FAILED',
                'message' => 'Invalid phone format',
            ], 400),
        ]);

        $provider = $this->makeProvider();
        $result = $provider->send('+254722000000', 'Hello');

        $this->assertFalse($result->success);
        $this->assertStringContainsString('Invalid phone format', $result->error);
        $this->assertStringContainsString('Bad request', $result->error);
    }

    /** @test */
    public function send_insufficient_balance_returns_distinct_error(): void
    {
        Http::fake([
            'sozuri.net/api/v1/messaging' => Http::response([
                'error_code' => 'BAD_REQUEST',
                'message' => 'Insufficient balance',
            ], 400),
        ]);

        $provider = $this->makeProvider();
        $result = $provider->send('+254722000000', 'Hello');

        $this->assertFalse($result->success);
        $this->assertStringContainsString('Insufficient balance', $result->error);
    }

    /** @test */
    public function send_insufficient_balance_envelope_in_messagedata(): void
    {
        Http::fake([
            'sozuri.net/api/v1/messaging' => Http::response([
                'messageData' => [
                    'message' => 'Error. Insufficient balance. Top up and try again.',
                ],
            ], 400),
        ]);

        $provider = $this->makeProvider();
        $result = $provider->send('+254722000000', 'Hello');

        $this->assertFalse($result->success);
        $this->assertStringContainsString('Insufficient balance', $result->error);
    }

    /** @test */
    public function send_401_auth_failure_returns_non_retryable_failure(): void
    {
        Http::fake([
            'sozuri.net/api/v1/messaging' => Http::response([
                'error_code' => 'AUTHENTICATION_FAILED',
                'message' => 'Invalid API key',
            ], 401),
        ]);

        $provider = $this->makeProvider();
        $result = $provider->send('+254722000000', 'Hello');

        $this->assertFalse($result->success);
        $this->assertStringContainsString('Invalid API key', $result->error);
    }

    /** @test */
    public function send_429_rate_limited_retries_and_exhausts(): void
    {
        $callCount = 0;
        Http::fake([
            'sozuri.net/api/v1/messaging' => function () use (&$callCount) {
                $callCount++;
                return Http::response([
                    'error_code' => 'RATE_LIMITED',
                    'message' => 'Too many requests',
                ], 429);
            },
        ]);

        $provider = $this->makeProvider();
        $result = $provider->send('+254722000000', 'Hello');

        $this->assertFalse($result->success);
        $this->assertStringContainsString('Max retries', $result->error);
        $this->assertEquals(5, $callCount);
    }

    /** @test */
    public function send_503_service_unavailable_retries(): void
    {
        $callCount = 0;
        Http::fake([
            'sozuri.net/api/v1/messaging' => function () use (&$callCount) {
                $callCount++;
                if ($callCount < 3) {
                    return Http::response(['error_code' => 'SERVICE_UNAVAILABLE', 'message' => 'Down'], 503);
                }
                return Http::response([
                    'recipients' => [['messageId' => 'msg-retry-ok', 'to' => '+254722000000', 'status' => 'accepted']],
                ], 200);
            },
        ]);

        $provider = $this->makeProvider();
        $result = $provider->send('+254722000000', 'Hello');

        $this->assertTrue($result->success);
        $this->assertEquals('msg-retry-ok', $result->providerMessageId);
        $this->assertEquals(3, $callCount);
    }

    /** @test */
    public function send_connection_error_retries(): void
    {
        $callCount = 0;
        Http::fake([
            'sozuri.net/api/v1/messaging' => function () use (&$callCount) {
                $callCount++;
                if ($callCount < 2) {
                    throw new \Illuminate\Http\Client\ConnectionException('Connection timed out');
                }
                return Http::response([
                    'recipients' => [['messageId' => 'msg-ok-after-timeout', 'to' => '+254722000000', 'status' => 'accepted']],
                ], 200);
            },
        ]);

        $provider = $this->makeProvider();
        $result = $provider->send('+254722000000', 'Hello');

        $this->assertTrue($result->success);
        $this->assertEquals('msg-ok-after-timeout', $result->providerMessageId);
        $this->assertEquals(2, $callCount);
    }

    /** @test */
    public function send_bulk_returns_results_keyed_by_phone(): void
    {
        Http::fake([
            'sozuri.net/api/v1/messaging' => Http::response([
                'messageData' => ['messages' => 2],
                'recipients' => [
                    ['messageId' => 'msg-bulk-1', 'to' => '+254722000000', 'status' => 'accepted', 'statusCode' => '11'],
                    ['messageId' => 'msg-bulk-2', 'to' => '+254733000000', 'status' => 'accepted', 'statusCode' => '11'],
                ],
            ], 200),
        ]);

        $provider = $this->makeProvider();
        $results = $provider->sendBulk(['0722000000', '0733000000'], 'Bulk message');

        $this->assertCount(2, $results);
        $this->assertArrayHasKey('+254722000000', $results);
        $this->assertArrayHasKey('+254733000000', $results);
        $this->assertTrue($results['+254722000000']->success);
        $this->assertTrue($results['+254733000000']->success);
        // Each recipient gets its own messageId from Sozuri
        $this->assertEquals('msg-bulk-1', $results['+254722000000']->providerMessageId);
        $this->assertEquals('msg-bulk-2', $results['+254733000000']->providerMessageId);
    }

    /** @test */
    public function send_bulk_maps_rejected_recipient_to_failed_without_blocking_others(): void
    {
        Http::fake([
            'sozuri.net/api/v1/messaging' => Http::response([
                'recipients' => [
                    ['messageId' => 'msg-ok-1', 'to' => '+254722000000', 'status' => 'accepted'],
                    ['messageId' => null, 'to' => '+254799999999', 'status' => 'unsupported_number', 'statusCode' => '21'],
                ],
            ], 200),
        ]);

        $provider = $this->makeProvider();
        $results = $provider->sendBulk(['+254722000000', '+254799999999'], 'Bulk message');

        $this->assertTrue($results['+254722000000']->success);
        $this->assertFalse($results['+254799999999']->success);
        $this->assertStringContainsString('unsupported_number', $results['+254799999999']->error);
    }

    /** @test */
    public function send_single_rejected_recipient_returns_failure(): void
    {
        Http::fake([
            'sozuri.net/api/v1/messaging' => Http::response([
                'recipients' => [
                    ['messageId' => null, 'to' => '+254722000000', 'status' => 'unsupported_number', 'statusCode' => '21'],
                ],
            ], 200),
        ]);

        $provider = $this->makeProvider();
        $result = $provider->send('+254722000000', 'Hello');

        $this->assertFalse($result->success);
        $this->assertStringContainsString('unsupported_number', $result->error);
    }

    /** @test */
    public function test_connection_sends_test_message(): void
    {
        Http::fake([
            'sozuri.net/api/v1/messaging' => Http::response([
                'recipients' => [
                    ['messageId' => 'msg-test-1', 'to' => '+254722000000', 'status' => 'accepted'],
                ],
            ], 200),
        ]);

        $provider = $this->makeProvider();
        $result = $provider->testConnection('+254722000000');

        $this->assertTrue($result->success);
        $this->assertEquals('msg-test-1', $result->providerMessageId);
    }

    /** @test */
    public function verify_callback_auth_returns_true_when_no_auth_key_configured(): void
    {
        $provider = $this->makeProvider();
        $this->assertTrue($provider->verifyCallbackAuth([]));
    }

    /** @test */
    public function verify_callback_auth_rejects_wrong_key(): void
    {
        $provider = $this->makeProvider(['auth_key' => 'secret-123']);
        $this->assertFalse($provider->verifyCallbackAuth(['auth_key' => 'wrong']));
        $this->assertFalse($provider->verifyCallbackAuth(['authKey' => 'wrong']));
    }

    /** @test */
    public function verify_callback_auth_accepts_camelcase_authkey(): void
    {
        // Sozuri sends the Auth Key in the body as authKey
        $provider = $this->makeProvider(['auth_key' => 'secret-123']);
        $this->assertTrue($provider->verifyCallbackAuth(['authKey' => 'secret-123']));
    }

    /** @test */
    public function verify_callback_signature_validates_hmac(): void
    {
        $rawBody = json_encode(['messageId' => 'msg-x', 'status' => 'success']);
        $signature = hash_hmac('sha256', $rawBody, 'secret-123');

        $provider = $this->makeProvider(['auth_key' => 'secret-123']);
        $this->assertTrue($provider->verifyCallbackSignature($rawBody, $signature));
        $this->assertTrue($provider->verifyCallbackSignature($rawBody, 'sha256=' . $signature));
        $this->assertFalse($provider->verifyCallbackSignature($rawBody, hash_hmac('sha256', $rawBody, 'other-key')));
        $this->assertFalse($provider->verifyCallbackSignature($rawBody . 'tampered', $signature));
    }

    /** @test */
    public function handle_delivery_status_maps_success_to_delivered_with_unix_timestamp(): void
    {
        // Sample delivery callback from Sozuri docs
        $log = CommunicationLog::create([
            'trigger_type' => 'manual',
            'trigger_id' => null,
            'trigger_model' => null,
            'channel' => 'sms',
            'recipient_type' => 'parent',
            'recipient_id' => 1,
            'contact' => '+254722000000',
            'body' => 'Test message',
            'status' => 'sent',
            'provider_message_id' => 'MSGBLK5F96B6A0CC2EB1603712672',
            'sent_at' => now(),
        ]);

        $provider = $this->makeProvider();
        $result = $provider->handleDeliveryStatus([
            'project' => 'test-project',
            'messageId' => 'MSGBLK5F96B6A0CC2EB1603712672',
            'channel' => 'sms',
            'status' => 'success',
            'network' => 'safaricom',
            'type' => 'bulkDelivery',
            'timestamp' => '1603713484',
        ]);

        $this->assertNotNull($result);
        $this->assertEquals('delivered', $result['status']);
        $log->refresh();
        $this->assertEquals('delivered', $log->status);
        $this->assertEquals(1603713484, $log->delivered_at->timestamp);
    }

    /** @test */
    public function handle_delivery_status_does_not_regress_delivered_to_sent(): void
    {
        $log = CommunicationLog::create([
            'trigger_type' => 'manual',
            'trigger_id' => null,
            'trigger_model' => null,
            'channel' => 'sms',
            'recipient_type' => 'parent',
            'recipient_id' => 1,
            'contact' => '+254722000000',
            'body' => 'Test message',
            'status' => 'delivered',
            'provider_message_id' => 'msg-order-001',
            'delivered_at' => now(),
            'sent_at' => now(),
        ]);

        $provider = $this->makeProvider();
        $result = $provider->handleDeliveryStatus([
            'messageId' => 'msg-order-001',
            'status' => 'accepted', // out-of-order late callback
        ]);

        $this->assertNull($result);
        $log->refresh();
        $this->assertEquals('delivered', $log->status);
    }

    /** @test */
    public function verify_callback_auth_accepts_correct_key(): void
    {
        $provider = $this->makeProvider(['auth_key' => 'secret-123']);
        $this->assertTrue($provider->verifyCallbackAuth(['auth_key' => 'secret-123']));
    }

    /** @test */
    public function handle_delivery_status_updates_log_to_delivered(): void
    {
        $log = CommunicationLog::create([
            'trigger_type' => 'manual',
            'trigger_id' => null,
            'trigger_model' => null,
            'channel' => 'sms',
            'recipient_type' => 'parent',
            'recipient_id' => 1,
            'contact' => '+254722000000',
            'body' => 'Test message',
            'status' => 'sent',
            'provider_message_id' => 'msg-dlr-001',
            'sent_at' => now(),
        ]);

        $provider = $this->makeProvider();
        $result = $provider->handleDeliveryStatus([
            'messageId' => 'msg-dlr-001',
            'status' => 'delivered',
            'channel' => 'sms',
            'timestamp' => '2026-08-21T12:00:00Z',
        ]);

        $this->assertNotNull($result);
        $this->assertEquals('delivered', $result['status']);
        $log->refresh();
        $this->assertEquals('delivered', $log->status);
        $this->assertNotNull($log->delivered_at);
    }

    /** @test */
    public function handle_delivery_status_updates_log_to_failed(): void
    {
        $log = CommunicationLog::create([
            'trigger_type' => 'manual',
            'trigger_id' => null,
            'trigger_model' => null,
            'channel' => 'sms',
            'recipient_type' => 'parent',
            'recipient_id' => 1,
            'contact' => '+254722000000',
            'body' => 'Test message',
            'status' => 'sent',
            'provider_message_id' => 'msg-dlr-002',
            'sent_at' => now(),
        ]);

        $provider = $this->makeProvider();
        $result = $provider->handleDeliveryStatus([
            'messageId' => 'msg-dlr-002',
            'status' => 'failed',
            'channel' => 'sms',
            'network' => 'Safaricom',
        ]);

        $this->assertNotNull($result);
        $this->assertEquals('failed', $result['status']);
        $log->refresh();
        $this->assertEquals('failed', $log->status);
        $this->assertStringContainsString('Safaricom', $log->failure_reason);
    }

    /** @test */
    public function handle_delivery_status_ignores_unknown_status(): void
    {
        CommunicationLog::create([
            'trigger_type' => 'manual',
            'trigger_id' => null,
            'trigger_model' => null,
            'channel' => 'sms',
            'recipient_type' => 'parent',
            'recipient_id' => 1,
            'contact' => '+254722000000',
            'body' => 'Test message',
            'status' => 'sent',
            'provider_message_id' => 'msg-dlr-003',
            'sent_at' => now(),
        ]);

        $provider = $this->makeProvider();
        $result = $provider->handleDeliveryStatus([
            'messageId' => 'msg-dlr-003',
            'status' => 'unknown_future_status',
        ]);

        $this->assertNull($result);
    }

    /** @test */
    public function handle_delivery_status_returns_null_for_missing_message_id(): void
    {
        $provider = $this->makeProvider();
        $result = $provider->handleDeliveryStatus(['status' => 'delivered']);

        $this->assertNull($result);
    }

    /** @test */
    public function handle_delivery_status_returns_null_for_no_matching_log(): void
    {
        $provider = $this->makeProvider();
        $result = $provider->handleDeliveryStatus([
            'messageId' => 'nonexistent-id',
            'status' => 'delivered',
        ]);

        $this->assertNull($result);
    }

    /** @test */
    public function handle_delivery_status_rejects_invalid_auth_key(): void
    {
        CommunicationLog::create([
            'trigger_type' => 'manual',
            'trigger_id' => null,
            'trigger_model' => null,
            'channel' => 'sms',
            'recipient_type' => 'parent',
            'recipient_id' => 1,
            'contact' => '+254722000000',
            'body' => 'Test message',
            'status' => 'sent',
            'provider_message_id' => 'msg-secure-001',
            'sent_at' => now(),
        ]);

        $provider = $this->makeProvider(['auth_key' => 'real-key']);

        $this->assertFalse($provider->verifyCallbackAuth(['auth_key' => 'wrong']));
        $this->assertTrue($provider->verifyCallbackAuth(['auth_key' => 'real-key']));
    }

    /** @test */
    public function credentials_come_from_database_when_available(): void
    {
        CommunicationSetting::create([
            'settings_key' => 'active_sms_provider',
            'provider_type' => 'sms',
            'provider_name' => 'sozuri',
            'is_active' => true,
            'credentials' => ['provider_name' => 'sozuri'],
        ]);

        CommunicationSetting::create([
            'settings_key' => 'sms_provider',
            'provider_type' => 'sms',
            'provider_name' => 'sozuri',
            'is_active' => true,
            'credentials' => [
                'api_key' => 'db-api-key',
                'project' => 'db-project',
                'sender_id' => 'DBSchool',
                'message_type' => 'promotional',
            ],
        ]);

        Http::fake([
            'sozuri.net/api/v1/messaging' => function ($request) {
                $body = json_decode($request->body(), true);
                $this->assertEquals('db-project', $body['project']);
                $this->assertEquals('DBSchool', $body['from']);
                $this->assertEquals('promotional', $body['type']);

                return Http::response([
                    'recipients' => [['messageId' => 'msg-db-001', 'to' => '+254722000000', 'status' => 'accepted']],
                ], 200);
            },
        ]);

        $provider = new SozuriSmsProvider();
        $result = $provider->send('+254722000000', 'Test from DB credentials');

        $this->assertTrue($result->success);
    }

    /** @test */
    public function backoff_calculation_stays_within_bounds(): void
    {
        $provider = $this->makeProvider();

        $reflection = new \ReflectionClass($provider);
        $method = $reflection->getMethod('calculateBackoff');
        $method->setAccessible(true);

        // Test several attempts — all should be between 0 and 30 seconds
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $delay = $method->invoke($provider, $attempt);
            $this->assertGreaterThanOrEqual(0, $delay);
            $this->assertLessThanOrEqual(30.0, $delay);
        }
    }
}
