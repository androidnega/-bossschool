@extends('layouts.app')

@section('title', __('Paystack settings'))

@section('header-title', __('Paystack integration'))

@section('content')
    <div class="max-w-3xl space-y-6">
        <div class="rounded-xl border border-slate-200 bg-white p-6">
            <h1 class="text-xl font-semibold text-slate-900">{{ __('Paystack integration') }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ __('Configure the Paystack keys used for SMS-credit top-ups and subscription payments. Keys are stored encrypted at rest and never exposed to schools.') }}</p>

            <form method="POST" action="{{ route('platform.payments.settings.update') }}" class="mt-8 space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700" for="paystack_public_key">{{ __('Public key') }}</label>
                    <input id="paystack_public_key" name="paystack_public_key" type="text"
                           value="{{ old('paystack_public_key', $publicKey) }}"
                           placeholder="pk_test_…"
                           autocomplete="off"
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-sm">
                    <p class="mt-1 text-xs text-slate-500">{{ __('Safe to display. Used by Paystack inline-checkout if you later embed it in the UI.') }}</p>
                    @error('paystack_public_key')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700" for="paystack_secret_key">{{ __('Secret key') }}</label>
                    <input id="paystack_secret_key" name="paystack_secret_key" type="password"
                           value=""
                           placeholder="{{ $hasSecret ? __('Already set (:preview). Leave blank to keep.', ['preview' => $secretPreview]) : 'sk_test_…' }}"
                           autocomplete="new-password"
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-sm">
                    <p class="mt-1 text-xs text-slate-500">{{ __('Required for outbound calls and webhook signature verification. Stored at rest but never echoed back into this form.') }}</p>
                    @error('paystack_secret_key')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <p class="text-sm font-semibold text-slate-800">{{ __('Webhook endpoint') }}</p>
                    <p class="mt-1 break-all rounded bg-white px-2 py-1 font-mono text-xs text-slate-700 border border-slate-200">{{ url('/api/webhooks/payments/paystack') }}</p>
                    <p class="mt-2 text-xs text-slate-500">{{ __('Paste this into your Paystack dashboard → Settings → API Keys & Webhooks. We verify every callback with sha512(HMAC) using the secret above.') }}</p>
                </div>

                <hr class="border-slate-200">

                <div class="flex items-center gap-3">
                    <input id="paystack_enabled_sms" name="paystack_enabled_sms" type="checkbox" value="1"
                           class="size-4 rounded border-slate-300"
                           @checked(old('paystack_enabled_sms', $enabledSms))>
                    <label for="paystack_enabled_sms" class="text-sm text-slate-800">{{ __('Allow schools to buy SMS credits via Paystack') }}</label>
                </div>

                <div class="flex items-center gap-3">
                    <input id="paystack_enabled_subscription" name="paystack_enabled_subscription" type="checkbox" value="1"
                           class="size-4 rounded border-slate-300"
                           @checked(old('paystack_enabled_subscription', $enabledSub))>
                    <label for="paystack_enabled_subscription" class="text-sm text-slate-800">{{ __('Allow schools to pay subscription fees via Paystack') }}</label>
                </div>

                <hr class="border-slate-200">

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700" for="sms_price_pesewas">{{ __('Per-SMS price (pesewas)') }}</label>
                    <input id="sms_price_pesewas" name="sms_price_pesewas" type="number"
                           value="{{ old('sms_price_pesewas', $pricePesewas) }}"
                           step="0.01" min="0.01" max="50"
                           required
                           class="mt-1 w-40 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <p class="mt-1 text-xs text-slate-500">
                        {{ __('Charged once per recipient sent. At :p pesewas, GHS 1.00 buys :n SMS messages.', ['p' => $pricePesewas, 'n' => number_format($smsPerGhs)]) }}
                    </p>
                    @error('sms_price_pesewas')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center justify-between border-t border-slate-200 pt-4">
                    <a href="{{ route('platform.settings.index') }}" class="text-sm text-slate-500 hover:text-slate-700">{{ __('Back to platform settings') }}</a>
                    <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800">{{ __('Save Paystack settings') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
