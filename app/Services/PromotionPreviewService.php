<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\PromotionHistory;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use Illuminate\Support\Collection;

/**
 * Builds an academic-performance preview for the promotion screen.
 *
 * Given a (tenant, class, academic year, term), for every active/inactive
 * student in that class we compute:
 *
 *   - total score    (sum of result.total for that period)
 *   - average score  (total / count of results)
 *   - class position with tie-handling (1, 1, 3)
 *   - attendance:    days marked present (including late), days marked absent
 *   - suggested action:
 *        graduate  — the class is a final-year class (the head decided this
 *                    by leaving the from-class as the highest in the school)
 *        repeat    — average < pass mark (50 by default, configurable)
 *        promote   — otherwise
 *
 * The service never mutates the database; the controller's store() action
 * remains the only path that actually records promotions.
 */
class PromotionPreviewService
{
    /**
     * Pass mark threshold (out of {@see Result::letterGradeFromTotal()}'s 0-300 total).
     * Anything strictly below this average is "repeat".
     */
    public const DEFAULT_PASS_MARK = 50.0;

    /**
     * @return Collection<int, array{
     *   student: Student,
     *   total: float,
     *   average: float,
     *   position: ?int,
     *   class_size: int,
     *   present: int,
     *   absent: int,
     *   suggestion: 'promote'|'repeat'|'graduate',
     *   reason: string,
     * }>
     */
    public function for(
        SchoolClass $class,
        ?AcademicYear $year,
        ?Term $term,
        bool $isFinalYearClass = false,
        float $passMark = self::DEFAULT_PASS_MARK,
    ): Collection {
        $students = Student::query()
            ->where('class_id', $class->id)
            ->whereIn('status', ['active', 'inactive'])
            ->orderBy('name')
            ->get();

        if ($students->isEmpty() || $year === null || $term === null) {
            return $students->map(fn (Student $s): array => $this->emptyRow($s, $isFinalYearClass));
        }

        $totalsByStudent = $this->totalsByStudent($class, $year, $term);
        $attendanceByStudent = $this->attendanceByStudent($class, $year, $term);
        $resultCountByStudent = $this->resultCountByStudent($class, $year, $term);

        // Rank: order desc by (total, average). Equal totals AND equal averages
        // share the same rank; the next distinct pair takes (sharedCount + n)
        // -> "1, 1, 3" Olympic style.
        $ranking = $students
            ->map(function (Student $s) use ($totalsByStudent, $resultCountByStudent) {
                $total = (float) ($totalsByStudent[$s->id] ?? 0);
                $count = (int) ($resultCountByStudent[$s->id] ?? 0);
                $avg = $count > 0 ? round($total / $count, 2) : 0.0;

                return ['id' => $s->id, 'total' => $total, 'average' => $avg];
            })
            ->sortByDesc(fn ($r) => [$r['total'], $r['average']])
            ->values();

        $positions = [];
        $rank = 0;
        $lastKey = null;
        foreach ($ranking as $i => $row) {
            $key = $row['total'].':'.$row['average'];
            if ($key !== $lastKey) {
                $rank = $i + 1;
                $lastKey = $key;
            }
            $positions[$row['id']] = $rank;
        }

        $classSize = $students->count();

        return $students->map(function (Student $s) use (
            $totalsByStudent,
            $attendanceByStudent,
            $resultCountByStudent,
            $positions,
            $classSize,
            $isFinalYearClass,
            $passMark
        ): array {
            $total = (float) ($totalsByStudent[$s->id] ?? 0);
            $count = (int) ($resultCountByStudent[$s->id] ?? 0);
            $avg = $count > 0 ? round($total / $count, 2) : 0.0;
            $present = (int) ($attendanceByStudent[$s->id]['present'] ?? 0);
            $absent = (int) ($attendanceByStudent[$s->id]['absent'] ?? 0);

            [$suggestion, $reason] = $this->suggest($total, $avg, $count, $isFinalYearClass, $passMark);

            return [
                'student' => $s,
                'total' => $total,
                'average' => $avg,
                'position' => $positions[$s->id] ?? null,
                'class_size' => $classSize,
                'present' => $present,
                'absent' => $absent,
                'suggestion' => $suggestion,
                'reason' => $reason,
            ];
        });
    }

    /**
     * @return array{0: 'promote'|'repeat'|'graduate', 1: string}
     */
    private function suggest(float $total, float $average, int $count, bool $isFinalYearClass, float $passMark): array
    {
        if ($isFinalYearClass) {
            return [PromotionHistory::STATUS_GRADUATED, __('Final-year class — graduation is the expected next step.')];
        }

        if ($count === 0) {
            return [PromotionHistory::STATUS_REPEATED, __('No results recorded — cannot confirm a pass; default to repeat.')];
        }

        if ($average < $passMark) {
            return [
                PromotionHistory::STATUS_REPEATED,
                __(':avg average is below the :pass pass mark.', ['avg' => $average, 'pass' => $passMark]),
            ];
        }

        return [PromotionHistory::STATUS_PROMOTED, __(':avg average meets the :pass pass mark.', ['avg' => $average, 'pass' => $passMark])];
    }

    /**
     * @return array<int, array{student: Student, total: float, average: float, position: null, class_size: int, present: int, absent: int, suggestion: string, reason: string}>
     */
    private function emptyRow(Student $student, bool $isFinalYearClass): array
    {
        return [
            'student' => $student,
            'total' => 0.0,
            'average' => 0.0,
            'position' => null,
            'class_size' => 0,
            'present' => 0,
            'absent' => 0,
            'suggestion' => $isFinalYearClass ? PromotionHistory::STATUS_GRADUATED : PromotionHistory::STATUS_REPEATED,
            'reason' => __('No results recorded for the chosen period.'),
        ];
    }

    /**
     * @return array<int, float>
     */
    private function totalsByStudent(SchoolClass $class, AcademicYear $year, Term $term): array
    {
        return Result::query()
            ->join('students', 'students.id', '=', 'results.student_id')
            ->where('students.class_id', $class->id)
            ->where('results.tenant_id', $class->tenant_id)
            ->where('results.academic_year_id', $year->id)
            ->where('results.term_id', $term->id)
            ->selectRaw('results.student_id as sid, SUM(results.total) as ttl')
            ->groupBy('results.student_id')
            ->pluck('ttl', 'sid')
            ->map(fn ($v) => (float) $v)
            ->all();
    }

    /**
     * @return array<int, int>
     */
    private function resultCountByStudent(SchoolClass $class, AcademicYear $year, Term $term): array
    {
        return Result::query()
            ->join('students', 'students.id', '=', 'results.student_id')
            ->where('students.class_id', $class->id)
            ->where('results.tenant_id', $class->tenant_id)
            ->where('results.academic_year_id', $year->id)
            ->where('results.term_id', $term->id)
            ->selectRaw('results.student_id as sid, COUNT(*) as c')
            ->groupBy('results.student_id')
            ->pluck('c', 'sid')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    /**
     * @return array<int, array{present: int, absent: int}>
     */
    private function attendanceByStudent(SchoolClass $class, AcademicYear $year, Term $term): array
    {
        $rows = Attendance::query()
            ->join('students', 'students.id', '=', 'attendance.student_id')
            ->where('students.class_id', $class->id)
            ->where('attendance.tenant_id', $class->tenant_id)
            ->where('attendance.academic_year_id', $year->id)
            ->where('attendance.term_id', $term->id)
            ->selectRaw('attendance.student_id as sid, attendance.status as st, COUNT(*) as c')
            ->groupBy('attendance.student_id', 'attendance.status')
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $sid = (int) $r->sid;
            $out[$sid] ??= ['present' => 0, 'absent' => 0];

            if (in_array($r->st, [Attendance::STATUS_PRESENT, Attendance::STATUS_LATE], true)) {
                $out[$sid]['present'] += (int) $r->c;
            } elseif ($r->st === Attendance::STATUS_ABSENT) {
                $out[$sid]['absent'] += (int) $r->c;
            }
        }

        return $out;
    }
}
