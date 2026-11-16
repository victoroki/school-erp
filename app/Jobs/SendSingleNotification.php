<?php

namespace App\Jobs;

use App\Models\CommunicationLog;
use App\Services\Communication\PhoneHelper;
use App\Services\Communication\SmsProviderInterface;
use App\Services\Communication\SmtpEmailProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSingleNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;

    public function __construct(
        public CommunicationLog $log
    ) {}

    public function backoff(): array
    {
        return [30, 60, 120];
    }

    public function handle(): void
    {
        $this->log->update(['status' => 'pending', 'sent_at' => now()]);

        $result = match ($this->log->channel) {
            'sms' => $this->sendSms(),
            'email' => $this->sendEmail(),
            default => false,
        };

        if ($result) {
            $this->log->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } else {
            $this->log->update([
                'status' => 'failed',
                'failure_reason' => 'Provider returned failure',
            ]);
        }
    }

    private function sendSms(): bool
    {
        $phone = PhoneHelper::formatForSms($this->log->contact);
        if (!$phone) {
            $this->log->update(['failure_reason' => 'Invalid phone number']);
            return false;
        }

        $provider = app(SmsProviderInterface::class);
        $result = $provider->send($phone, $this->log->body);

        if ($result->success) {
            $this->log->update([
                'provider_message_id' => $result->providerMessageId,
                'cost' => $result->cost,
            ]);
            return true;
        }

        $this->log->update(['failure_reason' => $result->error]);
        return false;
    }

    private function sendEmail(): bool
    {
        $provider = app(SmtpEmailProvider::class);
        return $provider->send(
            $this->log->contact,
            $this->log->subject ?? 'Notification',
            $this->log->body
        );
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendSingleNotification job failed', [
            'log_id' => $this->log->id,
            'error' => $exception->getMessage(),
        ]);

        $this->log->update([
            'status' => 'failed',
            'failure_reason' => $exception->getMessage(),
        ]);
    }
}
