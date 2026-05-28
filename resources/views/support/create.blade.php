@extends('layouts.app')

@section('title', __('New support ticket'))
@section('header-title', __('New support ticket'))

@section('content')
    @if($errors->any())
        <div class="mb-4 rounded-md border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('support.store') }}" enctype="multipart/form-data" class="space-y-3 rounded-xl border border-slate-200 bg-white p-4 text-sm">
        @csrf
        <div>
            <label class="text-xs text-slate-500">{{ __('Subject') }}</label>
            <input name="subject" required maxlength="255" class="mt-1 w-full rounded-md border border-slate-300 px-2 py-1.5" value="{{ old('subject') }}" />
        </div>
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="text-xs text-slate-500">{{ __('Category') }}</label>
                <select name="category" required class="mt-1 w-full rounded-md border border-slate-300 px-2 py-1.5">
                    @foreach($categories as $c) <option value="{{ $c }}">{{ $c }}</option> @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-slate-500">{{ __('Priority') }}</label>
                <select name="priority" class="mt-1 w-full rounded-md border border-slate-300 px-2 py-1.5">
                    @foreach($priorities as $p) <option value="{{ $p }}" @if($p==='medium') selected @endif>{{ $p }}</option> @endforeach
                </select>
            </div>
        </div>
        <div>
            <label class="text-xs text-slate-500">{{ __('Describe the issue') }}</label>
            <textarea name="body" rows="6" required maxlength="10000" class="mt-1 w-full rounded-md border border-slate-300 px-2 py-1.5">{{ old('body') }}</textarea>
        </div>
        <div>
            <label class="text-xs text-slate-500">{{ __('Attachment (optional, max 5 MB)') }}</label>
            <input type="file" name="attachment" class="mt-1 block w-full text-xs" />
        </div>
        <div class="flex justify-end">
            <button type="submit" class="rounded-md bg-primary px-3 py-1.5 text-white">{{ __('Submit ticket') }}</button>
        </div>
    </form>
@endsection
