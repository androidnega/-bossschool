@extends('layouts.app')

@section('title', __('Support inbox'))
@section('header-title', __('Support inbox'))

@section('content')
    <form method="GET" class="mb-3 flex gap-2 text-sm">
        <select name="status" class="rounded-md border border-slate-300 px-2 py-1.5">
            <option value="">{{ __('All statuses') }}</option>
            @foreach($statuses as $s)<option value="{{ $s }}" @if(($filters['status']??null)===$s) selected @endif>{{ $s }}</option>@endforeach
        </select>
        <select name="category" class="rounded-md border border-slate-300 px-2 py-1.5">
            <option value="">{{ __('All categories') }}</option>
            @foreach($categories as $c)<option value="{{ $c }}" @if(($filters['category']??null)===$c) selected @endif>{{ $c }}</option>@endforeach
        </select>
        <button class="rounded-md border border-slate-300 px-2 py-1.5">{{ __('Filter') }}</button>
    </form>

    <div class="rounded-xl border border-slate-200 bg-white">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-3 py-2">{{ __('Tenant') }}</th>
                    <th class="px-3 py-2">{{ __('Subject') }}</th>
                    <th class="px-3 py-2">{{ __('Priority') }}</th>
                    <th class="px-3 py-2">{{ __('Status') }}</th>
                    <th class="px-3 py-2">{{ __('Category') }}</th>
                    <th class="px-3 py-2">{{ __('Created') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $t)
                    <tr class="border-t">
                        <td class="px-3 py-2">{{ $t->tenant?->name }}</td>
                        <td class="px-3 py-2"><a class="text-primary hover:underline" href="{{ route('platform.support.show', $t) }}">{{ $t->subject }}</a></td>
                        <td class="px-3 py-2">{{ $t->priority }}</td>
                        <td class="px-3 py-2">{{ $t->status }}</td>
                        <td class="px-3 py-2">{{ $t->category }}</td>
                        <td class="px-3 py-2">{{ $t->created_at?->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-3 py-4 text-center text-slate-500">{{ __('No tickets.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $tickets->links() }}</div>
@endsection
