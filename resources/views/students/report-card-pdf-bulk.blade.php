<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('Report cards') }} — {{ $class?->name ?? '' }} · {{ $term?->name ?? '' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; margin: 0; padding: 0; }
        .student-card { page-break-after: always; }
        .student-card:last-child { page-break-after: auto; }
    </style>
</head>
<body>
@foreach ($cards as $card)
    <div class="student-card">
        @include('students.report-card-pdf', $card)
    </div>
@endforeach
</body>
</html>
