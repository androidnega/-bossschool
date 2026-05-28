@extends('layouts.app')

@section('title', __('Backup detail'))
@section('header-title', __('Backup #').$backup->id)

@section('content')
    @if(session('status'))
        <div class="mb-4 rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-md border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">{{ $errors->first() }}</div>
    @endif

    <dl class="grid grid-cols-2 gap-3 rounded-xl border border-slate-200 bg-white p-4 text-sm">
        <dt class="text-slate-500">{{ __('Tenant') }}</dt>
        <dd>{{ $backup->tenant?->name }} ({{ $backup->tenant?->subdomain }})</dd>
        <dt class="text-slate-500">{{ __('Type') }}</dt>
        <dd>{{ $backup->backup_type }}</dd>
        <dt class="text-slate-500">{{ __('Status') }}</dt>
        <dd>{{ $backup->status }}</dd>
        <dt class="text-slate-500">{{ __('Size') }}</dt>
        <dd>{{ number_format($backup->size_bytes) }} B</dd>
        <dt class="text-slate-500">{{ __('Checksum') }}</dt>
        <dd class="font-mono text-xs break-all">{{ $backup->checksum }}</dd>
        @if($checksumValid !== null)
            <dt class="text-slate-500">{{ __('Checksum verified') }}</dt>
            <dd>{{ $checksumValid ? __('Yes') : __('No (file changed since backup)') }}</dd>
        @endif
        <dt class="text-slate-500">{{ __('Created by') }}</dt>
        <dd>{{ $backup->creator?->name }}</dd>
        <dt class="text-slate-500">{{ __('Created at') }}</dt>
        <dd>{{ $backup->created_at?->format('Y-m-d H:i') }}</dd>
        @if($backup->restored_at)
            <dt class="text-slate-500">{{ __('Restored by') }}</dt>
            <dd>{{ $backup->restorer?->name }}</dd>
            <dt class="text-slate-500">{{ __('Restored at') }}</dt>
            <dd>{{ $backup->restored_at->format('Y-m-d H:i') }}</dd>
        @endif
        @if($backup->failure_reason)
            <dt class="text-slate-500">{{ __('Failure reason') }}</dt>
            <dd class="text-rose-700">{{ $backup->failure_reason }}</dd>
        @endif
    </dl>

    @if(($backup->metadata['row_counts'] ?? null) && is_array($backup->metadata['row_counts']))
        <div class="mt-6 rounded-xl border border-slate-200 bg-white p-4">
            <h2 class="mb-2 text-sm font-semibold">{{ __('Row counts') }}</h2>
            <ul class="text-sm">
                @foreach($backup->metadata['row_counts'] as $table => $count)
                    <li class="flex justify-between"><span>{{ $table }}</span><span>{{ number_format($count) }}</span></li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($backup->status === \App\Models\TenantBackup::STATUS_COMPLETED)
        <div class="mt-6 rounded-xl border border-rose-200 bg-rose-50 p-4">
            <h2 class="text-sm font-semibold text-rose-700">{{ __('Restore (Super Admin only)') }}</h2>
            <p class="mt-1 text-xs text-rose-700">{{ __('Restore is additive — existing rows are kept; rows from the backup are inserted by primary key. Run a current backup first if you need to compare.') }}</p>
            <form method="POST" action="{{ route('platform.backups.restore', $backup) }}" class="mt-3 grid gap-3 sm:grid-cols-3">
                @csrf
                <input type="number" name="target_tenant_id" placeholder="{{ __('Override target tenant id (optional)') }}" class="rounded-md border border-rose-300 px-2 py-1.5 text-sm sm:col-span-1" />
                <input type="text" name="confirm" required placeholder="{{ __('Type RESTORE') }}" class="rounded-md border border-rose-300 px-2 py-1.5 text-sm" />
                <input type="password" name="password" required placeholder="{{ __('Your password') }}" class="rounded-md border border-rose-300 px-2 py-1.5 text-sm" />
                <button type="submit" class="rounded-md bg-rose-600 px-3 py-2 text-sm text-white sm:col-span-3">{{ __('Restore backup') }}</button>
            </form>
        </div>
    @endif
@endsection
