@extends('layouts.app')

@section('title', __('Edit permissions'))
@section('header-title', __('Edit permissions — ').$user->name)

@section('content')
    @if($errors->any())
        <div class="mb-4 rounded-md border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('user-permissions.update', $user) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <label class="text-sm font-semibold">{{ __('Role') }}</label>
            <select name="role" class="mt-1 w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                @foreach($assignableRoles as $r)
                    <option value="{{ $r }}" @selected($user->role === $r)>{{ $r }}</option>
                @endforeach
            </select>
            <p class="mt-2 text-xs text-slate-500">{{ __('SuperAdmin role cannot be assigned from a school account.') }}</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <h2 class="text-sm font-semibold">{{ __('Explicit permission grants') }}</h2>
            <p class="text-xs text-slate-500">{{ __('Grants here are additive to the role’s defaults. Unchecking does not strip a permission already granted by the role.') }}</p>

            @foreach($catalogue as $module => $perms)
                <fieldset class="mt-4 border-t border-slate-100 pt-3">
                    <legend class="text-xs uppercase text-slate-500">{{ str_replace('_', ' ', $module) }}</legend>
                    <div class="mt-2 grid grid-cols-2 gap-2 text-sm">
                        @foreach($perms as $p)
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="permissions[]" value="{{ $p->key }}" @checked(in_array($p->key, $userKeys, true)) />
                                <span class="font-mono text-xs">{{ $p->key }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endforeach
        </div>

        <button type="submit" class="rounded-md bg-primary px-3 py-2 text-sm text-white">{{ __('Save') }}</button>
    </form>
@endsection
