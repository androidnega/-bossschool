<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Result score limits
    |--------------------------------------------------------------------------
    |
    | TEMPORARY DEFAULTS - phase 1 only.
    |
    | Until a configurable per-class / per-subject grading scale is implemented
    | (audit M9 / D1), we enforce a safe cap on each result component and on
    | the calculated total. A Ghanaian basic-school exam component is almost
    | always scored out of 100 with three components summing to 100 (CT 20 +
    | Mid 20 + Exam 60) or similar. Capping each at 100 makes it impossible
    | to produce 240+ totals that the audit flagged.
    |
    | Schools that need custom maxima per component can override this via the
    | env (until Phase 2 introduces per-class scales).
    |
    */

    'score' => [
        'max_per_component' => (int) env('SCHOOL_SCORE_MAX_PER_COMPONENT', 100),
        'max_total' => (int) env('SCHOOL_SCORE_MAX_TOTAL', 300),
    ],
];
