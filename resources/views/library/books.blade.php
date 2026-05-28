@extends('layouts.app')

@section('title', __('Library books'))
@section('header-title', __('Library — Books'))

@section('content')
    @if(session('status'))
        <div class="mb-4 rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
    @endif

    @can('create', \App\Models\LibraryBook::class)
        <form method="POST" action="{{ route('library.books.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-xl border border-slate-200 bg-white p-4 sm:grid-cols-3">
            @csrf
            <input type="text" name="title" required placeholder="{{ __('Title') }}" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm" />
            <input type="text" name="author" placeholder="{{ __('Author') }}" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm" />
            <input type="text" name="isbn" placeholder="{{ __('ISBN') }}" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm" />
            <input type="text" name="category" placeholder="{{ __('Category') }}" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm" />
            <input type="text" name="shelf_location" placeholder="{{ __('Shelf') }}" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm" />
            <input type="number" min="1" max="10000" name="copies_total" value="1" required class="rounded-md border border-slate-300 px-2 py-1.5 text-sm" />
            <button type="submit" class="sm:col-span-3 rounded-md bg-primary px-3 py-2 text-sm text-white">{{ __('Add book') }}</button>
        </form>
    @endcan

    <form method="GET" class="mb-3 flex gap-2 text-sm">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('Search title / author / ISBN') }}" class="w-72 rounded-md border border-slate-300 px-2 py-1.5" />
        <button type="submit" class="rounded-md bg-slate-700 px-3 py-1.5 text-white">{{ __('Search') }}</button>
    </form>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Title') }}</th>
                    <th class="px-4 py-3">{{ __('Author') }}</th>
                    <th class="px-4 py-3">{{ __('ISBN') }}</th>
                    <th class="px-4 py-3">{{ __('Category') }}</th>
                    <th class="px-4 py-3">{{ __('Shelf') }}</th>
                    <th class="px-4 py-3">{{ __('Available') }} / {{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($books as $b)
                    <tr>
                        <td class="px-4 py-3">{{ $b->title }}</td>
                        <td class="px-4 py-3">{{ $b->author }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $b->isbn }}</td>
                        <td class="px-4 py-3">{{ $b->category }}</td>
                        <td class="px-4 py-3">{{ $b->shelf_location }}</td>
                        <td class="px-4 py-3">{{ $b->copies_available }} / {{ $b->copies_total }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">{{ __('No books yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $books->links() }}</div>
@endsection
