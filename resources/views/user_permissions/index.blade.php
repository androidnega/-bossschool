@extends('layouts.app')

@section('title', __('User permissions'))
@section('header-title', __('User permissions'))

@section('content')
    @if(session('status'))
        <div class="mb-4 rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
    @endif
    <p class="mb-4 text-sm text-slate-600">{{ __('Assign focused roles (Headteacher, Exam Officer, Librarian, etc.) or grant individual module permissions to a user.') }}</p>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('User') }}</th>
                    <th class="px-4 py-3">{{ __('Role') }}</th>
                    <th class="px-4 py-3">{{ __('Email') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Permissions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($users as $u)
                    <tr>
                        <td class="px-4 py-3">{{ $u->name }}</td>
                        <td class="px-4 py-3">{{ $u->role }}</td>
                        <td class="px-4 py-3 text-xs">{{ $u->email }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('user-permissions.edit', $u) }}" class="text-blue-700">{{ __('Edit') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">{{ __('No users yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $users->links() }}</div>
@endsection
