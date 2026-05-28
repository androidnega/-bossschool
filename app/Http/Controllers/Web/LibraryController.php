<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Library\StoreLibraryBookRequest;
use App\Http\Requests\Library\StoreLibraryLoanRequest;
use App\Models\LibraryBook;
use App\Models\LibraryLoan;
use App\Models\Staff;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LibraryController extends Controller
{
    public function books(Request $request): View
    {
        $this->authorize('viewAny', LibraryBook::class);

        $books = LibraryBook::query()
            ->when($request->filled('q'), fn ($q) => $q->where(function ($w) use ($request) {
                $term = '%'.$request->string('q').'%';
                $w->where('title', 'like', $term)->orWhere('author', 'like', $term)->orWhere('isbn', 'like', $term);
            }))
            ->orderBy('title')
            ->paginate(25)
            ->withQueryString();

        return view('library.books', ['books' => $books]);
    }

    public function storeBook(StoreLibraryBookRequest $request): RedirectResponse
    {
        $data = $request->validated();
        LibraryBook::query()->create([
            'tenant_id' => (int) $request->user()->tenant_id,
            'title' => $data['title'],
            'author' => $data['author'] ?? null,
            'isbn' => $data['isbn'] ?? null,
            'category' => $data['category'] ?? null,
            'copies_total' => $data['copies_total'],
            'copies_available' => $data['copies_total'],
            'shelf_location' => $data['shelf_location'] ?? null,
            'status' => LibraryBook::STATUS_ACTIVE,
        ]);

        return redirect()->route('library.books')->with('status', __('Book added.'));
    }

    public function loans(Request $request): View
    {
        $this->authorize('viewAny', LibraryLoan::class);

        $user = $request->user();
        $query = LibraryLoan::query()->with(['book', 'student', 'staff'])->orderByDesc('id');

        if ($user->role === UserRole::Student->value) {
            $query->where('student_id', $user->student_id);
        } elseif ($user->role === UserRole::Parent->value) {
            $studentIds = $user->children()->pluck('students.id');
            $query->whereIn('student_id', $studentIds);
        }

        return view('library.loans', [
            'loans' => $query->paginate(25)->withQueryString(),
            'students' => Student::query()->orderBy('name')->get(),
            'staff' => Staff::query()->orderBy('name')->get(),
            'books' => LibraryBook::query()->where('status', LibraryBook::STATUS_ACTIVE)->orderBy('title')->get(),
        ]);
    }

    public function storeLoan(StoreLibraryLoanRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request): void {
            $book = LibraryBook::query()->whereKey($data['library_book_id'])->lockForUpdate()->firstOrFail();

            if ((int) $book->copies_available <= 0) {
                abort(422, 'No copies available for this book.');
            }

            LibraryLoan::query()->create([
                'tenant_id' => (int) $request->user()->tenant_id,
                'library_book_id' => $book->id,
                'student_id' => $data['student_id'] ?? null,
                'staff_id' => $data['staff_id'] ?? null,
                'borrowed_at' => $data['borrowed_at'],
                'due_at' => $data['due_at'] ?? null,
                'status' => LibraryLoan::STATUS_BORROWED,
                'issued_by_user_id' => $request->user()->id,
                'remarks' => $data['remarks'] ?? null,
            ]);

            $book->decrement('copies_available');
        });

        return redirect()->route('library.loans')->with('status', __('Loan recorded.'));
    }

    public function returnLoan(Request $request, LibraryLoan $loan): RedirectResponse
    {
        $this->authorize('update', $loan);

        DB::transaction(function () use ($loan, $request): void {
            if ($loan->status !== LibraryLoan::STATUS_BORROWED) {
                abort(422, 'Only borrowed loans can be returned.');
            }
            $loan->status = LibraryLoan::STATUS_RETURNED;
            $loan->returned_at = now()->toDateString();
            $loan->received_by_user_id = $request->user()->id;
            $loan->save();

            $loan->book()->increment('copies_available');
        });

        return back()->with('status', __('Book returned.'));
    }
}
