<?php

namespace App\Services\Communication;

interface SmsProviderInterface
{
    /**
     * Send an SMS to a single recipient.
     *
     * @param string $phone E.164 formatted phone number
     * @param string $message SMS body
     * @return SmsResult
     */
    public function send(string $phone, string $message): SmsResult;

    /**
     * Send SMS to multiple recipients.
     *
     * @param array $recipients Array of E.164 phone numbers
     * @param string $message SMS body
     * @return SmsResult[] Results keyed by phone number
     */
    public function sendBulk(array $recipients, string $message): array;

    /**
     * Test the provider connection with a test message.
     *
     * @param string $testPhone E.164 phone number to send test SMS to
     * @return SmsResult
     */
    public function testConnection(string $testPhone): SmsResult;
}
