@props([
    'title',
    'message' => null,
])
<div {{ $attributes->merge(['class' => 'rounded-lg border border-dashed border-gray-300 bg-page px-6 py-12 text-center']) }}>
    <p class="font-medium text-gray-900">{{ $title }}</p>
    @if ($message)
        <p class="mt-2 text-sm text-gray-600">{{ $message }}</p>
    @endif
    {{ $slot }}
</div>
