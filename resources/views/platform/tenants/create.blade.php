@extends('layouts.app')

@section('title', __('Create school'))

@section('header-title', __('Create school'))

@section('content')
    <h1 class="text-2xl font-semibold text-slate-900">{{ __('Create school') }}</h1>
    <p class="mt-1 text-sm text-slate-600">
        {{ __('Provision a new tenant. Choose a Ghanaian basic-school template — the system will create the classes, subjects and terms for you. You can edit everything after setup.') }}
    </p>

    @php
        $defaultTemplateCode = old('school_template_code', \App\Models\SchoolTemplate::CODE_PRIMARY_JHS);
    @endphp

    <form id="create-school-form" method="POST" action="{{ route('platform.tenants.store') }}" class="mt-8 space-y-8">
        @csrf

        {{-- ─────────────── 1. School profile ─────────────── --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="text-base font-semibold text-slate-900">{{ __('1. School profile') }}</h2>
            <p class="mt-1 text-sm text-slate-600">{{ __('Basic identity for the new school.') }}</p>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700" for="name">{{ __('School name') }}</label>
                    <input id="name" name="name" type="text" required value="{{ old('name') }}"
                           placeholder="{{ __('Example: Adom Grace Academy') }}"
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700" for="subdomain">{{ __('Subdomain') }}</label>
                    <input id="subdomain" name="subdomain" type="text" required value="{{ old('subdomain') }}"
                           placeholder="{{ __('Example: adom-grace') }}"
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                    <p class="mt-1 text-xs text-slate-500">{{ __('Used for the school\'s portal address. Lowercase letters, digits and hyphens only.') }}</p>
                    @error('subdomain')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700" for="plan_id">{{ __('Plan') }}</label>
                    <select id="plan_id" name="plan_id"
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                        <option value="">{{ __('None') }}</option>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>{{ $plan->name }}</option>
                        @endforeach
                    </select>
                    @error('plan_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700" for="status">{{ __('Initial status') }}</label>
                    <select id="status" name="status"
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                        <option value="trial" @selected(old('status', 'trial') === 'trial')>{{ __('Trial') }}</option>
                        <option value="active" @selected(old('status') === 'active')>{{ __('Active') }}</option>
                        <option value="suspended" @selected(old('status') === 'suspended')>{{ __('Suspended') }}</option>
                    </select>
                    @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        {{-- ─────────────── 2. School Setup Template ─────────────── --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="text-base font-semibold text-slate-900">{{ __('2. School Setup Template') }}</h2>
            <p class="mt-1 text-sm text-slate-600">
                {{ __('Choose a starting structure for this school. You can edit classes, subjects, terms and fees after setup.') }}
            </p>

            @if ($templates->isEmpty())
                <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    {{ __('No school templates are seeded yet. Run: php artisan db:seed --class=GhanaBasicSchoolTemplateSeeder') }}
                </div>
            @else
                <div class="mt-5 grid gap-3 sm:grid-cols-2" id="template-cards">
                    @foreach ($templates as $tpl)
                        <label class="template-card block cursor-pointer rounded-xl border border-slate-200 p-4 transition hover:border-primary"
                               data-code="{{ $tpl->code }}">
                            <div class="flex items-start gap-3">
                                <input type="radio" name="school_template_code" value="{{ $tpl->code }}"
                                       @checked($defaultTemplateCode === $tpl->code)
                                       class="mt-1 text-primary focus:ring-primary template-radio">
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-slate-900">{{ $tpl->name }}</div>
                                    <div class="mt-1 text-xs text-slate-600">{{ $tpl->description }}</div>
                                    @if ($tpl->curriculum_label)
                                        <div class="mt-2 inline-block rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600">{{ $tpl->curriculum_label }}</div>
                                    @endif
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('school_template_code')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror

                <label id="include-kg-row" class="mt-5 flex items-start gap-3 {{ $defaultTemplateCode === \App\Models\SchoolTemplate::CODE_FULL_BASIC ? '' : 'hidden' }}">
                    <input id="include_kg" type="checkbox" name="include_kg" value="1"
                           @checked(old('include_kg'))
                           class="mt-0.5 rounded border-slate-300 text-primary focus:ring-primary">
                    <div>
                        <div class="text-sm font-medium text-slate-800">{{ __('Include Kindergarten classes') }}</div>
                        <div class="text-xs text-slate-500">{{ __('Turn this on if the school has KG 1 and KG 2.') }}</div>
                    </div>
                </label>
            @endif
        </section>

        {{-- ─────────────── 3. Academic year ─────────────── --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="text-base font-semibold text-slate-900">{{ __('3. Academic year') }}</h2>
            <p class="mt-1 text-sm text-slate-600">{{ __('The system will create Term 1, Term 2 and Term 3, with Term 1 set as the active term.') }}</p>

            <div class="mt-5 max-w-sm">
                <label class="block text-sm font-medium text-slate-700" for="academic_year_name">{{ __('Academic year name') }}</label>
                <input id="academic_year_name" name="academic_year_name" type="text" value="{{ old('academic_year_name') }}"
                       placeholder="{{ __('Example: 2026/2027') }}"
                       class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                <p class="mt-1 text-xs text-slate-500">{{ __('Leave blank to auto-name based on the current year.') }}</p>
                @error('academic_year_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="mt-5 text-xs text-slate-500">
                <span class="font-semibold text-slate-700">{{ __('Terms to be created:') }}</span>
                Term 1 ({{ __('active') }}) · Term 2 · Term 3
                <span class="ml-1">— {{ __('these can be edited later from Academic Settings.') }}</span>
            </div>
        </section>

        {{-- ─────────────── 4. Default subjects ─────────────── --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="text-base font-semibold text-slate-900">{{ __('4. Default Ghanaian Subjects') }}</h2>
            <p class="mt-1 text-sm text-slate-600">
                {{ __('We will add the standard Ghanaian basic school subjects for the selected levels. You can rename, remove or add subjects later.') }}
            </p>
        </section>

        {{-- ─────────────── 5. Fee placeholders ─────────────── --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="text-base font-semibold text-slate-900">{{ __('5. Default Fee Items') }}</h2>
            <label class="mt-3 flex items-start gap-3">
                <input id="create_default_fees" type="checkbox" name="create_default_fees" value="1"
                       @checked(old('create_default_fees'))
                       class="mt-0.5 rounded border-slate-300 text-primary focus:ring-primary">
                <div>
                    <div class="text-sm font-medium text-slate-800">{{ __('Create default fee item placeholders') }}</div>
                    <div class="text-xs text-slate-500">
                        {{ __('This will create empty fee items such as Tuition Fee, PTA Dues, Examination Fee, ICT Fee, Sports Fee and Maintenance Fee. Amounts will be left blank for the school to fill in.') }}
                    </div>
                </div>
            </label>
        </section>

        {{-- ─────────────── 6. School admin ─────────────── --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="text-base font-semibold text-slate-900">{{ __('6. School administrator') }}</h2>
            <p class="mt-1 text-sm text-slate-600">{{ __('A temporary password is generated and written to a one-time credentials file. The admin is forced to change it on first sign-in.') }}</p>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700" for="admin_name">{{ __('Admin full name') }}</label>
                    <input id="admin_name" name="admin_name" type="text" value="{{ old('admin_name') }}"
                           placeholder="{{ __('Example: Ama Mensah') }}"
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                    @error('admin_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700" for="admin_email">{{ __('Admin email') }}</label>
                    <input id="admin_email" name="admin_email" type="email" value="{{ old('admin_email') }}"
                           placeholder="{{ __('Example: ama.mensah@adomgrace.edu.gh') }}"
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                    <p class="mt-1 text-xs text-slate-500">{{ __('Required to apply the template. Without an admin email, only a bare tenant row is created.') }}</p>
                    @error('admin_email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        {{-- ─────────────── Review ─────────────── --}}
        <section class="rounded-2xl border border-primary/40 bg-primary/5 p-6">
            <h2 class="text-base font-semibold text-slate-900">{{ __('Review School Setup') }}</h2>
            <ul class="mt-4 space-y-2 text-sm text-slate-700">
                <li><span class="font-medium text-slate-900">{{ __('Template:') }}</span> <span id="review-template">—</span></li>
                <li><span class="font-medium text-slate-900">{{ __('Academic year:') }}</span> <span id="review-year">{{ now()->year.'/'.(now()->year + 1) }}</span></li>
                <li><span class="font-medium text-slate-900">{{ __('Terms:') }}</span> Term 1 ({{ __('active') }}), Term 2, Term 3</li>
                <li><span class="font-medium text-slate-900">{{ __('Kindergarten included:') }}</span> <span id="review-kg">{{ __('No') }}</span></li>
                <li><span class="font-medium text-slate-900">{{ __('Default fee placeholders:') }}</span> <span id="review-fees">{{ __('No') }}</span></li>
            </ul>
        </section>

        <div class="flex gap-3">
            <button type="submit"
                    class="rounded-lg bg-slate-950 px-6 py-3 text-sm font-semibold text-white transition hover:bg-primary">
                {{ __('Create School') }}
            </button>
            <a href="{{ route('platform.tenants.index') }}"
               class="rounded-lg border border-slate-300 px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                {{ __('Cancel') }}
            </a>
        </div>
    </form>

    <script>
        (function () {
            'use strict';
            var form = document.getElementById('create-school-form');
            if (!form) return;

            var FULL_BASIC = @json(\App\Models\SchoolTemplate::CODE_FULL_BASIC);
            var YES = @json(__('Yes'));
            var NO  = @json(__('No'));
            var DASH = '—';

            var cards = form.querySelectorAll('.template-card');
            var radios = form.querySelectorAll('.template-radio');
            var includeKgRow = document.getElementById('include-kg-row');
            var includeKgCheckbox = document.getElementById('include_kg');
            var createFeesCheckbox = document.getElementById('create_default_fees');
            var yearInput = document.getElementById('academic_year_name');

            var reviewTemplate = document.getElementById('review-template');
            var reviewYear     = document.getElementById('review-year');
            var reviewKg       = document.getElementById('review-kg');
            var reviewFees     = document.getElementById('review-fees');

            function refresh() {
                var selected = form.querySelector('.template-radio:checked');
                var code = selected ? selected.value : '';

                cards.forEach(function (card) {
                    var on = card.dataset.code === code;
                    card.classList.toggle('border-primary', on);
                    card.classList.toggle('bg-primary/5', on);
                    card.classList.toggle('ring-1', on);
                    card.classList.toggle('ring-primary/30', on);
                });

                if (includeKgRow) {
                    includeKgRow.classList.toggle('hidden', code !== FULL_BASIC);
                }

                if (reviewTemplate) {
                    var label = selected && selected.closest('label');
                    var name = label ? label.querySelector('.text-sm.font-semibold') : null;
                    var desc = label ? label.querySelector('.text-xs.text-slate-600') : null;
                    reviewTemplate.textContent = name
                        ? (name.textContent.trim() + (desc ? ' — ' + desc.textContent.trim() : ''))
                        : DASH;
                }
                if (reviewYear) {
                    reviewYear.textContent = (yearInput && yearInput.value.trim()) || reviewYear.dataset.fallback || reviewYear.textContent;
                }
                if (reviewKg) {
                    reviewKg.textContent = (code === FULL_BASIC && includeKgCheckbox && includeKgCheckbox.checked) ? YES : NO;
                }
                if (reviewFees) {
                    reviewFees.textContent = (createFeesCheckbox && createFeesCheckbox.checked) ? YES : NO;
                }
            }

            if (reviewYear) {
                reviewYear.dataset.fallback = reviewYear.textContent;
            }
            radios.forEach(function (r) { r.addEventListener('change', refresh); });
            cards.forEach(function (c) { c.addEventListener('click', function () { /* radio click bubbles up; refresh on change */ }); });
            if (includeKgCheckbox) includeKgCheckbox.addEventListener('change', refresh);
            if (createFeesCheckbox) createFeesCheckbox.addEventListener('change', refresh);
            if (yearInput) yearInput.addEventListener('input', refresh);

            refresh();
        })();
    </script>
@endsection
