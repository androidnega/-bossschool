@extends('layouts.app')

@section('title', __('Add user'))

@section('header-title', __('Add tenant user'))

@section('content')
    @include('platform.tenants._control-nav', ['tenant' => $tenant])

    <h1 class="text-xl font-semibold text-slate-900">{{ __('Create user') }}</h1>

    <form method="POST" action="{{ route('platform.tenants.users.store', $tenant) }}" class="mt-6 max-w-lg space-y-4 rounded-xl border border-slate-200 bg-white p-6">
        @csrf
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700" for="name">{{ __('Name') }}</label>
            <input id="name" name="name" value="{{ old('name') }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700" for="email">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            @error('email')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700" for="role">{{ __('Role') }}</label>
            <select id="role" name="role" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @foreach ($roles as $r)
                    <option value="{{ $r }}" @selected(old('role') === $r)>{{ $r }}</option>
                @endforeach
            </select>
            @error('role')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700" for="student_id">{{ __('Student (portal only)') }}</label>
            <select id="student_id" name="student_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">{{ __('— Select —') }}</option>
                @foreach ($students as $s)
                    <option value="{{ $s->id }}" @selected((string) old('student_id') === (string) $s->id)>{{ $s->name }} (#{{ $s->id }})</option>
                @endforeach
            </select>
            @error('student_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700" for="password">{{ __('Password') }}</label>
            <input id="password" name="password" type="password" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            @error('password')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700" for="password_confirmation">{{ __('Confirm password') }}</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div class="flex gap-2 pt-2">
            <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800">{{ __('Create') }}</button>
            <a href="{{ route('platform.tenants.users.index', $tenant) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Cancel') }}</a>
        </div>
    </form>
@endsection
