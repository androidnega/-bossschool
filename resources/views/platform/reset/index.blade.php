@extends('layouts.app')

@section('title', __('Safe reset'))

@section('header-title', __('Operational data reset'))

@section('content')
    @php
        $summary = session('reset_summary');
    @endphp

    @if ($summary)
        <div class="mb-6 rounded-xl border-2 border-emerald-300 bg-emerald-50 p-6 shadow-sm" role="status" aria-live="polite">
            <div class="flex items-start gap-4">
                <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-white">
                    <i class="fa-solid fa-circle-check text-xl" aria-hidden="true"></i>
                </div>
                <div class="flex-1">
                    <h2 class="text-lg font-semibold text-emerald-900">
                        @if ($summary['scope'] === 'all')
                            {{ __('All school data reset (:n tenants)', ['n' => $summary['tenants_affected']]) }}
                        @else
                            {{ __('Reset complete for :name (:sub)', ['name' => $summary['tenant_name'], 'sub' => $summary['tenant_subdomain']]) }}
                        @endif
                    </h2>
                    <p class="mt-1 text-sm text-emerald-800">
                        {{ __(':n total rows removed.', ['n' => number_format($summary['total_rows'])]) }}
                        @if ($summary['scope'] === 'single' && ! empty($summary['snapshot_path']))
                            {{ __('Snapshot saved.') }}
                        @endif
                    </p>

                    @if (! empty($summary['counts']))
                        <details class="mt-4 group" open>
                            <summary class="cursor-pointer text-xs font-semibold uppercase tracking-wider text-emerald-700 hover:text-emerald-900">
                                {{ __('Breakdown by table') }}
                            </summary>
                            <div class="mt-3 grid grid-cols-2 gap-x-6 gap-y-1 text-sm sm:grid-cols-3 md:grid-cols-4">
                                @foreach ($summary['counts'] as $table => $count)
                                    <div class="flex items-baseline justify-between gap-3 border-b border-emerald-200/70 py-1.5">
                                        <span class="text-emerald-800 capitalize">{{ str_replace('_', ' ', $table) }}</span>
                                        <span class="tabular-nums font-semibold text-emerald-900">{{ number_format((int) $count) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </details>
                    @endif

                    @if ($summary['scope'] === 'single' && ! empty($summary['snapshot_path']))
                        <p class="mt-4 break-all text-xs text-emerald-800">
                            <span class="font-semibold">{{ __('Snapshot file:') }}</span>
                            <code class="rounded bg-white px-1.5 py-0.5 font-mono text-[11px] text-emerald-900 border border-emerald-200">{{ $summary['snapshot_path'] }}</code>
                        </p>
                    @endif
                    @if ($summary['scope'] === 'all' && ! empty($summary['snapshots']))
                        <p class="mt-4 text-xs text-emerald-800">{{ __(':n snapshot files written.', ['n' => count(array_filter($summary['snapshots']))]) }}</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="dash-card-danger rounded-xl border border-rose-200 p-5">
        <h1 class="flex items-center gap-2 text-xl font-semibold text-rose-950">
            <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
            {{ __('Danger zone') }}
        </h1>
        <p class="mt-2 text-sm text-rose-900/90">{{ __('These tools remove class, fee, payment, result, attendance, and message data for schools. Tenants, users, school profile rows, plans, and subscription records stay.') }}</p>
        <ul class="mt-3 list-inside list-disc text-sm text-rose-950/90">
            <li>{{ __('Super Admin only. Tenant staff cannot access this page.') }}</li>
            <li>{{ __('A JSON snapshot of all rows is written before deletion.') }}</li>
            <li>{{ __('You must re-enter your password and type RESET <SUBDOMAIN> exactly.') }}</li>
            <li>{{ __('Runs in a database transaction per tenant. Detailed activity logs are written.') }}</li>
        </ul>
    </div>

    <div class="mt-10 grid gap-10 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="flex items-center gap-2 text-lg font-semibold text-slate-900"><i class="fa-solid fa-school text-teal-700" aria-hidden="true"></i>{{ __('Reset one school') }}</h2>
            <p class="mt-2 text-sm text-slate-600">{{ __('Deletes operational rows for the selected tenant only.') }}</p>
            <form method="POST" action="{{ route('platform.reset.tenant') }}" class="mt-4 space-y-4"
                  onsubmit="return confirm(@json(__('Permanently delete operational data for the selected school?')));">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700" for="tenant_id">{{ __('School') }}</label>
                    <select id="tenant_id" name="tenant_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                        @foreach ($tenants as $t)
                            <option value="{{ $t->id }}" data-subdomain="{{ strtoupper($t->subdomain) }}" @selected(old('tenant_id') == $t->id)>{{ $t->name }} ({{ $t->subdomain }})</option>
                        @endforeach
                    </select>
                    @error('tenant_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700" for="confirm_one">{{ __('Type RESET <SUBDOMAIN> to confirm (e.g. RESET DEMO)') }}</label>
                    <input id="confirm_one" name="confirm" type="text" autocomplete="off" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="RESET DEMO" required value="{{ old('confirm') }}">
                    @error('confirm')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700" for="password_one">{{ __('Your password (re-authentication required)') }}</label>
                    <input id="password_one" name="password" type="password" autocomplete="current-password" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                    @error('password')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-rose-700 px-4 py-2 text-sm font-medium text-white hover:bg-rose-800">
                    <i class="fa-solid fa-trash-can" aria-hidden="true"></i>{{ __('Reset selected school data') }}
                </button>
            </form>
        </div>

        <div class="rounded-xl border border-rose-300 bg-rose-50/50 p-5">
            <h2 class="flex items-center gap-2 text-lg font-semibold text-rose-950"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>{{ __('Reset all schools') }}</h2>
            @if ($allowResetAll)
                <p class="mt-2 text-sm text-rose-900/90">{{ __('Runs the same purge for every tenant. Use only on demo stacks.') }}</p>
                <form method="POST" action="{{ route('platform.reset.all') }}" class="mt-4 space-y-4" onsubmit="return confirm(@json(__('This will wipe operational data for ALL tenants. Continue?')));">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-rose-900" for="confirm_all">{{ __('Type RESET ALL TENANTS to confirm') }}</label>
                        <input id="confirm_all" name="confirm" type="text" autocomplete="off" class="mt-1 w-full rounded-lg border border-rose-300 bg-white px-3 py-2 text-sm" placeholder="RESET ALL TENANTS" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-rose-900" for="password_all">{{ __('Your password (re-authentication required)') }}</label>
                        <input id="password_all" name="password" type="password" autocomplete="current-password" class="mt-1 w-full rounded-lg border border-rose-300 bg-white px-3 py-2 text-sm" required>
                    </div>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-rose-900 px-4 py-2 text-sm font-medium text-white hover:bg-black">
                        <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>{{ __('Reset all school demo data') }}
                    </button>
                </form>
            @else
                <p class="mt-2 text-sm text-rose-900/90">{{ __('The "reset all schools" action is disabled in this environment. Set PLATFORM_ALLOW_RESET_ALL=true in your environment to enable it, and consider running the purge from the CLI with a fresh database backup instead.') }}</p>
            @endif
        </div>
    </div>

    @if (isset($recentResets) && $recentResets->isNotEmpty())
        <div class="mt-10 rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="flex items-center gap-2 text-base font-semibold text-slate-900">
                <i class="fa-solid fa-clock-rotate-left text-slate-500" aria-hidden="true"></i>
                {{ __('Recent resets') }}
            </h2>
            <p class="mt-1 text-xs text-slate-500">{{ __('Last ten reset events. Read from the audit log; survives page reloads.') }}</p>

            <ul class="mt-4 divide-y divide-slate-100">
                @foreach ($recentResets as $event)
                    @php
                        $meta = is_array($event->metadata) ? $event->metadata : [];
                        $totalRows = (int) ($meta['total_rows'] ?? 0);
                        $scope = $event->action === 'system_data_reset' ? 'all' : 'single';
                    @endphp
                    <li class="flex items-start gap-3 py-3 text-sm">
                        <div class="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-full {{ $scope === 'all' ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                            <i class="fa-solid {{ $scope === 'all' ? 'fa-triangle-exclamation' : 'fa-check' }} text-xs" aria-hidden="true"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-slate-800">
                                <strong>{{ $event->actor_name ?? __('Unknown') }}</strong>
                                {{ $scope === 'all'
                                    ? __('reset all schools')
                                    : __('reset :school', ['school' => $meta['subdomain'] ?? __('a school')]) }}
                            </p>
                            <p class="text-xs text-slate-500">
                                {{ $event->created_at?->diffForHumans() }}
                                · {{ number_format($totalRows) }} {{ __('rows') }}
                                @if (! empty($meta['snapshot_path']))
                                    · <code class="font-mono text-[11px] text-slate-600">{{ basename($meta['snapshot_path']) }}</code>
                                @endif
                            </p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
@endsection
