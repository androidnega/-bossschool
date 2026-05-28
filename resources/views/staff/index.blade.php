@extends('layouts.app')

@section('title', __('Staff'))

@section('header-title', __('Staff'))

@section('content')
    <div class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">{{ __('Staff directory') }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ __('People records for your school (not user accounts).') }}</p>
        </div>
        @can('staff.manage')
            <a href="{{ route('staff.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary/95">
                <i class="fa-solid fa-plus" aria-hidden="true"></i>{{ __('Add staff') }}
            </a>
        @endcan
    </div>

    @if (session('status'))
        <div class="mt-4 rounded-xl border border-secondary/30 bg-page-soft px-4 py-3 text-sm text-slate-800" role="status">{{ session('status') }}</div>
    @endif

    <div class="mt-8 overflow-hidden rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('Name') }}</th>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('Role') }}</th>
                    <th class="hidden px-4 py-3 font-medium text-slate-700 md:table-cell">{{ __('Subject') }}</th>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('Phone') }}</th>
                    @can('staff.manage')
                        <th class="px-4 py-3 font-medium text-slate-700 text-end">{{ __('Actions') }}</th>
                    @endcan
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($staff as $row)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $row->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $row->role ?? '—' }}</td>
                        <td class="hidden px-4 py-3 text-slate-600 md:table-cell">{{ $row->subject ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $row->phone ?? '—' }}</td>
                        @can('staff.manage')
                            <td class="px-4 py-3 text-end">
                                <a href="{{ route('staff.edit', $row) }}" class="me-2 text-sm font-medium text-primary hover:underline">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('staff.destroy', $row) }}" class="inline" onsubmit="return confirm(@json(__('Remove this staff record?')));">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm font-medium text-rose-700 hover:underline">{{ __('Remove') }}</button>
                                </form>
                            </td>
                        @endcan
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ Gate::check('staff.manage') ? 5 : 4 }}" class="px-4 py-8 text-center text-slate-500">{{ __('No staff yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $staff->links() }}</div>
@endsection
