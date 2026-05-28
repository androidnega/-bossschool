@extends('layouts.app')

@section('title', __('Communication logs'))

@section('header-title', 'Messages')

@section('content')
    <div class="mb-4">
        <h1 class="text-2xl font-semibold text-primary">{{ __('Communication logs') }}</h1>
        <p class="mt-1 text-sm text-gray-600">{{ __('SMS-ready outbox. No external provider is wired yet — entries sit as queued until you connect one.') }}</p>
    </div>

    <form method="GET" class="mb-4 flex flex-wrap gap-2 text-sm">
        <select name="channel" class="rounded-md border border-gray-300 bg-page px-2 py-1.5">
            <option value="">{{ __('Any channel') }}</option>
            @foreach (\App\Models\CommunicationLog::CHANNELS as $c)<option value="{{ $c }}" @selected(request('channel') === $c)>{{ ucfirst($c) }}</option>@endforeach
        </select>
        <select name="purpose" class="rounded-md border border-gray-300 bg-page px-2 py-1.5">
            <option value="">{{ __('Any purpose') }}</option>
            @foreach (\App\Models\CommunicationLog::PURPOSES as $p)<option value="{{ $p }}" @selected(request('purpose') === $p)>{{ ucwords(str_replace('_', ' ', $p)) }}</option>@endforeach
        </select>
        <select name="status" class="rounded-md border border-gray-300 bg-page px-2 py-1.5">
            <option value="">{{ __('Any status') }}</option>
            @foreach (['queued','sent','failed','skipped'] as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>@endforeach
        </select>
        <button class="rounded-md border border-gray-300 bg-page px-3 py-1.5">{{ __('Filter') }}</button>
    </form>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-page">
        <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
            <thead class="bg-page-soft">
                <tr>
                    <th class="px-4 py-3">{{ __('When') }}</th>
                    <th class="px-4 py-3">{{ __('Recipient') }}</th>
                    <th class="px-4 py-3">{{ __('Channel') }}</th>
                    <th class="px-4 py-3">{{ __('Purpose') }}</th>
                    <th class="px-4 py-3">{{ __('Subject') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($logs as $l)
                    <tr>
                        <td class="px-4 py-3">{{ $l->created_at?->format('M j, H:i') }}</td>
                        <td class="px-4 py-3">{{ $l->recipient?->name ?? $l->recipient_phone ?? '—' }}</td>
                        <td class="px-4 py-3">{{ ucfirst($l->channel) }}</td>
                        <td class="px-4 py-3">{{ ucwords(str_replace('_', ' ', $l->purpose)) }}</td>
                        <td class="px-4 py-3">{{ $l->subject ?? \Illuminate\Support\Str::limit($l->message, 60) }}</td>
                        <td class="px-4 py-3"><span class="inline-flex rounded-full bg-page-soft px-2 py-0.5 text-xs font-medium capitalize">{{ $l->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">{{ __('No communication logs yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($logs->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">{{ $logs->links() }}</div>
        @endif
    </div>
@endsection
