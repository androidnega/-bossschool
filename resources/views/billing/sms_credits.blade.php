@extends('layouts.app')

@section('title', __('SMS credits'))

@section('header-title', __('Billing'))

@section('content')
    @include('billing._subnav')

    <h1 class="text-2xl font-semibold text-primary">{{ __('SMS credits') }}</h1>
    <p class="mt-1 text-sm text-gray-600">
        {{ __('Each SMS recipient costs :p pesewas. Buy credits to send fee reminders, attendance alerts, report cards, and announcements.', ['p' => $pricePesewas]) }}
    </p>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">

        <div class="lg:col-span-1">
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-6">
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">{{ __('Current balance') }}</p>
                <p class="mt-2 text-4xl font-bold tabular-nums text-emerald-900">{{ number_format($balance) }}</p>
                <p class="mt-1 text-xs text-emerald-700">{{ __('SMS messages remaining') }}</p>
            </div>

            <div class="mt-4 rounded-xl border border-slate-200 bg-white p-6">
                <p class="text-sm font-semibold text-slate-800">{{ __('How billing works') }}</p>
                <ul class="mt-2 list-inside list-disc space-y-1 text-xs text-slate-600">
                    <li>{{ __('One credit = one SMS to one recipient.') }}</li>
                    <li>{{ __('A bulk message to 100 students = 100 credits.') }}</li>
                    <li>{{ __('Failed sends are automatically refunded.') }}</li>
                    <li>{{ __('Top-ups are processed instantly through Paystack.') }}</li>
                </ul>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Buy SMS credits') }}</h2>

                @if (! $paystackEnabled)
                    <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                        {{ __('Paystack purchases are not enabled. Contact your platform administrator.') }}
                    </div>
                @else
                    <p class="mt-1 text-sm text-slate-600">{{ __('Pick a bundle. You will be taken to Paystack to complete the payment.') }}</p>

                    <form method="POST" action="{{ route('billing.sms-credits.purchase') }}" class="mt-6 space-y-4" id="sms-credits-form">
                        @csrf
                        <fieldset>
                            <legend class="sr-only">{{ __('Select a bundle') }}</legend>
                            <div class="grid gap-3 sm:grid-cols-2 md:grid-cols-3">
                                @foreach ($bundles as $i => $bundle)
                                    <label class="group cursor-pointer rounded-lg border border-slate-200 bg-slate-50 p-4 transition hover:border-emerald-500 hover:bg-emerald-50 has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50 has-[:checked]:ring-1 has-[:checked]:ring-emerald-300">
                                        <input type="radio" name="credits" value="{{ $bundle['credits'] }}"
                                               class="peer sr-only"
                                               @checked($i === 1)>
                                        <div class="flex items-center justify-between">
                                            <span class="text-2xl font-semibold tabular-nums text-slate-900">{{ number_format($bundle['credits']) }}</span>
                                            <i class="fa-solid fa-check rounded-full bg-emerald-600 px-1.5 py-1 text-xs text-white opacity-0 peer-checked:opacity-100" aria-hidden="true"></i>
                                        </div>
                                        <p class="mt-1 text-xs uppercase tracking-wider text-slate-500">{{ __('SMS messages') }}</p>
                                        <p class="mt-3 text-sm font-medium text-emerald-700">{{ cedis($bundle['cost_ghs']) }}</p>
                                    </label>
                                @endforeach
                            </div>

                            <div class="mt-6 rounded-lg border border-slate-200 bg-slate-50 p-4">
                                <label for="credits_custom" class="block text-sm font-medium text-slate-700">{{ __('Or enter a custom amount') }}</label>
                                <div class="mt-2 flex items-center gap-3">
                                    <input id="credits_custom" type="number" min="1" max="1000000" step="1"
                                           placeholder="{{ __('e.g. 2,500') }}"
                                           class="w-40 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                    <span class="text-sm text-slate-500">SMS = <strong id="credits_custom_cost" class="font-semibold text-emerald-700">{{ cedis(0) }}</strong></span>
                                </div>
                            </div>
                        </fieldset>

                        @error('credits')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror

                        <div class="border-t border-slate-200 pt-4">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-emerald-700">
                                <i class="fa-solid fa-credit-card" aria-hidden="true"></i>
                                {{ __('Continue to Paystack') }}
                            </button>
                            <a href="{{ route('billing.index') }}" class="ml-2 inline-flex items-center px-3 py-2 text-sm text-slate-600 hover:text-slate-900">{{ __('Cancel') }}</a>
                        </div>
                    </form>

                    @push('scripts')
                        <script>
                            (function() {
                                const PRICE_PESEWAS = parseFloat({{ json_encode($pricePesewas) }}) || 0.38;
                                const form = document.getElementById('sms-credits-form');
                                if (!form) return;
                                const custom = document.getElementById('credits_custom');
                                const costLabel = document.getElementById('credits_custom_cost');

                                function formatGhs(amount) {
                                    return 'GHS ' + amount.toFixed(2);
                                }

                                custom.addEventListener('input', function() {
                                    const n = parseInt(custom.value, 10);
                                    if (!isFinite(n) || n <= 0) {
                                        costLabel.textContent = formatGhs(0);
                                        return;
                                    }
                                    const ghs = Math.round((n * PRICE_PESEWAS / 100) * 100) / 100;
                                    costLabel.textContent = formatGhs(ghs);

                                    // Override the selected radio with the custom value just before submit.
                                    form.querySelectorAll('input[name="credits"]').forEach(r => r.checked = false);
                                    let hidden = form.querySelector('input[type="hidden"][name="credits"]');
                                    if (!hidden) {
                                        hidden = document.createElement('input');
                                        hidden.type = 'hidden';
                                        hidden.name = 'credits';
                                        form.appendChild(hidden);
                                    }
                                    hidden.value = String(n);
                                });

                                // Clear custom value when a radio is picked.
                                form.querySelectorAll('input[type="radio"][name="credits"]').forEach(r => {
                                    r.addEventListener('change', function() {
                                        if (custom.value !== '') {
                                            custom.value = '';
                                            costLabel.textContent = formatGhs(0);
                                            const hidden = form.querySelector('input[type="hidden"][name="credits"]');
                                            if (hidden) hidden.remove();
                                        }
                                    });
                                });
                            })();
                        </script>
                    @endpush
                @endif
            </div>

            @if ($recent->isNotEmpty())
                <div class="mt-4 rounded-xl border border-slate-200 bg-white p-6">
                    <h3 class="text-base font-semibold text-slate-900">{{ __('Recent activity') }}</h3>
                    <table class="mt-3 w-full text-left text-sm">
                        <thead class="text-xs uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="py-2 pr-2">{{ __('When') }}</th>
                                <th class="py-2 pr-2">{{ __('Reason') }}</th>
                                <th class="py-2 pr-2 text-right">{{ __('Change') }}</th>
                                <th class="py-2 text-right">{{ __('Balance after') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                        @foreach ($recent as $row)
                            <tr>
                                <td class="py-2 pr-2 text-slate-700">{{ $row->created_at?->diffForHumans() }}</td>
                                <td class="py-2 pr-2 text-slate-600">{{ str_replace('_', ' ', $row->reason) }}</td>
                                <td class="py-2 pr-2 text-right tabular-nums {{ $row->delta > 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                                    {{ $row->delta > 0 ? '+' : '' }}{{ number_format($row->delta) }}
                                </td>
                                <td class="py-2 text-right tabular-nums text-slate-700">{{ number_format($row->balance_after) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
