@extends('layouts.app')

@section('title', __('My assignments'))
@section('header-title', __('My subjects and classes'))

@section('content')
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <section class="rounded-xl border border-slate-200 bg-white p-4">
            <h2 class="mb-3 text-sm font-semibold text-slate-700">{{ __('Subjects') }}</h2>
            <ul class="divide-y divide-slate-100 text-sm">
                @forelse($subjects as $s)
                    <li class="py-1.5 text-slate-700">{{ $s->name }}</li>
                @empty
                    <li class="py-2 text-slate-500">{{ __('No subjects assigned yet.') }}</li>
                @endforelse
            </ul>
        </section>
        <section class="rounded-xl border border-slate-200 bg-white p-4">
            <h2 class="mb-3 text-sm font-semibold text-slate-700">{{ __('Classes') }}</h2>
            <ul class="divide-y divide-slate-100 text-sm">
                @forelse($classes as $c)
                    <li class="py-1.5 text-slate-700">{{ $c->name }} {{ $c->section ? '· '.$c->section : '' }}</li>
                @empty
                    <li class="py-2 text-slate-500">{{ __('No classes assigned yet.') }}</li>
                @endforelse
            </ul>
        </section>
    </div>
@endsection
