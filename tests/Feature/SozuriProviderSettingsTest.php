<?php

namespace Tests\Feature;

use App\Models\CommunicationSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SozuriProviderSettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
    }

    private function actingAsAdmin(): self
    {
        $this->actingAs($this->admin);

        // Bypass policy by granting the permission directly on first permission check
        \Gate::before(function (User $user) {
            return true;
        });

        return $this;
    }

    /** @test */
    public function resaving_form_with_masked_api_key_keeps_stored_credential(): void
    {
        $this->actingAsAdmin();

        $setting = CommunicationSetting::create([
            'settings_key' => 'sms_provider',
            'provider_type' => 'sms',
            'provider_name' => 'sozuri',
            'is_active' => true,
            'credentials' => [
                'api_key' => 'real-secret-api-key-abcd',
                'project' => 'Garikon School',
                'sender_id' => 'Garikon',
                'message_type' => 'transactional',
                'auth_key' => 'webhook-secret-wxyz',
            ],
        ]);

        $maskedApiKey = $setting->getMaskedCredential('api_key');
        $maskedAuthKey = $setting->getMaskedCredential('auth_key');

        $response = $this->post(route('communication.providers.update-sozuri'), [
            'api_key' => $maskedApiKey,
            'project' => 'Garikon School',
            'sender_id' => 'Garikon',
            'message_type' => 'transactional',
            'auth_key' => $maskedAuthKey,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('communication.providers.index'));

        $setting->refresh();
        $credentials = $setting->credentials;

        $this->assertEquals('real-secret-api-key-abcd', $credentials['api_key']);
        $this->assertEquals('webhook-secret-wxyz', $credentials['auth_key']);
    }

    /** @test */
    public function entering_a_new_api_key_replaces_the_stored_one(): void
    {
        $this->actingAsAdmin();

        CommunicationSetting::create([
            'settings_key' => 'sms_provider',
            'provider_type' => 'sms',
            'provider_name' => 'sozuri',
            'is_active' => true,
            'credentials' => [
                'api_key' => 'old-key-abcd',
                'project' => 'Garikon School',
                'sender_id' => 'Garikon',
                'message_type' => 'transactional',
            ],
        ]);

        $response = $this->post(route('communication.providers.update-sozuri'), [
            'api_key' => 'new-rotated-key-efgh',
            'project' => 'Garikon School',
            'sender_id' => 'Garikon',
            'message_type' => 'transactional',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('communication.providers.index'));

        $setting = CommunicationSetting::where('settings_key', 'sms_provider')
            ->where('provider_name', 'sozuri')->first();

        $this->assertEquals('new-rotated-key-efgh', $setting->credentials['api_key']);
    }
}
