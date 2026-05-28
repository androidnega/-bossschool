<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Fee;
use App\Models\OnboardingProgress;
use App\Models\SchoolClass;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Tenant;
use App\Models\TenantBackup;
use App\Models\Term;

/**
 * The 12-step pilot onboarding wizard.
 *
 * The wizard does not duplicate domain forms (school profile, terms, fees,
 * etc.) — instead, each step renders a thin "checkpoint" card that links
 * back to the canonical screen. The wizard only persists *progress*:
 *
 *   - current_step       1..12
 *   - completed_steps    array of step keys explicitly marked done
 *   - payload            free-form notes per step (e.g. number of imports)
 *
 * "Essentials" (steps 1-9) must be auto-detected as complete before the
 * tenant can be marked onboarding_complete. SMS/payment and first backup
 * (steps 11-12) are recommended but not required to finish pilot
 * onboarding.
 */
class OnboardingWizardService
{
    public const STEPS = [
        1 => ['key' => 'school_profile', 'label' => 'School profile', 'essential' => true],
        2 => ['key' => 'academic_year', 'label' => 'Academic year', 'essential' => true],
        3 => ['key' => 'terms', 'label' => 'Terms', 'essential' => true],
        4 => ['key' => 'classes', 'label' => 'Classes', 'essential' => true],
        5 => ['key' => 'subjects', 'label' => 'Subjects', 'essential' => true],
        6 => ['key' => 'staff', 'label' => 'Staff', 'essential' => true],
        7 => ['key' => 'teacher_assignments', 'label' => 'Teacher assignments', 'essential' => true],
        8 => ['key' => 'students', 'label' => 'Students', 'essential' => true],
        9 => ['key' => 'fees', 'label' => 'Fees / invoices', 'essential' => true],
        10 => ['key' => 'report_card_settings', 'label' => 'Report-card settings', 'essential' => false],
        11 => ['key' => 'sms_payment', 'label' => 'SMS & payment settings', 'essential' => false],
        12 => ['key' => 'first_backup', 'label' => 'First backup', 'essential' => false],
    ];

    public function progressFor(Tenant $tenant): OnboardingProgress
    {
        return OnboardingProgress::query()->firstOrCreate(
            ['tenant_id' => $tenant->id],
            ['current_step' => 1, 'completed_steps' => []]
        );
    }

    /** Auto-detected status for each step regardless of explicit marking. */
    public function autoDetected(Tenant $tenant): array
    {
        $school = $tenant->school()->first();

        return [
            'school_profile' => (bool) $school && trim((string) $school->name) !== '',
            'academic_year' => AcademicYear::query()->where('tenant_id', $tenant->id)->exists(),
            'terms' => Term::query()->where('tenant_id', $tenant->id)->count() >= 3,
            'classes' => SchoolClass::query()->where('tenant_id', $tenant->id)->exists(),
            'subjects' => Subject::query()->where('tenant_id', $tenant->id)->exists(),
            'staff' => Staff::query()->where('tenant_id', $tenant->id)->exists(),
            'teacher_assignments' => \Illuminate\Support\Facades\DB::table('teacher_subject')
                ->where('tenant_id', $tenant->id)
                ->exists(),
            'students' => Student::query()->where('tenant_id', $tenant->id)->exists(),
            'fees' => Fee::query()->where('tenant_id', $tenant->id)->exists(),
            'report_card_settings' => false, // optional, never auto-required
            'sms_payment' => false,
            'first_backup' => TenantBackup::query()
                ->where('tenant_id', $tenant->id)
                ->where('status', TenantBackup::STATUS_COMPLETED)
                ->exists(),
        ];
    }

    /**
     * @return array<int, array{step:int,key:string,label:string,essential:bool,done:bool}>
     */
    public function stepStatuses(Tenant $tenant): array
    {
        $auto = $this->autoDetected($tenant);
        $progress = $this->progressFor($tenant);
        $marked = array_flip((array) $progress->completed_steps);

        $out = [];
        foreach (self::STEPS as $step => $meta) {
            $out[] = [
                'step' => $step,
                'key' => $meta['key'],
                'label' => $meta['label'],
                'essential' => $meta['essential'],
                'done' => ($auto[$meta['key']] ?? false) || isset($marked[$meta['key']]),
            ];
        }

        return $out;
    }

    public function canFinish(Tenant $tenant): bool
    {
        foreach ($this->stepStatuses($tenant) as $step) {
            if ($step['essential'] && ! $step['done']) {
                return false;
            }
        }

        return true;
    }

    public function finish(Tenant $tenant, int $userId): bool
    {
        if (! $this->canFinish($tenant)) {
            return false;
        }
        $tenant->forceFill([
            'onboarding_complete' => true,
            'onboarding_completed_at' => now(),
        ])->save();

        $progress = $this->progressFor($tenant);
        $progress->forceFill([
            'finished_at' => now(),
            'updated_by_user_id' => $userId,
            'current_step' => count(self::STEPS),
        ])->save();

        return true;
    }
}
