<?php

namespace App\Services\Communication;

use App\Models\CommunicationSetting;
use AfricasTalking\SDK\AfricasTalking;
use Illuminate\Support\Facades\Log;

class AfricasTalkingSmsProvider implements SmsProviderInterface
{
    private $sms;
    private array $credentials;

    public function __construct(?array $credentials = null)
    {
        $this->credentials = $credentials ?? CommunicationSetting::getSmsCredentials() ?? $this->getEnvFallback();
        $at = new AfricasTalking(
            $this->credentials['username'] ?? config('africastalking.username', 'sandbox'),
            $this->credentials['api_key'] ?? config('africastalking.api_key', '')
        );
        $this->sms = $at->sms();
    }

    public function send(string $phone, string $message): SmsResult
    {
        $phone = PhoneHelper::formatForSms($phone);
        if (!$phone) {
            return SmsResult::failed('Invalid phone number');
        }

        try {
            $result = $this->sms->send([
                'to' => [$phone],
                'message' => $message,
                'from' => $this->credentials['sender_id'] ?? config('africastalking.sms.from', ''),
            ]);

            if (isset($result['status']) && $result['status'] === 'error') {
                $errorMsg = $result['message'] ?? 'Unknown Africa\'s Talking error';
                Log::error('AT SMS Error', ['phone' => $phone, 'error' => $errorMsg, 'response' => $result]);
                return SmsResult::failed($errorMsg, $result);
            }

            $recipients = $result['data'] ?? $result['SMSMessageData'] ?? null;
            if (is_array($recipients) && isset($recipients['recipients'])) {
                foreach ($recipients['recipients'] as $r) {
                    if (($r['status'] ?? '') === 'Success') {
                        return SmsResult::success(
                            $r['messageId'] ?? null,
                            $this->calculateCost($r),
                            $r
                        );
                    }
                    return SmsResult::failed($r['status'] ?? 'Failed', $r);
                }
            }

            return SmsResult::success(null, null, $result);
        } catch (\Exception $e) {
            Log::error('AT SMS Exception', ['phone' => $phone, 'error' => $e->getMessage()]);
            return SmsResult::failed($e->getMessage());
        }
    }

    public function sendBulk(array $recipients, string $message): array
    {
        $formatted = array_filter(array_map(fn($p) => PhoneHelper::formatForSms($p), $recipients));
        if (empty($formatted)) {
            return [];
        }

        try {
            $result = $this->sms->send([
                'to' => array_values($formatted),
                'message' => $message,
                'from' => $this->credentials['sender_id'] ?? config('africastalking.sms.from', ''),
                'enqueue' => true,
            ]);

            $results = [];
            foreach ($formatted as $phone) {
                $results[$phone] = SmsResult::success(null, null, $result);
            }
            return $results;
        } catch (\Exception $e) {
            Log::error('AT SMS Bulk Exception', ['error' => $e->getMessage()]);
            $results = [];
            foreach ($formatted as $phone) {
                $results[$phone] = SmsResult::failed($e->getMessage());
            }
            return $results;
        }
    }

    public function testConnection(string $testPhone): SmsResult
    {
        return $this->send($testPhone, 'Test message from ' . config('app.name') . ' — Africa\'s Talking SMS integration is working.');
    }

    private function getEnvFallback(): array
    {
        return [
            'username' => config('africastalking.username', 'sandbox'),
            'api_key' => config('africastalking.api_key', ''),
            'sender_id' => config('africastalking.sms.from', ''),
        ];
    }

    private function calculateCost(array $recipient): float
    {
        return (float) ($recipient['cost'] ?? 0);
    }
}
