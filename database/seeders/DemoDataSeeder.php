<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Fee;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\Term;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class DemoDataSeeder extends Seeder
{
    private const FEE_TYPES = ['Tuition', 'Admission', 'Exam Fee', 'PTA', 'Feeding', 'Transport'];

    private const SUBJECT_NAMES = ['English', 'Mathematics', 'Science', 'Social Studies', 'ICT', 'RME'];

    public function run(): void
    {
        $tenant = Tenant::query()->where('subdomain', 'demo')->firstOrFail();
        $tid = (int) $tenant->id;

        $classes = $this->seedClasses($tenant);
        $academicYear = $this->seedAcademicYear($tenant);
        $terms = $this->seedTerms($tenant, $academicYear);
        $this->seedStaff($tenant);
        $students = $this->seedStudents($tenant, $classes);
        $this->seedSubjects($tid, $classes);
        $this->seedFees($tid, $classes, $terms);
        $this->seedPayments($tid, $students);
        $this->seedResults($tid, $students, $academicYear, $terms);
        $this->seedAttendance($tid, $students, $academicYear, $terms);
        $this->seedSubscription($tenant);
    }

    /**
     * @return Collection<int, SchoolClass>
     */
    private function seedClasses(Tenant $tenant): Collection
    {
        $specs = [
            ['name' => 'KG 1', 'section' => null],
            ['name' => 'KG 2', 'section' => null],
            ['name' => 'Basic 1', 'section' => null],
            ['name' => 'Basic 2', 'section' => null],
            ['name' => 'Basic 3', 'section' => null],
            ['name' => 'Basic 4', 'section' => null],
            ['name' => 'Basic 5', 'section' => null],
            ['name' => 'Basic 6', 'section' => null],
            ['name' => 'JHS 1', 'section' => null],
            ['name' => 'JHS 2', 'section' => null],
            ['name' => 'JHS 3', 'section' => null],
        ];

        return collect($specs)->map(fn (array $spec) => SchoolClass::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'name' => $spec['name'],
                'section' => $spec['section'],
            ],
            []
        ));
    }

    private function seedAcademicYear(Tenant $tenant): AcademicYear
    {
        $year = AcademicYear::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'name' => '2025/2026',
            ],
            [
                'starts_on' => '2025-09-01',
                'ends_on' => '2026-07-31',
                'status' => AcademicYear::STATUS_ACTIVE,
            ]
        );

        AcademicYear::query()
            ->where('tenant_id', $tenant->id)
            ->whereKeyNot($year->id)
            ->update(['is_current' => false]);

        $year->forceFill(['is_current' => true])->save();

        return $year;
    }

    /**
     * @return Collection<int, Term>
     */
    private function seedTerms(Tenant $tenant, AcademicYear $year): Collection
    {
        $specs = [
            ['n' => 1, 'name' => 'Term 1', 'starts' => '2025-09-08', 'ends' => '2025-12-19'],
            ['n' => 2, 'name' => 'Term 2', 'starts' => '2026-01-12', 'ends' => '2026-04-10'],
            ['n' => 3, 'name' => 'Term 3', 'starts' => '2026-05-04', 'ends' => '2026-07-31'],
        ];

        $today = Carbon::today();

        $terms = collect($specs)->map(fn (array $spec) => Term::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'academic_year_id' => $year->id,
                'term_order' => $spec['n'],
            ],
            [
                'name' => $spec['name'],
                'starts_on' => $spec['starts'],
                'ends_on' => $spec['ends'],
                'status' => Term::STATUS_ACTIVE,
            ]
        ));

        $current = $terms->first(fn (Term $t) => $t->starts_on && $t->ends_on && $today->between($t->starts_on, $t->ends_on));
        $current ??= $terms->first();

        Term::query()->where('tenant_id', $tenant->id)->update(['is_current' => false]);
        $current?->forceFill(['is_current' => true])->save();

        return $terms;
    }

    private function seedStaff(Tenant $tenant): void
    {
        $rows = [
            ['phone' => '+233200000101', 'name' => 'Mrs. Akosua Boateng', 'role' => 'Head Teacher', 'subject' => 'Mathematics', 'salary' => '5800.00'],
            ['phone' => '+233200000102', 'name' => 'Mr. Kwame Osei', 'role' => 'Assistant Head', 'subject' => 'English', 'salary' => '5400.00'],
            ['phone' => '+233200000103', 'name' => 'Ms. Yaa Frimpong', 'role' => 'Teacher', 'subject' => 'Science', 'salary' => '4200.00'],
            ['phone' => '+233200000104', 'name' => 'Mr. Joseph Ampofo', 'role' => 'Teacher', 'subject' => 'Social Studies', 'salary' => '4100.00'],
            ['phone' => '+233200000105', 'name' => 'Mrs. Adwoa Serwaa', 'role' => 'Teacher', 'subject' => 'ICT', 'salary' => '4300.00'],
            ['phone' => '+233200000106', 'name' => 'Mr. Emmanuel Tetteh', 'role' => 'Teacher', 'subject' => 'RME', 'salary' => '4000.00'],
            ['phone' => '+233200000107', 'name' => 'Ms. Linda Owusu', 'role' => 'Bursar', 'subject' => null, 'salary' => '4800.00'],
            ['phone' => '+233200000108', 'name' => 'Mr. Kofi Badu', 'role' => 'Sports Coordinator', 'subject' => 'Physical Education', 'salary' => '3900.00'],
            ['phone' => '+233200000109', 'name' => 'Mrs. Rose Appiah', 'role' => 'Registrar', 'subject' => null, 'salary' => '4500.00'],
            ['phone' => '+233200000110', 'name' => 'Mr. Samuel Darko', 'role' => 'Librarian', 'subject' => null, 'salary' => '3600.00'],
        ];

        foreach ($rows as $row) {
            Staff::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'phone' => $row['phone'],
                ],
                [
                    'name' => $row['name'],
                    'role' => $row['role'],
                    'subject' => $row['subject'],
                    'salary' => $row['salary'],
                ]
            );
        }
    }

    /**
     * @param  Collection<int, SchoolClass>  $classes
     * @return Collection<int, Student>
     */
    private function seedStudents(Tenant $tenant, Collection $classes): Collection
    {
        $names = [
            ['Ama Serwaa', 'female'], ['Kofi Mensah', 'male'], ['Efua Boateng', 'female'], ['Yaw Owusu', 'male'],
            ['Abena Frimpong', 'female'], ['Kwaku Darko', 'male'], ['Akua Nyarko', 'female'], ['Yaw Bonsu', 'male'],
            ['Akosua Mensah', 'female'], ['Kojo Antwi', 'male'], ['Adwoa Kumi', 'female'], ['Fiifi Annan', 'male'],
            ['Yaa Ofori', 'female'], ['Nana Agyeman', 'male'], ['Esi Dadzie', 'female'], ['Kweku Appiah', 'male'],
            ['Afua Owusu', 'female'], ['Kwesi Boadi', 'male'], ['Maame Adu', 'female'], ['Yaw Manu', 'male'],
            ['Aba Wilson', 'female'], ['Ebenezer Lamptey', 'male'], ['Comfort Asare', 'female'], ['Samuel Teye', 'male'],
            ['Gifty Osei', 'female'], ['Patrick Ampadu', 'male'], ['Ruth Okyere', 'female'], ['Isaac Donkor', 'male'],
            ['Priscilla Amoah', 'female'], ['Daniel Kwarteng', 'male'], ['Lydia Agyei', 'female'], ['Michael Ofori', 'male'],
            ['Hannah Yeboah', 'female'], ['Stephen Adjei', 'male'], ['Janet Bonsu', 'female'], ['Francis Owusu', 'male'],
            ['Cecilia Mensim', 'female'], ['Richard Osei', 'male'], ['Patience Adu', 'female'], ['Thomas Boakye', 'male'],
            ['Vivian Appiah', 'female'], ['Emmanuel Sarpong', 'male'],
        ];

        for ($n = count($names); $n < 88; $n++) {
            $names[] = ['Learner '.($n + 1), $n % 2 === 0 ? 'female' : 'male'];
        }

        // Class count is 11 (KG1, KG2, Basic 1..6, JHS 1..3). Distribute students.
        $perClass = [8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8];
        $classOrder = [];
        foreach ($perClass as $classIndex => $count) {
            for ($j = 0; $j < $count; $j++) {
                $classOrder[] = $classIndex;
            }
        }

        $parents = [
            ['Mr. & Mrs. Serwaa', '+233241100101', '12 Palm Street, East Legon'],
            ['Mrs. Akua Mensah', '+233241100102', '45 Cocoa Road, Taifa'],
            ['Mr. Boateng', '+233241100103', '8 School Lane, Madina'],
            ['Mrs. Owusu', '+233241100104', '22 Ring Road Central'],
            ['Mr. Frimpong', '+233241100105', '5 N4 Highway, Kasoa'],
        ];

        $out = collect();

        foreach ($names as $i => [$name, $gender]) {
            if (! isset($classOrder[$i])) {
                break;
            }
            $class = $classes[$classOrder[$i]];
            $p = $parents[$i % count($parents)];
            $status = match ($i) {
                12, 37, 48 => 'inactive',
                25 => 'graduated',
                default => 'active',
            };

            $out->push(Student::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'class_id' => $class->id,
                    'name' => $name,
                ],
                [
                    'gender' => $gender,
                    'dob' => Carbon::now()->subYears(6 + ($i % 10))->subMonths($i % 12)->startOfMonth()->toDateString(),
                    'parent_name' => $p[0],
                    'parent_phone' => $p[1],
                    'address' => $p[2].', Unit '.(1 + ($i % 4)),
                    'admission_date' => Carbon::now()->subMonths(3 + ($i % 18))->toDateString(),
                    'admission_no' => sprintf('EA-%04d', 2025_000 + $i + 1),
                    'status' => $status,
                ]
            ));
        }

        return $out;
    }

    /**
     * @param  Collection<int, SchoolClass>  $classes
     */
    private function seedSubjects(int $tenantId, Collection $classes): void
    {
        foreach ($classes as $class) {
            foreach (self::SUBJECT_NAMES as $subjectName) {
                Subject::query()->updateOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'class_id' => $class->id,
                        'name' => $subjectName,
                    ],
                    []
                );
            }
        }
    }

    /**
     * @param  Collection<int, SchoolClass>  $classes
     * @param  Collection<int, Term>  $terms
     */
    private function seedFees(int $tenantId, Collection $classes, Collection $terms): void
    {
        $bases = [
            'Tuition' => 720.0,
            'Admission' => 120.0,
            'Exam Fee' => 90.0,
            'PTA' => 45.0,
            'Feeding' => 180.0,
            'Transport' => 150.0,
        ];

        $tier = [1.0, 1.0, 1.08, 1.12, 1.18, 1.28];

        foreach ($terms->sortBy('name')->values() as $term) {
            foreach ($classes->values() as $idx => $class) {
                $m = $tier[$idx] ?? 1.0;
                foreach ($bases as $type => $base) {
                    Fee::query()->updateOrCreate(
                        [
                            'tenant_id' => $tenantId,
                            'class_id' => $class->id,
                            'term_id' => $term->id,
                            'fee_type' => $type,
                        ],
                        [
                            'amount' => round($base * $m, 2),
                        ]
                    );
                }
            }
        }
    }

    /**
     * @param  Collection<int, Student>  $students
     */
    private function seedPayments(int $tenantId, Collection $students): void
    {
        $expectedByClass = Fee::query()
            ->where('tenant_id', $tenantId)
            ->selectRaw('class_id, SUM(amount) as total')
            ->groupBy('class_id')
            ->pluck('total', 'class_id');

        $methods = ['cash', 'momo', 'bank'];
        $seq = 1;

        foreach ($students as $i => $student) {
            $expected = (float) ($expectedByClass[(int) $student->class_id] ?? 0.0);
            if ($expected <= 0) {
                continue;
            }

            $ratios = [0.48, 0.62, 0.74, 0.88, 1.0];
            $ratio = $ratios[$i % count($ratios)];
            $target = round(min($expected, $expected * $ratio), 2);

            if ($target <= 0) {
                continue;
            }

            if ($i % 4 === 0) {
                $chunks = [$target];
            } elseif ($i % 4 === 1) {
                $a = round($target * 0.45, 2);
                $b = round($target - $a, 2);
                $chunks = array_values(array_filter([$a, $b], fn ($x) => $x > 0));
            } else {
                $a = round($target * 0.35, 2);
                $b = round($target * 0.3, 2);
                $c = round($target - $a - $b, 2);
                $chunks = array_values(array_filter([$a, $b, $c], fn ($x) => $x > 0));
            }

            foreach ($chunks as $amount) {
                $rid = 'DEMO-RCPT-'.str_pad((string) $seq, 5, '0', STR_PAD_LEFT);
                Payment::query()->updateOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'receipt_id' => $rid,
                    ],
                    [
                        'student_id' => $student->id,
                        'amount' => $amount,
                        'payment_channel' => $methods[$seq % 3],
                        'provider' => 'manual',
                        'status' => 'successful',
                        'payment_reference' => $seq % 5 === 0 ? 'TXN-DEMO-'.$seq : null,
                        'reference' => $seq % 5 === 0 ? 'TXN-DEMO-'.$seq : null,
                        'date' => Carbon::today()->subDays(($seq * 3) % 55)->toDateString(),
                    ]
                );
                $seq++;
            }
        }
    }

    /**
     * @param  Collection<int, Student>  $students
     * @param  Collection<int, Term>  $terms
     */
    private function seedResults(int $tenantId, Collection $students, AcademicYear $year, Collection $terms): void
    {
        $term = $terms->firstWhere('is_current', true) ?? $terms->first();

        foreach ($students as $student) {
            $subjects = Subject::query()
                ->where('tenant_id', $tenantId)
                ->where('class_id', $student->class_id)
                ->orderBy('name')
                ->get();

            foreach ($subjects as $subject) {
                $h = (int) (($student->id * 7 + $subject->id * 3) % 100);
                if ($h % 8 === 0) {
                    Result::query()->updateOrCreate(
                        [
                            'tenant_id' => $tenantId,
                            'student_id' => $student->id,
                            'subject_id' => $subject->id,
                            'academic_year_id' => $year->id,
                            'term_id' => $term->id,
                        ],
                        [
                            'class_test' => null,
                            'midterm' => null,
                            'exam' => null,
                        ]
                    );

                    continue;
                }

                $classTest = 8 + ($h % 11);
                $midterm = 10 + (($h * 2) % 14);
                $exam = 22 + (($h * 3) % 23);

                Result::query()->updateOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'student_id' => $student->id,
                        'subject_id' => $subject->id,
                        'academic_year_id' => $year->id,
                        'term_id' => $term->id,
                    ],
                    [
                        'class_test' => $classTest,
                        'midterm' => $midterm,
                        'exam' => $exam,
                    ]
                );
            }
        }
    }

    /**
     * @param  Collection<int, Student>  $students
     * @param  Collection<int, Term>  $terms
     */
    private function seedAttendance(int $tenantId, Collection $students, AcademicYear $year, Collection $terms): void
    {
        $term = $terms->firstWhere('is_current', true) ?? $terms->first();

        for ($d = 0; $d <= 35; $d++) {
            $date = Carbon::today()->subDays($d)->toDateString();

            foreach ($students as $idx => $student) {
                if ($d === 0 && $idx < 10) {
                    $status = $idx < 4 ? 'absent' : 'present';

                    Attendance::query()->updateOrCreate(
                        [
                            'tenant_id' => $tenantId,
                            'student_id' => $student->id,
                            'date' => $date,
                        ],
                        [
                            'academic_year_id' => $year->id,
                            'term_id' => $term->id,
                            'status' => $status,
                            'remarks' => $status === 'absent' ? 'No contact from parent' : null,
                        ]
                    );

                    continue;
                }

                $h = crc32((string) $student->id.'|'.$date) % 100;
                $status = match (true) {
                    $h < 5 => 'absent',
                    $h < 8 => 'late',
                    $h < 9 => 'excused',
                    default => 'present',
                };

                Attendance::query()->updateOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'student_id' => $student->id,
                        'date' => $date,
                    ],
                    [
                        'academic_year_id' => $year->id,
                        'term_id' => $term->id,
                        'status' => $status,
                        'remarks' => $status === 'late' ? 'Arrived after assembly' : null,
                    ]
                );
            }
        }
    }

    private function seedSubscription(Tenant $tenant): void
    {
        $growth = Plan::query()->where('name', 'Growth')->firstOrFail();

        Subscription::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'plan_id' => $growth->id,
                'start_date' => '2026-01-01',
            ],
            [
                'end_date' => '2027-01-01',
                'status' => Subscription::STATUS_ACTIVE,
                'payment_id' => null,
            ]
        );

        $tenant->update(['plan_id' => $growth->id]);
    }
}
