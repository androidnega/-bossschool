<?php

namespace Database\Seeders;

use App\Models\SchoolTemplate;
use App\Models\TemplateClass;
use App\Models\TemplateFeeItem;
use App\Models\TemplateLevel;
use App\Models\TemplateSubject;
use App\Models\TemplateTerm;
use Illuminate\Database\Seeder;

/**
 * Production-safe seeder. Inserts ONLY template (catalogue) rows. Never
 * creates tenants, schools, students, staff, attendance, results, payments,
 * messages or any other operational data.
 *
 * Idempotent — uses updateOrCreate everywhere so re-running on a populated
 * DB does not duplicate rows.
 *
 * Templates created:
 *   1. Ghana Primary Only      (Primary 1..6)
 *   2. Ghana JHS Only          (JHS 1..3)
 *   3. Ghana Primary + JHS     (Primary 1..6 and JHS 1..3)
 *   4. Ghana Full Basic School (KG 1..2, Primary 1..6, JHS 1..3)
 */
class GhanaBasicSchoolTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPrimaryOnly();
        $this->seedJhsOnly();
        $this->seedPrimaryAndJhs();
        $this->seedFullBasic();
    }

    /* ─────────────────────────── Templates ─────────────────────────── */

    private function seedPrimaryOnly(): void
    {
        $tpl = $this->template(SchoolTemplate::CODE_PRIMARY_ONLY, [
            'name' => 'Ghana Primary Only',
            'description' => 'Creates Primary 1 to Primary 6 with editable Ghanaian primary subjects.',
            'curriculum_label' => 'GES Standards-Based Curriculum (Primary)',
            'sort_order' => 10,
        ]);

        $lower = $this->level($tpl, TemplateLevel::CODE_LOWER_PRIMARY, 'Lower Primary', 20);
        $upper = $this->level($tpl, TemplateLevel::CODE_UPPER_PRIMARY, 'Upper Primary', 30);

        $this->classes($tpl, $lower, ['Primary 1', 'Primary 2', 'Primary 3'], 100);
        $this->classes($tpl, $upper, ['Primary 4', 'Primary 5', 'Primary 6'], 200);

        $this->primarySubjects($tpl);
        $this->defaultTerms($tpl);
        $this->defaultFeeItems($tpl);
    }

    private function seedJhsOnly(): void
    {
        $tpl = $this->template(SchoolTemplate::CODE_JHS_ONLY, [
            'name' => 'Ghana JHS Only',
            'description' => 'Creates JHS 1 to JHS 3 with editable Ghanaian JHS subjects.',
            'curriculum_label' => 'GES Common-Core Programme (JHS)',
            'sort_order' => 20,
        ]);

        $jhs = $this->level($tpl, TemplateLevel::CODE_JHS, 'Junior High School', 40);

        $this->classes($tpl, $jhs, ['JHS 1', 'JHS 2', 'JHS 3'], 300);

        $this->jhsSubjects($tpl);
        $this->defaultTerms($tpl);
        $this->defaultFeeItems($tpl);
    }

    private function seedPrimaryAndJhs(): void
    {
        $tpl = $this->template(SchoolTemplate::CODE_PRIMARY_JHS, [
            'name' => 'Ghana Primary + JHS',
            'description' => 'Creates Primary 1 to Primary 6 and JHS 1 to JHS 3.',
            'curriculum_label' => 'GES Basic Schools (Primary + JHS)',
            'sort_order' => 30,
        ]);

        $lower = $this->level($tpl, TemplateLevel::CODE_LOWER_PRIMARY, 'Lower Primary', 20);
        $upper = $this->level($tpl, TemplateLevel::CODE_UPPER_PRIMARY, 'Upper Primary', 30);
        $jhs   = $this->level($tpl, TemplateLevel::CODE_JHS, 'Junior High School', 40);

        $this->classes($tpl, $lower, ['Primary 1', 'Primary 2', 'Primary 3'], 100);
        $this->classes($tpl, $upper, ['Primary 4', 'Primary 5', 'Primary 6'], 200);
        $this->classes($tpl, $jhs,   ['JHS 1', 'JHS 2', 'JHS 3'], 300);

        $this->primarySubjects($tpl);
        $this->jhsSubjects($tpl);
        $this->defaultTerms($tpl);
        $this->defaultFeeItems($tpl);
    }

    private function seedFullBasic(): void
    {
        $tpl = $this->template(SchoolTemplate::CODE_FULL_BASIC, [
            'name' => 'Ghana Full Basic School',
            'description' => 'Creates KG 1 to KG 2, Primary 1 to Primary 6, and JHS 1 to JHS 3.',
            'curriculum_label' => 'GES Basic Schools (KG + Primary + JHS)',
            'sort_order' => 40,
        ]);

        $kg    = $this->level($tpl, TemplateLevel::CODE_KG, 'Kindergarten', 10, isOptional: true);
        $lower = $this->level($tpl, TemplateLevel::CODE_LOWER_PRIMARY, 'Lower Primary', 20);
        $upper = $this->level($tpl, TemplateLevel::CODE_UPPER_PRIMARY, 'Upper Primary', 30);
        $jhs   = $this->level($tpl, TemplateLevel::CODE_JHS, 'Junior High School', 40);

        $this->classes($tpl, $kg,    ['KG 1', 'KG 2'], 50);
        $this->classes($tpl, $lower, ['Primary 1', 'Primary 2', 'Primary 3'], 100);
        $this->classes($tpl, $upper, ['Primary 4', 'Primary 5', 'Primary 6'], 200);
        $this->classes($tpl, $jhs,   ['JHS 1', 'JHS 2', 'JHS 3'], 300);

        $this->kgSubjects($tpl);
        $this->primarySubjects($tpl);
        $this->jhsSubjects($tpl);
        $this->defaultTerms($tpl);
        $this->defaultFeeItems($tpl);
    }

    /* ────────────────────────── Subject sets ───────────────────────── */

    private function kgSubjects(SchoolTemplate $tpl): void
    {
        $level = $tpl->levels()->where('code', TemplateLevel::CODE_KG)->first();
        if (! $level) {
            return;
        }
        $subjects = [
            'Literacy',
            'Numeracy',
            'Our World Our People',
            'Creative Arts',
            'Physical Development',
            'Religious and Moral Education',
            'Ghanaian Language',
        ];
        $this->subjectsForLevel($tpl, $level, $subjects, 1000);
    }

    private function primarySubjects(SchoolTemplate $tpl): void
    {
        $subjects = [
            'English Language',
            'Mathematics',
            'Science',
            'Our World Our People',
            'Creative Arts',
            'Religious and Moral Education',
            'Computing',
            'Ghanaian Language',
            'Physical Education',
            'History',
        ];

        // Attach to both Lower and Upper Primary levels if present.
        foreach ([TemplateLevel::CODE_LOWER_PRIMARY, TemplateLevel::CODE_UPPER_PRIMARY] as $code) {
            $level = $tpl->levels()->where('code', $code)->first();
            if ($level) {
                $this->subjectsForLevel($tpl, $level, $subjects, 2000);
            }
        }
    }

    private function jhsSubjects(SchoolTemplate $tpl): void
    {
        $level = $tpl->levels()->where('code', TemplateLevel::CODE_JHS)->first();
        if (! $level) {
            return;
        }
        $subjects = [
            'English Language',
            'Mathematics',
            'Science',
            'Social Studies',
            'Religious and Moral Education',
            'Computing',
            'Career Technology',
            'Creative Arts and Design',
            'Ghanaian Language',
            'Physical Education',
        ];
        $this->subjectsForLevel($tpl, $level, $subjects, 3000);
    }

    /* ─────────────────────────── Shared bits ───────────────────────── */

    private function defaultTerms(SchoolTemplate $tpl): void
    {
        $terms = [
            ['Term 1', 'T1', true],
            ['Term 2', 'T2', false],
            ['Term 3', 'T3', false],
        ];

        foreach ($terms as $i => [$name, $short, $activeDefault]) {
            TemplateTerm::query()->updateOrCreate(
                [
                    'school_template_id' => $tpl->id,
                    'name' => $name,
                ],
                [
                    'short_name' => $short,
                    'sort_order' => ($i + 1) * 10,
                    'is_active_default' => $activeDefault,
                ]
            );
        }
    }

    private function defaultFeeItems(SchoolTemplate $tpl): void
    {
        $items = [
            ['Tuition Fee',     'Termly tuition charge for each enrolled student.',         false],
            ['PTA Dues',        'Parent–Teacher Association dues (collected per term).',    true],
            ['Examination Fee', 'Costs for end-of-term examinations and report cards.',     false],
            ['ICT Fee',         'Maintenance of school computers and internet.',            true],
            ['Sports Fee',      'Inter-school games, sports kits and field activities.',    true],
            ['Maintenance Fee', 'Upkeep of school buildings, furniture and facilities.',    true],
        ];

        foreach ($items as $i => [$name, $description, $optional]) {
            TemplateFeeItem::query()->updateOrCreate(
                [
                    'school_template_id' => $tpl->id,
                    'name' => $name,
                ],
                [
                    'description' => $description,
                    'amount' => null,
                    'is_optional' => $optional,
                    'is_active' => true,
                    'sort_order' => ($i + 1) * 10,
                ]
            );
        }
    }

    /* ───────────────────────── tiny helpers ────────────────────────── */

    private function template(string $code, array $attrs): SchoolTemplate
    {
        return SchoolTemplate::query()->updateOrCreate(
            ['code' => $code],
            array_merge([
                'country' => 'GH',
                'is_active' => true,
            ], $attrs)
        );
    }

    private function level(
        SchoolTemplate $tpl,
        string $code,
        string $name,
        int $sort,
        bool $isOptional = false
    ): TemplateLevel {
        return TemplateLevel::query()->updateOrCreate(
            [
                'school_template_id' => $tpl->id,
                'code' => $code,
            ],
            [
                'name' => $name,
                'sort_order' => $sort,
                'is_optional' => $isOptional,
            ]
        );
    }

    /**
     * @param  array<int, string>  $names
     */
    private function classes(SchoolTemplate $tpl, TemplateLevel $level, array $names, int $baseSort): void
    {
        foreach ($names as $i => $name) {
            TemplateClass::query()->updateOrCreate(
                [
                    'school_template_id' => $tpl->id,
                    'name' => $name,
                ],
                [
                    'template_level_id' => $level->id,
                    'short_name' => $name,
                    'sort_order' => $baseSort + ($i + 1),
                ]
            );
        }
    }

    /**
     * @param  array<int, string>  $names
     */
    private function subjectsForLevel(
        SchoolTemplate $tpl,
        TemplateLevel $level,
        array $names,
        int $baseSort
    ): void {
        foreach ($names as $i => $name) {
            TemplateSubject::query()->updateOrCreate(
                [
                    'school_template_id' => $tpl->id,
                    'template_level_id' => $level->id,
                    'name' => $name,
                ],
                [
                    'short_name' => null,
                    'code' => null,
                    'is_core' => true,
                    'is_editable' => true,
                    'sort_order' => $baseSort + ($i + 1),
                ]
            );
        }
    }
}
