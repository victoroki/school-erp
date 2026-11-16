<?php

namespace App\Services\Communication;

interface EmailProviderInterface
{
    /**
     * Send an email.
     *
     * @param string $to Email address
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @return bool Whether the send was successful
     */
    public function send(string $to, string $subject, string $body): bool;

    /**
     * Test the provider connection.
     *
     * @param string $testEmail Email address to send test to
     * @return bool
     */
    public function testConnection(string $testEmail): bool;
}
