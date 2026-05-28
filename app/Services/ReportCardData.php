<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\ReportCardMeta;
use App\Models\Result;
use App\Models\Student;
use App\Models\Term;
use Illuminate\Support\Collection;

/**
 * Loads everything a Ghanaian report card needs for a student in a given
 * (academic year, term): school header, results, attendance, position,
 * remarks, signatures, vacation/reopening dates, next-term fee.
 */
class ReportCardData
{
    public function __construct(private readonly AcademicContext $academic) {}

    /**
     * @return array{
     *   student: Student,
     *   year: ?AcademicYear,
     *   term: ?Term,
     *   results: Collection,
     *   meta: ?ReportCardMeta,
     *   attendance: array{opened: int, present: int, absent: int, late: int, excused: int},
     *   position: ?array{rank: int, size: int},
     *   total: float,
     *   average: float,
     * }
     */
    public function for(Student $student, ?AcademicYear $year = null, ?Term $term = null): array
    {
        $student->loadMissing('schoolClass');

        $year ??= $this->academic->currentYear();
        $term ??= $this->academic->currentTerm();

        $results = Result::query()
            ->where('tenant_id', $student->tenant_id)
            ->where('student_id', $student->id)
            ->when($year, fn ($q) => $q->where('academic_year_id', $year->id))
            ->when($term, fn ($q) => $q->where('term_id', $term->id))
            ->with('subject')
            ->orderBy('subject_id')
            ->get();

        $meta = ($year && $term)
            ? ReportCardMeta::query()
                ->where('tenant_id', $student->tenant_id)
                ->where('student_id', $student->id)
                ->where('academic_year_id', $year->id)
                ->where('term_id', $term->id)
                ->first()
            : null;

        $attendance = $this->attendanceSummary($student, $year, $term, $meta);
        $position = $this->position($student, $year, $term);

        $total = (float) $results->sum(fn ($r) => (float) $r->total);
        $average = $results->count() > 0 ? round($total / $results->count(), 2) : 0.0;

        return compact('student', 'year', 'term', 'results', 'meta', 'attendance', 'position', 'total', 'average');
    }

    /**
     * @return array{opened: int, present: int, absent: int, late: int, excused: int}
     */
    private function attendanceSummary(Student $student, ?AcademicYear $year, ?Term $term, ?ReportCardMeta $meta): array
    {
        if ($meta && ($meta->days_school_opened !== null || $meta->days_present !== null || $meta->days_absent !== null)) {
            return [
                'opened' => (int) ($meta->days_school_opened ?? 0),
                'present' => (int) ($meta->days_present ?? 0),
                'absent' => (int) ($meta->days_absent ?? 0),
                'late' => 0,
                'excused' => 0,
            ];
        }

        $rows = Attendance::query()
            ->where('tenant_id', $student->tenant_id)
            ->where('student_id', $student->id)
            ->when($term, fn ($q) => $q->where('term_id', $term->id))
            ->get(['status']);

        $count = fn (string $status) => $rows->where('status', $status)->count();

        return [
            'opened' => $rows->count(),
            'present' => $count('present') + $count('late'),
            'absent' => $count('absent'),
            'late' => $count('late'),
            'excused' => $count('excused'),
        ];
    }

    /**
     * Olympic-style ranking ("1, 1, 3") within the student's class for the
     * given (academic year, term). Primary sort key is total score; ties on
     * total are broken by average. Ties on BOTH total and average share a
     * position; the next distinct (total, average) pair takes
     * (sharedCount + n) so a 2-way tie at the top results in
     * (1st, 1st, 3rd) not (1st, 2nd, 3rd).
     *
     * `size` is the number of ranked students (including the current one).
     *
     * @return ?array{rank: int, size: int}
     */
    private function position(Student $student, ?AcademicYear $year, ?Term $term): ?array
    {
        if ($year === null || $term === null || $student->class_id === null) {
            return null;
        }

        $totals = Result::query()
            ->where('results.tenant_id', $student->tenant_id)
            ->where('results.academic_year_id', $year->id)
            ->where('results.term_id', $term->id)
            ->join('students', 'students.id', '=', 'results.student_id')
            ->where('students.class_id', $student->class_id)
            ->selectRaw('students.id as sid, SUM(results.total) as ttl, COUNT(*) as cnt')
            ->groupBy('students.id')
            ->get()
            ->map(fn ($r) => [
                'sid' => (int) $r->sid,
                'total' => (float) $r->ttl,
                'average' => ((int) $r->cnt) > 0 ? round(((float) $r->ttl) / (int) $r->cnt, 2) : 0.0,
            ])
            ->sortByDesc(fn ($r) => [$r['total'], $r['average']])
            ->values();

        if ($totals->isEmpty()) {
            return null;
        }

        $rank = null;
        $currentRank = 0;
        $lastKey = null;
        foreach ($totals as $i => $row) {
            $key = $row['total'].':'.$row['average'];
            if ($key !== $lastKey) {
                $currentRank = $i + 1;
                $lastKey = $key;
            }
            if ($row['sid'] === (int) $student->id) {
                $rank = $currentRank;
                break;
            }
        }

        if ($rank === null) {
            return null;
        }

        return ['rank' => $rank, 'size' => $totals->count()];
    }
}
