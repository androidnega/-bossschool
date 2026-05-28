@extends('layouts.app')

@section('title', __('Schools'))

@section('header-title', __('Schools / tenants'))

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">{{ __('Schools') }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ __('Create, review, and control tenant lifecycle.') }}</p>
        </div>
        <a href="{{ route('platform.tenants.create') }}" class="inline-flex items-center justify-center rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800">{{ __('Create school') }}</a>
    </div>

    <div class="mt-8 overflow-hidden rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('Name') }}</th>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('Subdomain') }}</th>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('Status') }}</th>
                    <th class="px-4 py-3 font-medium text-slate-700 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($tenants as $tenant)
                    <tr>
                        <td class="px-4 py-3"><a class="font-medium text-teal-800 hover:underline" href="{{ route('platform.tenants.show', $tenant) }}">{{ $tenant->name }}</a></td>
                        <td class="px-4 py-3 text-slate-600">{{ $tenant->subdomain }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $tenant->status }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('platform.tenants.show', $tenant) }}" class="text-sm font-medium text-slate-700 hover:text-slate-900">{{ __('Open') }}</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $tenants->links() }}</div>
@endsection
