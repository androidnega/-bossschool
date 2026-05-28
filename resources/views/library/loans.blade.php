@extends('layouts.app')

@section('title', __('Library loans'))
@section('header-title', __('Library — Loans'))

@section('content')
    @if(session('status'))
        <div class="mb-4 rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
    @endif

    @can('create', \App\Models\LibraryLoan::class)
        <form method="POST" action="{{ route('library.loans.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-xl border border-slate-200 bg-white p-4 sm:grid-cols-3">
            @csrf
            <select name="library_book_id" required class="rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                <option value="">{{ __('Book') }}</option>
                @foreach($books as $book)
                    <option value="{{ $book->id }}">{{ $book->title }} ({{ $book->copies_available }})</option>
                @endforeach
            </select>
            <select name="student_id" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                <option value="">{{ __('Student (optional)') }}</option>
                @foreach($students as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </select>
            <select name="staff_id" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                <option value="">{{ __('Staff (optional)') }}</option>
                @foreach($staff as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </select>
            <input type="date" name="borrowed_at" required value="{{ now()->toDateString() }}" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm" />
            <input type="date" name="due_at" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm" />
            <input type="text" name="remarks" placeholder="{{ __('Remarks') }}" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm" />
            <button type="submit" class="sm:col-span-3 rounded-md bg-primary px-3 py-2 text-sm text-white">{{ __('Record loan') }}</button>
        </form>
    @endcan

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Book') }}</th>
                    <th class="px-4 py-3">{{ __('Borrower') }}</th>
                    <th class="px-4 py-3">{{ __('Borrowed') }}</th>
                    <th class="px-4 py-3">{{ __('Due') }}</th>
                    <th class="px-4 py-3">{{ __('Returned') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($loans as $loan)
                    <tr>
                        <td class="px-4 py-3">{{ $loan->book?->title }}</td>
                        <td class="px-4 py-3">{{ $loan->student?->name ?? $loan->staff?->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $loan->borrowed_at?->format('Y-m-d') }}</td>
                        <td class="px-4 py-3">{{ $loan->due_at?->format('Y-m-d') ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $loan->returned_at?->format('Y-m-d') ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $loan->status }}</td>
                        <td class="px-4 py-3 text-right">
                            @can('update', $loan)
                                @if($loan->status === \App\Models\LibraryLoan::STATUS_BORROWED)
                                    <form method="POST" action="{{ route('library.loans.return', $loan) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="rounded-md bg-emerald-600 px-2 py-1 text-xs text-white">{{ __('Mark returned') }}</button>
                                    </form>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-slate-500">{{ __('No loans yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $loans->links() }}</div>
@endsection
