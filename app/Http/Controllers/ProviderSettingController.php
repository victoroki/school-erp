<?php

namespace App\Http\Controllers;

use App\Models\CommunicationSetting;
use App\Services\Communication\AfricasTalkingSmsProvider;
use App\Services\Communication\PhoneHelper;
use App\Services\Communication\SmsResult;
use App\Services\Communication\SozuriSmsProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Flash;

class ProviderSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:communication.manage');
    }

    public function index()
    {
        $smsSetting = CommunicationSetting::where('settings_key', 'sms_provider')
            ->where('provider_name', 'africastalking')->first();
        $sozuriSetting = CommunicationSetting::where('settings_key', 'sms_provider')
            ->where('provider_name', 'sozuri')->first();
        $emailSetting = CommunicationSetting::where('settings_key', 'email_provider')->first();
        $dailyLimitSetting = CommunicationSetting::where('settings_key', 'daily_sms_limit')->first();
        $activeProvider = CommunicationSetting::getActiveSmsProviderName();

        return view('communication.provider-settings.index', compact(
            'smsSetting', 'sozuriSetting', 'emailSetting', 'dailyLimitSetting', 'activeProvider'
        ));
    }

    public function updateSms(Request $request)
    {
        $request->validate([
            'api_key' => 'required|string',
            'username' => 'required|string',
            'sender_id' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        CommunicationSetting::updateOrCreate(
            ['settings_key' => 'sms_provider', 'provider_name' => 'africastalking'],
            [
                'provider_type' => 'sms',
                'provider_name' => 'africastalking',
                'is_active' => $request->boolean('is_active'),
                'credentials' => [
                    'api_key' => $request->api_key,
                    'username' => $request->username,
                    'sender_id' => $request->sender_id ?? '',
                ],
            ]
        );

        Flash::success('Africa\'s Talking SMS provider settings saved.');
        return redirect(route('communication.providers.index'));
    }

    public function updateSozuri(Request $request)
    {
        $request->validate([
            'api_key' => 'required|string',
            'project' => 'required|string',
            'sender_id' => 'required|string',
            'message_type' => 'required|in:transactional,promotional',
            'auth_key' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $setting = CommunicationSetting::where('settings_key', 'sms_provider')
            ->where('provider_name', 'sozuri')->first();

        // The form pre-fills secrets with masked values (e.g. ****abcd).
        // If the user didn't retype them, keep the stored credentials intact.
        $apiKey = $this->resolveSecret($request->api_key, $setting, 'api_key');
        if ($apiKey === null) {
            return back()->withInput()->with('flash_error', 'Please enter your Sozuri API key.');
        }

        $authKey = $this->resolveSecret($request->auth_key, $setting, 'auth_key');

        CommunicationSetting::updateOrCreate(
            ['settings_key' => 'sms_provider', 'provider_name' => 'sozuri'],
            [
                'provider_type' => 'sms',
                'provider_name' => 'sozuri',
                'is_active' => $request->boolean('is_active'),
                'credentials' => [
                    'api_key' => $apiKey,
                    'project' => $request->project,
                    'sender_id' => $request->sender_id,
                    'message_type' => $request->message_type,
                    'auth_key' => $authKey,
                ],
            ]
        );

        Flash::success('Sozuri SMS provider settings saved.');
        return redirect(route('communication.providers.index'));
    }

    /**
     * Return the submitted secret, or the stored one when the submission
     * is just the masked placeholder shown in the form.
     */
    private function resolveSecret(?string $submitted, ?CommunicationSetting $setting, string $key): ?string
    {
        if ($submitted === null || trim($submitted) === '') {
            // Field left empty: keep stored value for api_key, clear it for optional keys
            return $key === 'api_key' ? null : ($setting?->credentials[$key] ?? null);
        }

        if (str_contains($submitted, '*')) {
            // Masked value resubmitted — only valid if we already have a real one stored
            return $setting?->credentials[$key] ?? null;
        }

        return trim($submitted);
    }

    public function setActiveProvider(Request $request)
    {
        $request->validate([
            'provider' => 'required|in:africastalking,sozuri',
        ]);

        $providerName = $request->provider;

        // Check if the provider has saved credentials (regardless of is_active)
        $setting = CommunicationSetting::where('settings_key', 'sms_provider')
            ->where('provider_name', $providerName)
            ->first();

        if (! $setting) {
            Flash::error("Cannot activate {$providerName}: no saved credentials found. Configure and save the provider first.");
            return redirect(route('communication.providers.index'));
        }

        // Auto-enable the provider if it isn't already
        if (! $setting->is_active) {
            $setting->update(['is_active' => true]);
        }

        CommunicationSetting::updateOrCreate(
            ['settings_key' => 'active_sms_provider'],
            [
                'provider_type' => 'sms',
                'provider_name' => $providerName,
                'is_active' => true,
                'credentials' => ['provider_name' => $providerName],
            ]
        );

        Flash::success("Active SMS provider set to " . ($providerName === 'africastalking' ? "Africa's Talking" : "Sozuri") . ".");
        return redirect(route('communication.providers.index'));
    }

    public function updateEmail(Request $request)
    {
        $request->validate([
            'host' => 'required|string',
            'port' => 'required|integer',
            'username' => 'nullable|string',
            'password' => 'nullable|string',
            'encryption' => 'nullable|string',
            'from_address' => 'required|email',
            'from_name' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        CommunicationSetting::updateOrCreate(
            ['settings_key' => 'email_provider'],
            [
                'provider_type' => 'email',
                'provider_name' => 'smtp',
                'is_active' => $request->boolean('is_active'),
                'credentials' => [
                    'host' => $request->host,
                    'port' => (int) $request->port,
                    'username' => $request->username ?? '',
                    'password' => $request->password ?? '',
                    'encryption' => $request->encryption ?? 'tls',
                    'from_address' => $request->from_address,
                    'from_name' => $request->from_name ?? config('app.name'),
                ],
            ]
        );

        Flash::success('Email provider settings saved.');
        return redirect(route('communication.providers.index'));
    }

    public function updateDailyLimit(Request $request)
    {
        $request->validate([
            'daily_limit' => 'required|integer|min:1|max:10000',
        ]);

        CommunicationSetting::updateOrCreate(
            ['settings_key' => 'daily_sms_limit'],
            [
                'provider_type' => 'sms',
                'provider_name' => 'limit',
                'is_active' => true,
                'credentials' => [
                    'limit' => (int) $request->daily_limit,
                ],
            ]
        );

        Flash::success('Daily send limit updated.');
        return redirect(route('communication.providers.index'));
    }

    public function testSms(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $activeProvider = CommunicationSetting::getActiveSmsProviderName();

        try {
            $provider = $activeProvider === 'sozuri'
                ? new SozuriSmsProvider()
                : new AfricasTalkingSmsProvider();

            $result = $provider->testConnection(PhoneHelper::formatForSms($request->phone));

            $setting = CommunicationSetting::where('settings_key', 'sms_provider')
                ->where('provider_name', $activeProvider ?? 'africastalking')
                ->first();

            if ($setting) {
                $setting->update([
                    'last_tested_at' => now(),
                    'last_test_status' => $result->success ? 'success' : 'failed',
                    'last_test_error' => $result->error,
                ]);
            }

            if ($result->success) {
                return response()->json(['success' => true, 'message' => 'Test SMS sent successfully.']);
            }

            $message = $result->error;
            if (str_contains(strtolower($message ?? ''), 'insufficient')) {
                $message = 'Insufficient balance: ' . $message;
            }

            return response()->json(['success' => false, 'message' => 'Failed: ' . $message]);
        } catch (\Exception $e) {
            Log::error('SMS test failed', ['provider' => $activeProvider, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function handleSozuriWebhook(Request $request)
    {
        $payload = $request->all();

        // Resolve the provider to get the auth key
        $setting = CommunicationSetting::where('settings_key', 'sms_provider')
            ->where('provider_name', 'sozuri')
            ->first();

        $provider = new SozuriSmsProvider($setting?->credentials);

        // Callback carries the project Auth Key in the body plus an optional
        // X-Sozuri-Signature HMAC over the raw body — accept either proof.
        if (! $provider->verifyCallbackAuth($payload)
            && ! $provider->verifyCallbackSignature($request->getContent(), $request->header('X-Sozuri-Signature'))) {
            Log::warning('Sozuri webhook: invalid auth key or signature');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $result = $provider->handleDeliveryStatus($payload);

        if ($result) {
            return response()->json(['status' => 'ok', 'updated' => $result]);
        }

        return response()->json(['status' => 'ignored']);
    }
}
