@extends('layouts.app')

@section('title', __('Users') . ' · ' . $tenant->name)

@section('header-title', __('Tenant users'))

@section('content')
    @include('platform.tenants._control-nav', ['tenant' => $tenant])

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-xl font-semibold text-slate-900">{{ __('Users') }} — {{ $tenant->name }}</h1>
        <a href="{{ route('platform.tenants.users.create', $tenant) }}" class="inline-flex items-center gap-2 rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800">
            <i class="fa-solid fa-plus" aria-hidden="true"></i>{{ __('Add user') }}
        </a>
    </div>

    @if (session('status'))
        <div class="mt-4 rounded-xl border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-900">{{ session('status') }}</div>
    @endif

    <div class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('Name') }}</th>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('Email') }}</th>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('Role') }}</th>
                    <th class="px-4 py-3 font-medium text-slate-700"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($users as $u)
                    <tr>
                        <td class="px-4 py-3">{{ $u->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $u->email }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $u->role }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('platform.tenants.users.edit', [$tenant, $u]) }}" class="text-teal-800 hover:underline">{{ __('Edit') }}</a>
                            <form class="inline ms-3" method="POST" action="{{ route('platform.tenants.users.destroy', [$tenant, $u]) }}" onsubmit="return confirm(@json(__('Delete this user?')));">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-700 hover:underline">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
@endsection
