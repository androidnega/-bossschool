@extends('layouts.app')

@section('title', __('Bulk import & export'))

@section('header-title', 'Settings')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-primary">{{ __('Bulk import & export') }}</h1>
        <p class="mt-1 text-sm text-gray-600">{{ __('CSV-based. Download a template, fill it, then upload. Failed rows are reported.') }}</p>
    </div>

    @if(session('status'))
        <div class="mb-4 rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
    @endif

    @if($result)
        <div class="mb-6 rounded-lg border border-gray-200 bg-page p-4 text-sm">
            <h3 class="font-semibold text-gray-700">{{ __('Last import: :k', ['k' => $result['kind']]) }}</h3>
            <p class="text-gray-600">{{ __(':i of :t rows imported.', ['i' => $result['imported'], 't' => $result['total']]) }}</p>
            @if(! empty($result['errors']))
                <details class="mt-3" open>
                    <summary class="cursor-pointer text-red-700">{{ __(':n error(s)', ['n' => count($result['errors'])]) }}</summary>
                    <ul class="mt-2 space-y-1 text-xs text-red-700">
                        @foreach ($result['errors'] as $e)
                            <li><span class="font-mono">{{ __('Row :r', ['r' => $e['row']]) }}</span>: {{ $e['error'] }}</li>
                        @endforeach
                    </ul>
                </details>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div>
            <h2 class="mb-3 text-sm font-semibold text-gray-700">{{ __('Imports') }}</h2>
            <div class="space-y-3">
                @foreach ($importKinds as $kind)
                    @php $roles = $permissions['import'][$kind]; @endphp
                    @if(in_array(auth()->user()->role, $roles, true))
                        <form method="POST" action="{{ route('imports.import', $kind) }}" enctype="multipart/form-data" class="rounded-md border border-gray-200 bg-page p-3">
                            @csrf
                            <div class="flex items-center justify-between">
                                <span class="font-medium capitalize text-gray-800">{{ str_replace('_', ' ', $kind) }}</span>
                                <a href="{{ route('imports.template', $kind) }}" class="text-xs text-secondary hover:underline">{{ __('Download template') }}</a>
                            </div>
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <input type="file" name="file" accept=".csv,text/csv" required class="text-sm">
                                <button type="submit" class="rounded-md bg-primary px-3 py-1.5 text-sm text-white hover:bg-primary/95">{{ __('Import') }}</button>
                            </div>
                        </form>
                    @endif
                @endforeach
            </div>
        </div>

        <div>
            <h2 class="mb-3 text-sm font-semibold text-gray-700">{{ __('Exports') }}</h2>
            <ul class="divide-y divide-gray-200 rounded-md border border-gray-200 bg-page">
                @foreach ($exportKinds as $kind)
                    @php $roles = $permissions['export'][$kind]; @endphp
                    @if(in_array(auth()->user()->role, $roles, true))
                        <li class="flex items-center justify-between px-3 py-2">
                            <span class="capitalize">{{ str_replace('_', ' ', $kind) }}</span>
                            <a href="{{ route('exports.kind', $kind) }}" class="rounded-md border border-gray-300 bg-page px-2 py-1 text-xs hover:bg-page-soft">{{ __('Download CSV') }}</a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    </div>
@endsection
