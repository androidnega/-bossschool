@extends('layouts.app')

@section('title', __('Ticket #:id', ['id' => $ticket->id]))
@section('header-title', $ticket->subject)

@section('content')
    @if(session('status'))
        <div class="mb-4 rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
    @endif

    <p class="mb-2 text-xs text-slate-500">{{ __('Tenant') }}: <strong>{{ $ticket->tenant?->name }}</strong></p>

    <div class="mb-4 rounded-xl border border-slate-200 bg-white p-4 text-sm">
        <div class="mb-2 flex flex-wrap items-center gap-2">
            <span class="rounded bg-slate-100 px-2 py-0.5 text-xs">{{ $ticket->status }}</span>
            <span class="rounded bg-slate-100 px-2 py-0.5 text-xs">{{ $ticket->priority }}</span>
            <span class="rounded bg-slate-100 px-2 py-0.5 text-xs">{{ $ticket->category }}</span>
        </div>
        <p class="text-slate-700"><strong>{{ $ticket->creator?->name }}</strong> · {{ $ticket->created_at?->diffForHumans() }}</p>
        <p class="mt-2 whitespace-pre-line">{{ $ticket->body }}</p>
    </div>

    <div class="space-y-3">
        @foreach($ticket->messages as $m)
            <div class="rounded-xl border {{ $m->is_internal_note ? 'border-amber-300 bg-amber-50' : 'border-slate-200 bg-white' }} p-3 text-sm">
                <div class="text-xs text-slate-500">{{ $m->author?->name }} · {{ $m->created_at?->diffForHumans() }} @if($m->is_internal_note) — <strong class="text-amber-700">{{ __('Internal note') }}</strong> @endif</div>
                <p class="mt-1 whitespace-pre-line">{{ $m->body }}</p>
            </div>
        @endforeach
    </div>

    @if($ticket->status !== 'closed')
        <form method="POST" action="{{ route('support.reply', $ticket) }}" enctype="multipart/form-data" class="mt-4 space-y-2 rounded-xl border border-slate-200 bg-white p-3 text-sm">
            @csrf
            <textarea name="body" rows="4" required class="w-full rounded-md border border-slate-300 px-2 py-1.5"></textarea>
            <label class="flex items-center gap-1 text-xs"><input type="checkbox" name="is_internal_note" value="1" /> {{ __('Internal note (not visible to school)') }}</label>
            <button type="submit" class="rounded-md bg-primary px-3 py-1.5 text-white">{{ __('Send reply') }}</button>
        </form>
    @endif

    <form method="POST" action="{{ route('support.change-status', $ticket) }}" class="mt-3 flex items-center gap-2 text-xs">
        @csrf
        <select name="status" class="rounded-md border border-slate-300 px-2 py-1">
            @foreach(\App\Models\SupportTicket::STATUSES as $s)
                <option value="{{ $s }}" @if($s===$ticket->status) selected @endif>{{ $s }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-md border border-slate-300 px-2 py-1">{{ __('Change status') }}</button>
    </form>
@endsection
