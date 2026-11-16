<?php

namespace App\Services\Communication;

use App\Models\CommunicationSetting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SmtpEmailProvider implements EmailProviderInterface
{
    private ?array $credentials;

    public function __construct()
    {
        $this->credentials = CommunicationSetting::getEmailCredentials();
    }

    public function send(string $to, string $subject, string $body): bool
    {
        try {
            $fromAddress = $this->credentials['from_address'] ?? config('mail.from.address');
            $fromName = $this->credentials['from_name'] ?? config('mail.from.name');

            // Temporarily override mail config if we have DB credentials
            if ($this->credentials) {
                config([
                    'mail.mailers.smtp.host' => $this->credentials['host'] ?? config('mail.mailers.smtp.host'),
                    'mail.mailers.smtp.port' => $this->credentials['port'] ?? config('mail.mailers.smtp.port'),
                    'mail.mailers.smtp.username' => $this->credentials['username'] ?? config('mail.mailers.smtp.username'),
                    'mail.mailers.smtp.password' => $this->credentials['password'] ?? config('mail.mailers.smtp.password'),
                    'mail.mailers.smtp.encryption' => $this->credentials['encryption'] ?? config('mail.mailers.smtp.encryption'),
                ]);
            }

            \Illuminate\Support\Facades\Mail::raw($body, function ($message) use ($to, $subject, $fromAddress, $fromName) {
                $message->to($to)
                    ->subject($subject)
                    ->from($fromAddress, $fromName);
            });

            return true;
        } catch (\Exception $e) {
            Log::error('SMTP Email Error', ['to' => $to, 'error' => $e->getMessage()]);
            return false;
        }
    }

    public function testConnection(string $testEmail): bool
    {
        return $this->send(
            $testEmail,
            'Test Email from ' . config('app.name'),
            '<p>This is a test email. If you received this, your SMTP configuration is working correctly.</p>'
        );
    }
}
