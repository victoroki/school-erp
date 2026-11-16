<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunicationSetting extends Model
{
    protected $table = 'communication_settings';

    protected $fillable = [
        'provider_type',
        'provider_name',
        'is_active',
        'credentials',
        'settings_key',
        'last_tested_at',
        'last_test_status',
        'last_test_error',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_tested_at' => 'datetime',
    ];

    protected $hidden = [
        'credentials',
    ];

    // Manual encrypted accessors — encrypted:array is incompatible with json columns in strict mode
    public function getCredentialsAttribute(?string $value): ?array
    {
        if (is_null($value)) {
            return null;
        }

        $decrypted = \Illuminate\Support\Facades\Crypt::decryptString($value);

        return json_decode($decrypted, true);
    }

    public function setCredentialsAttribute(array $value): void
    {
        $this->attributes['credentials'] = \Illuminate\Support\Facades\Crypt::encryptString(json_encode($value));
    }

    public function scopeForType($query, string $type)
    {
        return $query->where('provider_type', $type);
    }

    public static function getSmsCredentials(): ?array
    {
        // Check active provider selector first
        $active = static::where('settings_key', 'active_sms_provider')->first();
        $providerName = $active?->credentials['provider_name'] ?? null;

        if ($providerName) {
            $setting = static::where('settings_key', 'sms_provider')
                ->where('provider_name', $providerName)
                ->where('is_active', true)
                ->first();

            if ($setting) {
                return $setting->credentials;
            }
        }

        // Legacy fallback: any active sms_provider row
        $setting = static::where('settings_key', 'sms_provider')->where('is_active', true)->first();
        return $setting?->credentials;
    }

    public static function getActiveSmsProviderName(): ?string
    {
        $active = static::where('settings_key', 'active_sms_provider')->first();
        return $active?->credentials['provider_name'] ?? null;
    }

    public static function getEmailCredentials(): ?array
    {
        $setting = static::where('settings_key', 'email_provider')->where('is_active', true)->first();
        return $setting?->credentials;
    }

    public function getMaskedCredential(string $key): string
    {
        $value = $this->credentials[$key] ?? '';
        if (strlen($value) <= 4) {
            return str_repeat('*', max(strlen($value), 4));
        }
        return str_repeat('*', strlen($value) - 4) . substr($value, -4);
    }
}
