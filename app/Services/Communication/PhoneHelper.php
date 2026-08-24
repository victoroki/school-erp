<?php

namespace App\Services\Communication;

class PhoneHelper
{
    /**
     * Check whether a value is a valid Kenyan mobile number.
     *
     * Accepts 07XXXXXXXX / 01XXXXXXXX, +2547XXXXXXXX, 2547XXXXXXXX,
     * and bare 7XXXXXXXX / 1XXXXXXXX — with spaces, dashes or dots.
     */
    public static function isValidKenyanMobile(?string $phone): bool
    {
        return self::normalizeLocal($phone) !== null;
    }

    /**
     * Normalize any accepted input format to the canonical local format
     * stored in the database: 07XXXXXXXX / 01XXXXXXXX (10 digits).
     *
     * Returns null when the number is not a valid Kenyan mobile number.
     */
    public static function normalizeLocal(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $digits = preg_replace('/[^0-9]/', '', $phone);

        if (preg_match('/^(?:0|254)?(7|1)(\d{8})$/', $digits, $m)) {
            return '0' . $m[1] . $m[2];
        }

        return null;
    }

    /**
     * Format a Kenyan phone number to international format for Africa's Talking.
     *
     * Converts local formats (07XX, 7XX, 1XX) to +254XXXXXXXXX.
     * Used by both manual (SendBulkMessage) and auto (SendSingleNotification) flows.
     *
     * @param string|null $phone
     * @return string|null E.164 formatted phone or null if invalid
     */
    public static function formatForSms(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        } elseif (str_starts_with($phone, '7') || str_starts_with($phone, '1')) {
            $phone = '254' . $phone;
        }

        if (strlen($phone) === 12 && str_starts_with($phone, '254')) {
            return '+' . $phone;
        }

        if (strlen($phone) === 9) {
            return '+254' . $phone;
        }

        return '+' . $phone;
    }

    /**
     * Format for display (masked).
     *
     * @param string|null $phone
     * @return string|null
     */
    public static function formatForDisplay(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        } elseif (str_starts_with($phone, '7') || str_starts_with($phone, '1')) {
            $phone = '254' . $phone;
        }

        if (strlen($phone) == 12) {
            return '+' . substr($phone, 0, 3) . ' ' . substr($phone, 3, 3) . ' ' . substr($phone, 6, 3) . ' ' . substr($phone, 9);
        }

        return '+' . $phone;
    }

    /**
     * Mask a phone number for logging (keep last 4 digits).
     *
     * @param string|null $phone
     * @return string|null
     */
    public static function mask(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $formatted = self::formatForDisplay($phone);
        if (!$formatted) {
            return null;
        }

        $digits = preg_replace('/[^0-9]/', '', $formatted);
        if (strlen($digits) < 4) {
            return str_repeat('*', strlen($digits));
        }

        return str_repeat('*', strlen($digits) - 4) . substr($digits, -4);
    }
}
