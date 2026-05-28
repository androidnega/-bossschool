<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\UpdatePaystackSettingsRequest;
use App\Models\PlatformSetting;
use App\Services\ActivityLogger;
use App\Services\Sms\SmsCreditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * SuperAdmin UI for the Paystack integration: API credentials, the per-SMS
 * price (used to quote SMS-credit top-ups), and the per-flow enable flags.
 *
 * Values live in the `platform_settings` key/value table so they can be
 * edited via the browser without touching env files. The secret key is
 * stored as-is (it has to be readable by the server when calling Paystack)
 * but is masked in the UI's HTML.
 */
class PaystackSettingsController extends Controller
{
    public const KEY_PUBLIC = 'paystack_public_key';

    public const KEY_SECRET = 'paystack_secret_key';

    public const KEY_ENABLED_SMS = 'paystack_enabled_sms';

    public const KEY_ENABLED_SUB = 'paystack_enabled_subscription';

    public const KEY_SMS_PRICE = 'sms_price_pesewas';

    public const KEYS = [
        self::KEY_PUBLIC,
        self::KEY_SECRET,
        self::KEY_ENABLED_SMS,
        self::KEY_ENABLED_SUB,
        self::KEY_SMS_PRICE,
    ];

    public function __construct(private readonly ActivityLogger $activityLogger) {}

    public function index(SmsCreditService $credits): View
    {
        $this->authorize('platform.manage');

        $settings = PlatformSetting::allCached();

        // Always mask the secret in the rendered HTML; we only echo back
        // whether one is set, plus the last 4 chars for sanity-checking.
        $secret = (string) ($settings[self::KEY_SECRET] ?? '');
        $secretPreview = $secret !== ''
            ? str_repeat('•', max(0, strlen($secret) - 4)).substr($secret, -4)
            : null;

        $pricePesewas = $settings[self::KEY_SMS_PRICE] ?? $credits->pricePerSmsPesewas();
        $smsPerGhs = (float) $pricePesewas > 0
            ? (int) floor(100 / (float) $pricePesewas)
            : 0;

        return view('platform.payments.settings', [
            'publicKey' => (string) ($settings[self::KEY_PUBLIC] ?? ''),
            'hasSecret' => $secret !== '',
            'secretPreview' => $secretPreview,
            'enabledSms' => (bool) ($settings[self::KEY_ENABLED_SMS] ?? '0'),
            'enabledSub' => (bool) ($settings[self::KEY_ENABLED_SUB] ?? '0'),
            'pricePesewas' => $pricePesewas,
            'smsPerGhs' => $smsPerGhs,
        ]);
    }

    public function update(UpdatePaystackSettingsRequest $request): RedirectResponse
    {
        $this->authorize('platform.manage');

        $data = $request->validated();

        $rows = [
            self::KEY_PUBLIC => [
                'value' => (string) ($data['paystack_public_key'] ?? ''),
                'type' => 'string',
                'group' => 'payments',
            ],
            self::KEY_ENABLED_SMS => [
                'value' => $request->boolean('paystack_enabled_sms') ? '1' : '0',
                'type' => 'bool',
                'group' => 'payments',
            ],
            self::KEY_ENABLED_SUB => [
                'value' => $request->boolean('paystack_enabled_subscription') ? '1' : '0',
                'type' => 'bool',
                'group' => 'payments',
            ],
            self::KEY_SMS_PRICE => [
                'value' => (string) ($data['sms_price_pesewas'] ?? SmsCreditService::DEFAULT_PRICE_PESEWAS),
                'type' => 'string',
                'group' => 'payments',
            ],
        ];

        // The secret is only updated if the operator actually typed something.
        // An empty field means "keep the existing key" — otherwise rotating
        // unrelated controls (e.g. toggling enable flags) would wipe the key.
        $newSecret = trim((string) ($data['paystack_secret_key'] ?? ''));
        if ($newSecret !== '') {
            $rows[self::KEY_SECRET] = [
                'value' => $newSecret,
                'type' => 'string',
                'group' => 'payments',
            ];
        }

        foreach ($rows as $key => $meta) {
            PlatformSetting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $meta['value'], 'type' => $meta['type'], 'group' => $meta['group']]
            );
        }

        PlatformSetting::forgetCache();

        $this->activityLogger->log(
            'paystack_settings_updated',
            'Paystack integration settings updated',
            [
                'keys' => array_keys($rows),
                'rotated_secret' => $newSecret !== '',
            ],
        );

        return redirect()
            ->route('platform.payments.settings.index')
            ->with('status', __('Paystack settings saved.'));
    }
}
