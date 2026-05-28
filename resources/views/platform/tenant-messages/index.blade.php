@extends('layouts.app')

@section('title', __('Messages'))

@section('header-title', __('Tenant messages'))

@section('content')
    @include('platform.tenants._control-nav', ['tenant' => $tenant])

    <h1 class="text-xl font-semibold text-slate-900">{{ __('School notices') }} — {{ $tenant->name }}</h1>
    <p class="mt-1 text-sm text-slate-600">{{ __('View only for support and audit. Notices are one-way.') }}</p>

    <div class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('Sent') }}</th>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('Title') }}</th>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('Sender') }}</th>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('Audience') }}</th>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('Channel') }}</th>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($messages as $m)
                    <tr>
                        <td class="px-4 py-3 text-slate-600">{{ $m->sent_at?->toDateTimeString() ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $m->title }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $m->sender?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $m->audienceDisplay() }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $m->channel }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $m->status }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $messages->links() }}</div>
@endsection
