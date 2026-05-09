@props([
    'icon',
    'label',
    'value',
    'hint' => null,
    'variant' => 'neutral',
])

<div {{ $attributes->class(['dash-card-'.$variant, 'overflow-hidden rounded-xl border border-black/[0.06] p-3 sm:p-4']) }}>
    <div class="flex min-w-0 items-start gap-2.5">
        <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-white/90 text-sm text-slate-800 ring-1 ring-black/[0.06]">
            <i class="{{ $icon }}" aria-hidden="true"></i>
        </div>
        <div class="min-w-0 flex-1">
            <p class="break-words text-[0.7rem] font-semibold uppercase leading-snug tracking-wide text-slate-600 [hyphens:auto]">{{ $label }}</p>
            <p class="mt-1 break-words text-xl font-semibold tracking-tight text-slate-900 tabular-nums sm:text-2xl">{{ $value }}</p>
            @if ($hint)
                <p class="mt-1 break-words text-xs leading-snug text-slate-600">{{ $hint }}</p>
            @endif
        </div>
    </div>
    {{ $slot }}
</div>
