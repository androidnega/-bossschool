@extends('layouts.app')

@section('title', __('Staff'))

@section('header-title', __('Tenant staff'))

@section('content')
    @include('platform.tenants._control-nav', ['tenant' => $tenant])

    <h1 class="text-xl font-semibold text-slate-900">{{ __('Staff') }} — {{ $tenant->name }}</h1>

    <div class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('Name') }}</th>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('Role') }}</th>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('Subject') }}</th>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('Phone') }}</th>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('Teacher user') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($linked as [$staff, $teacherUser])
                    <tr>
                        <td class="px-4 py-3">{{ $staff->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $staff->role }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $staff->subject ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $staff->phone }}</td>
                        <td class="px-4 py-3 text-slate-600">
                            @if ($teacherUser)
                                {{ $teacherUser->email }}
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
