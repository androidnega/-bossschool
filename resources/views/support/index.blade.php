@extends('layouts.app')

@section('title', __('Support tickets'))
@section('header-title', __('Support tickets'))

@section('content')
    @if(session('status'))
        <div class="mb-4 rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
    @endif
    <div class="mb-3 flex justify-end">
        <a href="{{ route('support.create') }}" class="rounded-md bg-primary px-3 py-1.5 text-sm text-white">{{ __('New ticket') }}</a>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-3 py-2">{{ __('Subject') }}</th>
                    <th class="px-3 py-2">{{ __('Category') }}</th>
                    <th class="px-3 py-2">{{ __('Priority') }}</th>
                    <th class="px-3 py-2">{{ __('Status') }}</th>
                    <th class="px-3 py-2">{{ __('Created') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $t)
                    <tr class="border-t">
                        <td class="px-3 py-2"><a class="text-primary hover:underline" href="{{ route('support.show', $t) }}">{{ $t->subject }}</a></td>
                        <td class="px-3 py-2">{{ $t->category }}</td>
                        <td class="px-3 py-2">{{ $t->priority }}</td>
                        <td class="px-3 py-2">{{ $t->status }}</td>
                        <td class="px-3 py-2">{{ $t->created_at?->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-3 py-4 text-center text-slate-500">{{ __('No tickets yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $tickets->links() }}</div>
@endsection
