@extends('layouts.app')

@section('title', __('Notices'))

@section('header-title', __('Notices'))

@section('content')
    {{-- TODO: Two-way messaging / replies — notices only for now. --}}
    <div class="flex flex-col gap-2 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">{{ __('School notices') }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ __('One-way notices: who sent them, who they target, and when they were sent.') }}</p>
        </div>
    </div>

    @if (session('status'))
        <div class="mt-4 rounded-xl border border-secondary/30 bg-page-soft px-4 py-3 text-sm text-slate-800" role="status">{{ session('status') }}</div>
    @endif

    @if ($canCreateSchool || $canFeeReminder || $canClassNotice)
        <div class="mt-8 rounded-xl border border-slate-200 bg-white p-4 sm:p-6">
            <h2 class="text-lg font-semibold text-slate-900"><i class="fa-solid fa-pen-to-square me-2 text-primary" aria-hidden="true"></i>{{ __('Compose notice') }}</h2>
            <form method="POST" action="{{ route('messages.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label for="msg-title" class="mb-1 block text-sm font-medium text-slate-700">{{ __('Title') }} <span class="text-slate-400">({{ __('optional') }})</span></label>
                    <input id="msg-title" name="title" type="text" value="{{ old('title') }}" maxlength="255" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                </div>
                <div>
                    <label for="msg-audience" class="mb-1 block text-sm font-medium text-slate-700">{{ __('Audience') }}</label>
                    <select id="msg-audience" name="recipient_type" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="" disabled @selected(old('recipient_type') === null)>{{ __('Select…') }}</option>
                        @if ($canCreateSchool)
                            <option value="all_parents" @selected(old('recipient_type') === 'all_parents')>{{ __('All parents') }}</option>
                            <option value="all_students" @selected(old('recipient_type') === 'all_students')>{{ __('All students') }}</option>
                            <option value="teachers" @selected(old('recipient_type') === 'teachers')>{{ __('Teachers') }}</option>
                            <option value="all_users" @selected(old('recipient_type') === 'all_users')>{{ __('All school users') }}</option>
                            <option value="class_parents" @selected(old('recipient_type') === 'class_parents')>{{ __('Class parents') }}</option>
                            <option value="class_students" @selected(old('recipient_type') === 'class_students')>{{ __('Class students') }}</option>
                            <option value="selected_parent" @selected(old('recipient_type') === 'selected_parent')>{{ __('Selected parent') }}</option>
                        @elseif ($canFeeReminder)
                            <option value="all_parents" @selected(old('recipient_type') === 'all_parents')>{{ __('All parents') }}</option>
                            <option value="class_parents" @selected(old('recipient_type') === 'class_parents')>{{ __('Class parents') }}</option>
                        @elseif ($canClassNotice)
                            <option value="class_parents" @selected(old('recipient_type') === 'class_parents')>{{ __('Class parents') }}</option>
                            <option value="class_students" @selected(old('recipient_type') === 'class_students')>{{ __('Class students') }}</option>
                        @endif
                    </select>
                </div>
                <div>
                    <label for="msg-class" class="mb-1 block text-sm font-medium text-slate-700">{{ __('Class') }} <span class="text-slate-400">({{ __('if audience is class-based') }})</span></label>
                    <select id="msg-class" name="school_class_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">{{ __('—') }}</option>
                        @foreach ($classes as $c)
                            <option value="{{ $c->id }}" @selected((string) old('school_class_id') === (string) $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                @if ($canCreateSchool && $parentUsers->isNotEmpty())
                    <div>
                        <label for="msg-parent" class="mb-1 block text-sm font-medium text-slate-700">{{ __('Parent user') }} <span class="text-slate-400">({{ __('for selected parent') }})</span></label>
                        <select id="msg-parent" name="recipient_user_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">{{ __('—') }}</option>
                            @foreach ($parentUsers as $pu)
                                <option value="{{ $pu->id }}" @selected((string) old('recipient_user_id') === (string) $pu->id)>{{ $pu->name }} ({{ $pu->email }})</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div>
                    <label for="msg-body" class="mb-1 block text-sm font-medium text-slate-700">{{ __('Notice body') }}</label>
                    <textarea id="msg-body" name="content" rows="4" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('content') }}</textarea>
                </div>
                <p class="text-xs text-slate-500"><i class="fa-solid fa-circle-info me-1" aria-hidden="true"></i>{{ __('Replies are not available yet — this publishes a one-way notice only.') }}</p>
                @if ($canFeeReminder)
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="send_sms" value="1" class="rounded border-slate-300 text-primary focus:ring-primary" />
                        {{ __('Also send by SMS to parents with phone numbers') }}
                    </label>
                @endif
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary/95">
                    <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>{{ __('Publish notice') }}
                </button>
            </form>
        </div>
    @endif

    <div class="mt-10 overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-2">{{ __('Type') }}</th>
                    <th class="px-4 py-2">{{ __('Title') }}</th>
                    <th class="px-4 py-2">{{ __('Sender') }}</th>
                    <th class="px-4 py-2">{{ __('Audience') }}</th>
                    <th class="px-4 py-2">{{ __('Category') }}</th>
                    <th class="px-4 py-2">{{ __('Sent') }}</th>
                    <th class="px-4 py-2">{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($messages as $m)
                    <tr class="align-top">
                        <td class="px-4 py-3"><span class="inline-flex rounded-full bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-800">{{ __('Notice') }}</span></td>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $m->displayTitle() }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $m->sender?->name ?? __('School office') }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $m->audienceDisplay() }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $m->noticeKindLabel() }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $m->sent_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $m->statusDisplay() }}</td>
                    </tr>
                    <tr class="border-b border-slate-100 bg-slate-50/50">
                        <td colspan="7" class="px-4 py-3 text-slate-800">{{ $m->content }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">{{ __('No notices yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $messages->links() }}</div>
@endsection
