@extends('layouts.app')

@section('title', __('School backups'))
@section('header-title', __('School backups'))

@section('content')
    @if(session('status'))
        <div class="mb-4 rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-md border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">{{ $errors->first() }}</div>
    @endif

    <p class="mb-4 text-sm text-slate-600">{{ __('Create a JSON backup of your school’s data. Backups are stored securely and can be downloaded any time.') }}</p>

    @can('create', \App\Models\TenantBackup::class)
        <form method="POST" action="{{ route('backups.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-xl border border-slate-200 bg-white p-4 sm:grid-cols-3">
            @csrf
            <select name="backup_type" required class="rounded-md border border-slate-300 px-2 py-1.5 text-sm sm:col-span-2">
                @foreach(\App\Models\TenantBackup::TYPES as $type)
                    <option value="{{ $type }}">{{ str_replace('_', ' ', $type) }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-md bg-primary px-3 py-2 text-sm text-white">{{ __('Take backup now') }}</button>
        </form>
    @endcan

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('When') }}</th>
                    <th class="px-4 py-3">{{ __('Type') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3">{{ __('Size') }}</th>
                    <th class="px-4 py-3">{{ __('By') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($backups as $b)
                    <tr>
                        <td class="px-4 py-3">{{ $b->created_at?->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3">{{ $b->backup_type }}</td>
                        <td class="px-4 py-3">{{ $b->status }}</td>
                        <td class="px-4 py-3">{{ number_format($b->size_bytes) }} B</td>
                        <td class="px-4 py-3">{{ $b->creator?->name }}</td>
                        <td class="px-4 py-3 text-right">
                            @if($b->status === \App\Models\TenantBackup::STATUS_COMPLETED)
                                <a href="{{ route('backups.download', $b) }}" class="text-blue-700">{{ __('Download') }}</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">{{ __('No backups yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $backups->links() }}</div>
@endsection
