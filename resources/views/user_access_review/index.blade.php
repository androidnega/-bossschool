@extends('layouts.app')

@section('title', __('User access review'))
@section('header-title', __('User access review'))

@section('content')
    @if(session('status'))
        <div class="mb-4 rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
    @endif

    <form method="GET" class="mb-4 flex gap-2 text-sm">
        <select name="role" class="rounded-md border border-slate-300 px-2 py-1.5">
            <option value="">{{ __('Any role') }}</option>
            @foreach($roles as $r)
                <option value="{{ $r }}" @selected(($filters['role'] ?? '') === $r)>{{ $r }}</option>
            @endforeach
        </select>
        <input type="number" name="inactive_days" min="7" max="365" value="{{ $filters['inactive_days'] ?? 60 }}" class="w-32 rounded-md border border-slate-300 px-2 py-1.5" />
        <button class="rounded-md bg-slate-700 px-3 py-1.5 text-white">{{ __('Filter') }}</button>
    </form>

    <h2 class="mb-2 text-sm font-semibold">{{ __('All users') }}</h2>
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Name') }}</th>
                    <th class="px-4 py-3">{{ __('Role') }}</th>
                    <th class="px-4 py-3">{{ __('Last login') }}</th>
                    <th class="px-4 py-3">{{ __('Active') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($users as $u)
                    <tr>
                        <td class="px-4 py-3">{{ $u->name }}<div class="text-xs text-slate-500">{{ $u->email }}</div></td>
                        <td class="px-4 py-3">{{ $u->role }}</td>
                        <td class="px-4 py-3 text-xs">{{ $u->last_login_at?->format('Y-m-d H:i') ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $u->is_active ? __('Yes') : __('No') }}</td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <form method="POST" action="{{ route('user-access-review.force-reset', $u) }}" class="inline">
                                @csrf
                                <button class="text-xs text-blue-700">{{ __('Force reset') }}</button>
                            </form>
                            <form method="POST" action="{{ route('user-access-review.revoke-sessions', $u) }}" class="inline">
                                @csrf
                                <button class="text-xs text-amber-700">{{ __('Revoke sessions') }}</button>
                            </form>
                            <form method="POST" action="{{ route('user-access-review.deactivate', $u) }}" class="inline">
                                @csrf
                                <button class="text-xs text-rose-700">{{ __('Deactivate') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">{{ __('No users.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $users->links() }}</div>

    @if($inactive->isNotEmpty())
        <h2 class="mt-8 mb-2 text-sm font-semibold">{{ __('Inactive users (no login in :days days)', ['days' => $filters['inactive_days'] ?? 60]) }}</h2>
        <div class="overflow-x-auto rounded-xl border border-amber-200 bg-amber-50">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-amber-100 text-xs uppercase text-amber-700">
                    <tr>
                        <th class="px-4 py-3">{{ __('Name') }}</th>
                        <th class="px-4 py-3">{{ __('Role') }}</th>
                        <th class="px-4 py-3">{{ __('Last login') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber-100">
                    @foreach($inactive as $u)
                        <tr>
                            <td class="px-4 py-3">{{ $u->name }}</td>
                            <td class="px-4 py-3">{{ $u->role }}</td>
                            <td class="px-4 py-3 text-xs">{{ $u->last_login_at?->format('Y-m-d') ?? __('Never') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
