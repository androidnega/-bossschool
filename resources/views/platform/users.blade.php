@extends('layouts.app')

@section('title', __('Platform users'))

@section('header-title', __('Platform users'))

@section('content')
    <h1 class="text-2xl font-semibold text-slate-900">{{ __('School users') }}</h1>
    <p class="mt-1 text-sm text-slate-600">{{ __('Users attached to a tenant (not SuperAdmin accounts).') }}</p>

    <div class="mt-8 overflow-hidden rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('Name') }}</th>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('Email') }}</th>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('Role') }}</th>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('School') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($users as $u)
                    <tr>
                        <td class="px-4 py-3">{{ $u->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $u->email }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $u->role }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $u->tenant?->name }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
@endsection
