@extends('layouts.app')

@section('title', __('Admin dashboard'))

@section('header-title', __('Admin'))

@section('content')
    @php
        $adminActions = [
            ['href' => route('students.create'), 'icon' => 'fa-solid fa-user-plus', 'label' => __('Add student'), 'style' => 'primary'],
            ['href' => route('staff.index'), 'icon' => 'fa-solid fa-id-badge', 'label' => __('Staff'), 'style' => 'default'],
            ['href' => route('classes.index'), 'icon' => 'fa-solid fa-users-between-lines', 'label' => __('Classes'), 'style' => 'default'],
            ['href' => route('terms.index'), 'icon' => 'fa-solid fa-calendar-days', 'label' => __('Terms'), 'style' => 'default'],
        ];
    @endphp

    <div class="rounded-3xl bg-stone-100/60 p-4 ring-1 ring-stone-200/50 sm:p-6">
        <div class="mx-auto max-w-6xl space-y-8 pb-2">
            <header class="glass-panel rounded-3xl p-6 sm:p-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0 space-y-3">
                        <p class="text-[0.65rem] font-semibold uppercase tracking-[0.14em] text-stone-500">{{ __('School operations') }}</p>
                        <h1 class="text-2xl font-semibold tracking-tight text-stone-900 sm:text-3xl">{{ __('Operations overview') }}</h1>
                        <p class="max-w-xl text-sm leading-relaxed text-stone-600 sm:text-[15px]">{{ __('People, classes, attendance, and notices.') }}</p>
                    </div>
                    <div class="flex shrink-0 flex-wrap gap-2">
                        @can('viewAny', \App\Models\Message::class)
                            <a href="{{ route('messages.index') }}" class="inline-flex items-center gap-2 rounded-2xl border border-primary/30 bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-stone-900/10 transition hover:bg-primary/95">
                                <i class="fa-solid fa-bullhorn" aria-hidden="true"></i>
                                {{ __('Send notice') }}
                            </a>
                        @endcan
                    </div>
                </div>
            </header>

            <section class="space-y-3" aria-labelledby="admin-quick-actions">
                <h2 id="admin-quick-actions" class="text-[0.65rem] font-semibold uppercase tracking-[0.16em] text-stone-500">{{ __('Quick actions') }}</h2>
                <ul class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach ($adminActions as $action)
                        @php
                            $isPrimary = ($action['style'] ?? '') === 'primary';
                            $tile = $isPrimary
                                ? 'flex min-h-[5.25rem] flex-col justify-between gap-3 rounded-2xl border border-primary/35 bg-primary p-4 text-white shadow-md shadow-stone-900/10 transition-colors duration-150 hover:border-primary/50 hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 focus-visible:ring-offset-2 sm:min-h-[5.5rem]'
                                : 'glass-tile glass-panel-subtle flex min-h-[5.25rem] flex-col justify-between gap-3 rounded-2xl border border-stone-200/70 p-4 text-stone-800 sm:min-h-[5.5rem]';
                            $iconBox = $isPrimary
                                ? 'flex size-10 shrink-0 items-center justify-center rounded-xl bg-white/20 text-white ring-1 ring-white/25'
                                : 'flex size-10 shrink-0 items-center justify-center rounded-xl bg-white/90 text-primary ring-1 ring-stone-200/60';
                        @endphp
                        <li>
                            <a href="{{ $action['href'] }}" class="{{ $tile }}">
                                <span class="{{ $iconBox }}">
                                    <i class="{{ $action['icon'] }} text-sm" aria-hidden="true"></i>
                                </span>
                                <span class="text-xs font-semibold sm:text-[13px] {{ $isPrimary ? 'text-white' : 'text-stone-900' }}">{{ $action['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>

            @if (count($setupReminders))
                <div class="glass-panel rounded-2xl border-rose-200/60 p-4 sm:p-5">
                    <p class="flex items-center gap-2 text-sm font-semibold text-rose-950">
                        <i class="fa-solid fa-circle-exclamation text-rose-600" aria-hidden="true"></i>
                        {{ __('Setup reminders') }}
                    </p>
                    <ul class="mt-3 list-inside list-disc space-y-1.5 text-sm text-rose-900/90">
                        @foreach ($setupReminders as $r)
                            <li>{{ $r }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="space-y-3" aria-labelledby="admin-metrics">
                <h2 id="admin-metrics" class="text-[0.65rem] font-semibold uppercase tracking-[0.16em] text-stone-500">{{ __('At a glance') }}</h2>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <x-dash-card icon="fa-solid fa-user-graduate" :label="__('Students')" :value="number_format($studentCount)" variant="students" class="glass-tile glass-panel-subtle !rounded-2xl !border-stone-200/60" />
                    <x-dash-card icon="fa-solid fa-user-plus" :label="__('New admissions (30d)')" :value="number_format($newAdmissions)" variant="revenue" class="glass-tile glass-panel-subtle !rounded-2xl !border-stone-200/60" />
                    <x-dash-card icon="fa-solid fa-users-between-lines" :label="__('Classes')" :value="number_format($classCount)" variant="attendance" class="glass-tile glass-panel-subtle !rounded-2xl !border-stone-200/60" />
                    <x-dash-card icon="fa-solid fa-id-badge" :label="__('Staff')" :value="number_format($staffCount)" variant="staff" class="glass-tile glass-panel-subtle !rounded-2xl !border-stone-200/60" />
                </div>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <x-dash-card icon="fa-solid fa-calendar-check" :label="__('Attendance rows today')" :value="number_format($attendanceToday)" variant="attendance" class="glass-tile glass-panel-subtle !rounded-2xl !border-stone-200/60" />
                    <x-dash-card icon="fa-solid fa-user-xmark" :label="__('Absent today')" :value="number_format($absentToday)" variant="debtors" class="glass-tile glass-panel-subtle !rounded-2xl !border-stone-200/60" />
                    <x-dash-card icon="fa-solid fa-user-clock" :label="__('Non-active students')" :value="number_format($pendingInactive)" variant="neutral" class="glass-tile glass-panel-subtle !rounded-2xl !border-stone-200/60 sm:col-span-2 lg:col-span-1" />
                </div>
            </section>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <section class="glass-panel rounded-2xl p-0">
                    <h2 class="border-b border-stone-200/60 px-4 py-3 text-sm font-semibold text-stone-900">{{ __('Recent students') }}</h2>
                    <ul class="divide-y divide-stone-200/40">
                        @foreach ($recentStudents as $s)
                            <li class="flex items-center gap-3 px-4 py-3.5 transition hover:bg-white/50">
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-full border border-stone-200/60 bg-white/70 text-sm text-primary backdrop-blur-sm">
                                    <i class="fa-solid fa-user" aria-hidden="true"></i>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-medium text-stone-900">{{ $s->name }}</p>
                                    <p class="truncate text-xs text-stone-500">{{ $s->schoolClass?->name ?? '—' }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <section class="glass-panel rounded-2xl p-4 sm:p-5">
                    <h2 class="text-sm font-semibold text-stone-900">{{ __('Recent school notices') }}</h2>
                    <ul class="mt-4 space-y-3">
                        @forelse ($recentMessages as $m)
                            <li class="glass-tile glass-panel-subtle rounded-2xl border border-stone-200/70 p-4">
                                <p class="text-[0.65rem] font-semibold uppercase tracking-wide text-primary">{{ __('Notice') }}</p>
                                @if ($m->title)
                                    <p class="mt-1 font-medium text-stone-900">{{ $m->title }}</p>
                                @endif
                                <p class="mt-1 text-xs text-stone-500">{{ $m->sent_at?->diffForHumans() }} · {{ $m->sender?->name ?? __('System') }} · {{ $m->notice_kind ?? $m->channel ?? '—' }} · {{ $m->status }}</p>
                                <p class="mt-1 text-xs text-stone-600">{{ $m->audienceDisplay() }}</p>
                                <p class="mt-2 text-sm leading-relaxed text-stone-700">{{ \Illuminate\Support\Str::limit($m->content, 140) }}</p>
                            </li>
                        @empty
                            <li class="rounded-2xl border border-dashed border-stone-200/80 bg-white/40 px-4 py-10 text-center text-sm text-stone-500 backdrop-blur-sm">{{ __('No notices yet.') }}</li>
                        @endforelse
                    </ul>
                </section>
            </div>
        </div>
    </div>
@endsection
