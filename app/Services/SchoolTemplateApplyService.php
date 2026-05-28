<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Fee;
use App\Models\SchoolClass;
use App\Models\SchoolTemplate;
use App\Models\Subject;
use App\Models\TemplateClass;
use App\Models\TemplateFeeItem;
use App\Models\TemplateLevel;
use App\Models\TemplateSubject;
use App\Models\TemplateTerm;
use App\Models\Tenant;
use App\Models\Term;
use Illuminate\Support\Facades\DB;

/**
 * Apply a SchoolTemplate to a real tenant — i.e. copy the template's
 * classes / subjects / terms / fee placeholders into the live per-tenant
 * tables.
 *
 * NEVER copies students, staff, attendance, results, payments or messages.
 * The template is a blueprint; every school edits its own copy afterwards.
 *
 * Returns a structured summary:
 *
 *   [
 *     'template'           => ['id' => 4, 'code' => 'GH_FULL_BASIC', 'name' => '...'],
 *     'academic_year_id'   => 17,
 *     'classes_created'    => 11,
 *     'subjects_created'   => 84,
 *     'terms_created'      => 3,
 *     'fee_items_created'  => 6,
 *     'fee_rows_created'   => 0,    // rows inserted in the `fees` table
 *     'skipped_kg'         => false,
 *   ]
 */
class SchoolTemplateApplyService
{
    /**
     * @param  array{
     *     academic_year_name?:?string,
     *     starts_on?:?string,
     *     ends_on?:?string,
     *     include_kg?:bool,
     *     create_default_fees?:bool,
     *     created_by_user_id?:?int,
     * } $options
     *
     * @return array<string, mixed>
     */
    public function apply(Tenant $tenant, SchoolTemplate $template, array $options = []): array
    {
        return DB::transaction(function () use ($tenant, $template, $options): array {
            $tenantId = (int) $tenant->id;
            $includeKg = (bool) ($options['include_kg'] ?? true);
            $createFees = (bool) ($options['create_default_fees'] ?? false);

            $year = $this->createAcademicYear(
                tenantId: $tenantId,
                desiredName: $options['academic_year_name'] ?? null,
                startsOn: $options['starts_on'] ?? null,
                endsOn: $options['ends_on'] ?? null,
                createdByUserId: $options['created_by_user_id'] ?? null,
            );

            $termsCreated = $this->cloneTerms($tenantId, $template, $year);

            [$classMap, $classesCreated, $skippedLevelIds] = $this->cloneClasses(
                $tenantId,
                $template,
                $includeKg
            );

            $subjectsCreated = $this->cloneSubjects(
                $tenantId,
                $template,
                $classMap,
                $skippedLevelIds
            );

            $feeRowsCreated = 0;
            $feeItemsCreated = 0;
            if ($createFees) {
                $firstTerm = Term::query()
                    ->where('tenant_id', $tenantId)
                    ->where('academic_year_id', $year->id)
                    ->orderBy('term_order')
                    ->first();

                if ($firstTerm) {
                    [$feeItemsCreated, $feeRowsCreated] = $this->cloneFeeItems(
                        $tenantId,
                        $template,
                        $classMap,
                        $firstTerm
                    );
                }
            }

            return [
                'template' => [
                    'id' => $template->id,
                    'code' => $template->code,
                    'name' => $template->name,
                ],
                'academic_year_id' => $year->id,
                'classes_created' => $classesCreated,
                'subjects_created' => $subjectsCreated,
                'terms_created' => $termsCreated,
                'fee_items_created' => $feeItemsCreated,
                'fee_rows_created' => $feeRowsCreated,
                'skipped_kg' => ! $includeKg && $this->templateHasKg($template),
            ];
        });
    }

    /* ─────────────────────────── academic year ─────────────────────── */

    private function createAcademicYear(
        int $tenantId,
        ?string $desiredName,
        ?string $startsOn,
        ?string $endsOn,
        ?int $createdByUserId
    ): AcademicYear {
        $name = $desiredName !== null && trim($desiredName) !== ''
            ? trim($desiredName)
            : now()->year.'/'.(now()->year + 1);

        // No other current year may exist for this tenant.
        AcademicYear::query()
            ->where('tenant_id', $tenantId)
            ->where('is_current', true)
            ->update(['is_current' => false]);

        return AcademicYear::query()->updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'name' => $name,
            ],
            [
                'starts_on' => $startsOn ?: now()->startOfYear()->toDateString(),
                'ends_on' => $endsOn ?: now()->endOfYear()->toDateString(),
                'is_current' => true,
                'status' => AcademicYear::STATUS_ACTIVE,
                'created_by_user_id' => $createdByUserId,
            ]
        );
    }

    /* ─────────────────────────── terms ─────────────────────────────── */

    private function cloneTerms(int $tenantId, SchoolTemplate $template, AcademicYear $year): int
    {
        $created = 0;
        $now = now();

        $templateTerms = $template->terms()->orderBy('sort_order')->get();

        foreach ($templateTerms as $i => $t) {
            /** @var TemplateTerm $t */
            $existing = Term::query()
                ->where('tenant_id', $tenantId)
                ->where('academic_year_id', $year->id)
                ->where('name', $t->name)
                ->first();

            // Term-spacing heuristic: ~4 months per term, starting in the
            // first month of the year.
            $startsOn = $now->copy()->startOfYear()->addMonths($i * 4)->toDateString();
            $endsOn = $now->copy()->startOfYear()->addMonths($i * 4 + 3)->endOfMonth()->toDateString();

            if ($existing === null) {
                Term::query()->create([
                    'tenant_id' => $tenantId,
                    'academic_year_id' => $year->id,
                    'name' => $t->name,
                    'term_order' => $i + 1,
                    'starts_on' => $startsOn,
                    'ends_on' => $endsOn,
                    'is_current' => $t->is_active_default,
                    'status' => Term::STATUS_ACTIVE,
                ]);
                $created++;
            }
        }

        return $created;
    }

    /* ─────────────────────────── classes ───────────────────────────── */

    /**
     * @return array{0: array<int,int>, 1: int, 2: array<int, true>}
     *         [template_class_id => real class_id, classes_created, skipped_level_ids]
     */
    private function cloneClasses(int $tenantId, SchoolTemplate $template, bool $includeKg): array
    {
        $skippedLevels = [];
        if (! $includeKg) {
            $kgLevelIds = $template->levels()
                ->where('code', TemplateLevel::CODE_KG)
                ->pluck('id')
                ->all();
            foreach ($kgLevelIds as $id) {
                $skippedLevels[$id] = true;
            }
        }

        $classes = $template->classes()->orderBy('sort_order')->get();
        $created = 0;
        $map = [];

        foreach ($classes as $tplClass) {
            /** @var TemplateClass $tplClass */
            if (isset($skippedLevels[$tplClass->template_level_id])) {
                continue;
            }

            $real = SchoolClass::query()->where('tenant_id', $tenantId)
                ->where('name', $tplClass->name)
                ->first();

            if ($real === null) {
                $real = SchoolClass::query()->create([
                    'tenant_id' => $tenantId,
                    'name' => $tplClass->name,
                    'section' => 'A',
                ]);
                $created++;
            }

            $map[$tplClass->id] = (int) $real->id;
        }

        return [$map, $created, $skippedLevels];
    }

    /* ─────────────────────────── subjects ──────────────────────────── */

    /**
     * Template subjects can be anchored by level (apply to every class in
     * that level) OR by a specific template class. We resolve both into
     * concrete real classes via $classMap and then create one Subject row
     * per (real class, subject name) pair.
     *
     * @param  array<int,int>  $classMap     template_class_id => real_class_id
     * @param  array<int,true> $skippedLevels
     */
    private function cloneSubjects(
        int $tenantId,
        SchoolTemplate $template,
        array $classMap,
        array $skippedLevels
    ): int {
        $templateSubjects = $template->subjects()->orderBy('sort_order')->get();
        if ($templateSubjects->isEmpty() || empty($classMap)) {
            return 0;
        }

        // Pre-build a level→[realClassId,…] index from the template.
        /** @var array<int, list<int>> $levelToRealClasses */
        $levelToRealClasses = [];
        $templateClasses = $template->classes()->get();
        foreach ($templateClasses as $tc) {
            if (! isset($classMap[$tc->id])) {
                continue;
            }
            $levelId = (int) $tc->template_level_id;
            if (! isset($levelToRealClasses[$levelId])) {
                $levelToRealClasses[$levelId] = [];
            }
            $levelToRealClasses[$levelId][] = $classMap[$tc->id];
        }

        $created = 0;

        foreach ($templateSubjects as $ts) {
            /** @var TemplateSubject $ts */
            if (isset($skippedLevels[$ts->template_level_id])) {
                continue;
            }

            $targetClassIds = $this->resolveTargetClasses($ts, $classMap, $levelToRealClasses);

            foreach ($targetClassIds as $classId) {
                $exists = Subject::query()
                    ->where('tenant_id', $tenantId)
                    ->where('class_id', $classId)
                    ->where('name', $ts->name)
                    ->exists();

                if (! $exists) {
                    Subject::query()->create([
                        'tenant_id' => $tenantId,
                        'class_id' => $classId,
                        'name' => $ts->name,
                    ]);
                    $created++;
                }
            }
        }

        return $created;
    }

    /**
     * @param  array<int,int>            $classMap
     * @param  array<int, list<int>>     $levelToRealClasses
     * @return list<int>
     */
    private function resolveTargetClasses(
        TemplateSubject $ts,
        array $classMap,
        array $levelToRealClasses
    ): array {
        // Subject pinned to a specific template class.
        if ($ts->template_class_id !== null) {
            return isset($classMap[$ts->template_class_id])
                ? [$classMap[$ts->template_class_id]]
                : [];
        }

        // Subject pinned to a level → fan out to every class in that level.
        if ($ts->template_level_id !== null) {
            return $levelToRealClasses[(int) $ts->template_level_id] ?? [];
        }

        // No anchor — apply to every real class.
        return array_values($classMap);
    }

    /* ─────────────────────────── fee items ─────────────────────────── */

    /**
     * Create one row in the `fees` table per (class × fee item) for the
     * first term of the new academic year. `amount` is left at 0.00 so
     * the bursar fills in the real numbers in the Fees screen.
     *
     * @param  array<int,int>  $classMap
     * @return array{0:int,1:int}  [feeItemsCreated, feeRowsCreated]
     */
    private function cloneFeeItems(
        int $tenantId,
        SchoolTemplate $template,
        array $classMap,
        Term $firstTerm
    ): array {
        $items = $template->feeItems()->where('is_active', true)->orderBy('sort_order')->get();
        if ($items->isEmpty() || empty($classMap)) {
            return [0, 0];
        }

        $rowsCreated = 0;
        $itemsCreated = 0;

        foreach ($items as $item) {
            /** @var TemplateFeeItem $item */
            $touched = false;
            foreach ($classMap as $classId) {
                $exists = Fee::query()
                    ->where('tenant_id', $tenantId)
                    ->where('class_id', $classId)
                    ->where('term_id', $firstTerm->id)
                    ->where('fee_type', $item->name)
                    ->exists();
                if ($exists) {
                    continue;
                }

                Fee::query()->create([
                    'tenant_id' => $tenantId,
                    'class_id' => $classId,
                    'term_id' => $firstTerm->id,
                    'fee_type' => $item->name,
                    'amount' => 0.00,
                ]);
                $rowsCreated++;
                $touched = true;
            }
            if ($touched) {
                $itemsCreated++;
            }
        }

        return [$itemsCreated, $rowsCreated];
    }

    private function templateHasKg(SchoolTemplate $template): bool
    {
        return $template->levels()->where('code', TemplateLevel::CODE_KG)->exists();
    }
}
