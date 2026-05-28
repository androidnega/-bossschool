@extends('layouts.app')

@section('title', __('Tenant backups'))
@section('header-title', __('Tenant backups'))

@section('content')
    @if(session('status'))
        <div class="mb-4 rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-md border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('platform.backups.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-xl border border-slate-200 bg-white p-4 sm:grid-cols-3">
        @csrf
        <select name="tenant_id" required class="rounded-md border border-slate-300 px-2 py-1.5 text-sm">
            <option value="">{{ __('Tenant') }}</option>
            @foreach($tenants as $t)
                <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->subdomain }})</option>
            @endforeach
        </select>
        <select name="backup_type" required class="rounded-md border border-slate-300 px-2 py-1.5 text-sm">
            @foreach(\App\Models\TenantBackup::TYPES as $type)
                <option value="{{ $type }}">{{ str_replace('_', ' ', $type) }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-md bg-primary px-3 py-2 text-sm text-white">{{ __('Create backup') }}</button>
    </form>

    <form method="GET" class="mb-3 flex gap-2 text-sm">
        <select name="tenant_id" class="rounded-md border border-slate-300 px-2 py-1.5">
            <option value="">{{ __('All tenants') }}</option>
            @foreach($tenants as $t)
                <option value="{{ $t->id }}" @selected((string)($filters['tenant_id'] ?? '') === (string)$t->id)>{{ $t->name }}</option>
            @endforeach
        </select>
        <select name="type" class="rounded-md border border-slate-300 px-2 py-1.5">
            <option value="">{{ __('Any type') }}</option>
            @foreach(\App\Models\TenantBackup::TYPES as $type)
                <option value="{{ $type }}" @selected(($filters['type'] ?? '') === $type)>{{ $type }}</option>
            @endforeach
        </select>
        <button class="rounded-md bg-slate-700 px-3 py-1.5 text-white">{{ __('Filter') }}</button>
    </form>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('ID') }}</th>
                    <th class="px-4 py-3">{{ __('Tenant') }}</th>
                    <th class="px-4 py-3">{{ __('Type') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3">{{ __('Size') }}</th>
                    <th class="px-4 py-3">{{ __('When') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($backups as $b)
                    <tr>
                        <td class="px-4 py-3">#{{ $b->id }}</td>
                        <td class="px-4 py-3">{{ $b->tenant?->name }}</td>
                        <td class="px-4 py-3">{{ $b->backup_type }}</td>
                        <td class="px-4 py-3">{{ $b->status }}</td>
                        <td class="px-4 py-3">{{ number_format($b->size_bytes) }} B</td>
                        <td class="px-4 py-3">{{ $b->created_at?->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('platform.backups.show', $b) }}" class="text-blue-700">{{ __('View') }}</a>
                            @if($b->status === \App\Models\TenantBackup::STATUS_COMPLETED || $b->status === \App\Models\TenantBackup::STATUS_RESTORED)
                                <a href="{{ route('platform.backups.download', $b) }}" class="text-slate-700">{{ __('Download') }}</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-slate-500">{{ __('No backups yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $backups->links() }}</div>
@endsection
