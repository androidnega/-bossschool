<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\Plan;
use App\Models\PlatformSetting;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\SchoolTemplate;
use App\Models\Tenant;
use App\Models\Term;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * One-call tenant provisioning.
 *
 * Responsibilities:
 *  - Create the tenant row with sane defaults (status, trial end).
 *  - Create the school profile shell.
 *  - Auto-create the first academic year + 3 default terms.
 *  - Create the default JHS/Primary class list if requested.
 *  - Provision the first Admin user with a UNIQUE temporary password
 *    (never a default like "password"), flagged force_password_reset.
 *  - Optionally seed Ghana demo data.
 *  - Return a structured result that includes the plaintext credentials
 *    so they can be emailed or written to a downloadable file by the caller.
 *
 * Security rules enforced here:
 *  - Demo data and demo users are NEVER seeded unless explicitly enabled in
 *    the call AND demo provisioning is allowed by config (defaults to OFF in
 *    production).
 *  - Passwords are written ONCE to the result; never logged, never echoed.
 *  - In production, demo flags are ignored unless DEMO_ALLOWED_IN_PROD=true.
 */
class TenantProvisioningService
{
    public function __construct(
        private readonly ActivityLogger $logger,
        private readonly PermissionService $permissions,
        private readonly SchoolTemplateApplyService $templateApply,
    ) {}

    public const DEFAULT_PRIMARY_CLASSES = ['Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 5', 'Class 6'];

    public const DEFAULT_JHS_CLASSES = ['JHS 1', 'JHS 2', 'JHS 3'];

    /**
     * @param array{
     *     name:string,
     *     subdomain:string,
     *     plan_id?:?int,
     *     status?:string,
     *     admin_name?:string,
     *     admin_email?:string,
     *     school_template_id?:?int,
     *     school_template_code?:?string,
     *     academic_year_name?:?string,
     *     include_kg?:bool,
     *     create_default_fees?:bool,
     *     create_default_classes?:bool,
     *     create_demo_data?:bool,
     *     create_demo_users?:bool,
     *     send_email?:bool,
     * } $input
     *
     * @return array{
     *     tenant:Tenant,
     *     admin:User,
     *     temp_password:string,
     *     credentials_file:?string,
     *     template_summary:?array<string, mixed>,
     *     checklist:array<int, array{key:string,label:string,done:bool,detail:?string}>,
     * }
     */
    public function provision(array $input): array
    {
        return DB::transaction(function () use ($input): array {
            $trialDays = PlatformSetting::getInt('default_trial_days', 14);

            $tenant = Tenant::query()->create([
                'name' => $input['name'],
                'subdomain' => $input['subdomain'],
                'plan_id' => $input['plan_id'] ?? Plan::query()->orderBy('id')->value('id'),
                'status' => ($input['status'] ?? 'trial') === 'active' ? Tenant::STATUS_ACTIVE : Tenant::STATUS_TRIAL,
                'trial_end' => ($input['status'] ?? 'trial') === 'trial' ? now()->addDays($trialDays) : null,
                'onboarding_complete' => false,
            ]);

            School::query()->create([
                'tenant_id' => $tenant->id,
                'name' => $input['name'],
            ]);

            $template = $this->resolveTemplate($input);
            $templateSummary = null;

            // Create the admin user FIRST so the template apply can attribute
            // the academic year to a real user. Password is hashed via the
            // model's 'hashed' cast.
            $tempPassword = $this->generateTempPassword();
            $adminEmail = $input['admin_email']
                ?? ($input['subdomain'].'-admin@'.(parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'example.test'));

            $admin = User::query()->create([
                'tenant_id' => $tenant->id,
                'name' => $input['admin_name'] ?? ($input['name'].' Admin'),
                'email' => $adminEmail,
                'password' => $tempPassword,
                'role' => UserRole::Admin->value,
                'force_password_reset' => true,
                'is_active' => true,
            ]);

            if ($template !== null) {
                // Modern path: apply the chosen template (classes, subjects,
                // terms, optional fee placeholders).
                $templateSummary = $this->templateApply->apply($tenant, $template, [
                    'academic_year_name' => $input['academic_year_name'] ?? null,
                    'include_kg' => (bool) ($input['include_kg'] ?? true),
                    'create_default_fees' => (bool) ($input['create_default_fees'] ?? false),
                    'created_by_user_id' => $admin->id,
                ]);
            } else {
                // Legacy path: hand-create the academic year + 3 terms, and
                // optionally the old Primary+JHS class list. Kept for the
                // bare "create_default_classes" flow used by older tests.
                $year = AcademicYear::query()->create([
                    'tenant_id' => $tenant->id,
                    'name' => now()->year.'/'.(now()->year + 1),
                    'starts_on' => now()->startOfYear()->toDateString(),
                    'ends_on' => now()->endOfYear()->toDateString(),
                    'is_current' => true,
                    'status' => AcademicYear::STATUS_ACTIVE,
                    'created_by_user_id' => $admin->id,
                ]);

                foreach (['Term 1', 'Term 2', 'Term 3'] as $i => $termName) {
                    Term::query()->create([
                        'tenant_id' => $tenant->id,
                        'academic_year_id' => $year->id,
                        'name' => $termName,
                        'term_order' => $i + 1,
                        'starts_on' => now()->copy()->addMonths($i * 4)->startOfMonth()->toDateString(),
                        'ends_on' => now()->copy()->addMonths($i * 4 + 3)->endOfMonth()->toDateString(),
                        'is_current' => $i === 0,
                        'status' => Term::STATUS_ACTIVE,
                    ]);
                }

                if (! empty($input['create_default_classes'])) {
                    foreach (array_merge(self::DEFAULT_PRIMARY_CLASSES, self::DEFAULT_JHS_CLASSES) as $cls) {
                        SchoolClass::query()->create([
                            'tenant_id' => $tenant->id,
                            'name' => $cls,
                            'section' => 'A',
                        ]);
                    }
                }
            }

            if ($this->canSeedDemo($input)) {
                $this->seedDemoData($tenant);
                if (! empty($input['create_demo_users'])) {
                    $this->seedDemoUsers($tenant);
                }
            }

            $this->logger->log(
                'tenant_provisioned',
                'Tenant provisioned via service',
                [
                    'tenant_id' => $tenant->id,
                    'subdomain' => $tenant->subdomain,
                    'template' => $template?->code,
                    'with_demo' => (bool) ($input['create_demo_data'] ?? false),
                ],
                $tenant->id,
                Tenant::class,
                $tenant->id
            );

            $credentialsFile = $this->writeCredentialsFile($tenant, $admin, $tempPassword);

            if (($input['send_email'] ?? false) && config('mail.default') !== 'log') {
                $this->sendCredentialsEmail($admin, $tempPassword, $tenant);
            }

            return [
                'tenant' => $tenant->fresh(),
                'admin' => $admin->fresh(),
                'temp_password' => $tempPassword,
                'credentials_file' => $credentialsFile,
                'template_summary' => $templateSummary,
                'checklist' => $this->checklist($tenant),
            ];
        });
    }

    /**
     * Resolve the chosen template from either an ID or a code, returning
     * null when neither is supplied (legacy path).
     */
    private function resolveTemplate(array $input): ?SchoolTemplate
    {
        if (! empty($input['school_template_id'])) {
            return SchoolTemplate::query()->find($input['school_template_id']);
        }
        if (! empty($input['school_template_code'])) {
            return SchoolTemplate::query()->where('code', $input['school_template_code'])->first();
        }

        return null;
    }

    /**
     * @return array<int, array{key:string,label:string,done:bool,detail:?string}>
     */
    public function checklist(Tenant $tenant): array
    {
        $school = School::query()->where('tenant_id', $tenant->id)->first();
        $year = AcademicYear::query()->where('tenant_id', $tenant->id)->first();
        $terms = Term::query()->where('tenant_id', $tenant->id)->count();
        $classes = SchoolClass::query()->where('tenant_id', $tenant->id)->count();
        $admins = User::query()->where('tenant_id', $tenant->id)->where('role', UserRole::Admin->value)->count();
        $perm = DB::table('permissions')->count();

        return [
            ['key' => 'tenant_row', 'label' => 'Tenant row created', 'done' => true, 'detail' => $tenant->subdomain],
            ['key' => 'school_profile', 'label' => 'School profile created', 'done' => (bool) $school, 'detail' => $school?->name],
            ['key' => 'academic_year', 'label' => 'Academic year created', 'done' => (bool) $year, 'detail' => $year?->name],
            ['key' => 'terms', 'label' => '3 terms created', 'done' => $terms >= 3, 'detail' => "$terms terms"],
            ['key' => 'classes', 'label' => 'Default classes created', 'done' => $classes > 0, 'detail' => "$classes classes"],
            ['key' => 'admin_user', 'label' => 'Admin user created', 'done' => $admins > 0, 'detail' => "$admins admins"],
            ['key' => 'permissions', 'label' => 'Default permissions seeded', 'done' => $perm > 0, 'detail' => "$perm role rows"],
        ];
    }

    private function canSeedDemo(array $input): bool
    {
        if (empty($input['create_demo_data'])) {
            return false;
        }
        if (app()->isProduction() && ! (bool) config('platform.demo_allowed_in_prod', env('DEMO_ALLOWED_IN_PROD', false))) {
            return false;
        }

        return true;
    }

    private function seedDemoData(Tenant $tenant): void
    {
        $class = SchoolClass::query()->where('tenant_id', $tenant->id)->first()
            ?: SchoolClass::query()->create(['tenant_id' => $tenant->id, 'name' => 'JHS 1', 'section' => 'A']);

        $names = ['Kwame Mensah', 'Akosua Boateng', 'Yaw Owusu', 'Adwoa Asante', 'Kojo Annan'];
        foreach ($names as $i => $name) {
            \App\Models\Student::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => $name],
                [
                    'class_id' => $class->id,
                    'gender' => $i % 2 === 0 ? 'male' : 'female',
                    'status' => 'active',
                ]
            );
        }
    }

    /**
     * Seed demo teacher/accountant/parent users. ALL receive their own
     * unique random passwords; we never seed the legacy "password" string.
     */
    private function seedDemoUsers(Tenant $tenant): void
    {
        $roles = [UserRole::Teacher, UserRole::Accountant, UserRole::Parent];
        foreach ($roles as $role) {
            $email = strtolower($role->value).'.'.$tenant->subdomain.'@demo.local';
            User::query()->firstOrCreate(
                ['email' => $email],
                [
                    'tenant_id' => $tenant->id,
                    'name' => 'Demo '.$role->value,
                    'role' => $role->value,
                    'password' => $this->generateTempPassword(),
                    'force_password_reset' => true,
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * Generate a 16-character mixed-case alphanumeric temp password that is
     * unique per call. We deliberately exclude ambiguous characters (0/O,
     * 1/I/l) so a human can copy the credential off paper without errors.
     */
    private function generateTempPassword(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
        $out = '';
        $max = strlen($alphabet) - 1;
        for ($i = 0; $i < 16; $i++) {
            $out .= $alphabet[random_int(0, $max)];
        }

        return $out;
    }

    /**
     * Write the credentials to a non-public local file, owned by the
     * 'backups' disk so it sits next to backup files (already locked down).
     * Returns the relative path.
     */
    private function writeCredentialsFile(Tenant $tenant, User $admin, string $password): string
    {
        $disk = (string) config('backups.disk', 'local');
        $path = 'provisioning/'.$tenant->subdomain.'-'.now()->format('Ymd-His').'.txt';

        $body = "Tenant: {$tenant->name} ({$tenant->subdomain})\n"
            ."Admin email: {$admin->email}\n"
            ."Temporary password: {$password}\n"
            ."Generated at: ".now()->toIso8601String()."\n\n"
            ."IMPORTANT: the admin must reset this password on first login.\n"
            ."Delete this file once the credentials have been delivered.\n";

        Storage::disk($disk)->put($path, $body);

        return $path;
    }

    private function sendCredentialsEmail(User $admin, string $password, Tenant $tenant): void
    {
        try {
            Mail::raw(
                "Welcome to ".config('app.name').".\n\n"
                ."Your school '{$tenant->name}' has been provisioned.\n"
                ."Sign-in email: {$admin->email}\n"
                ."Temporary password: {$password}\n\n"
                ."You will be asked to set a new password on first sign-in.",
                function ($m) use ($admin, $tenant): void {
                    $m->to($admin->email)->subject("Your {$tenant->name} school account");
                }
            );
        } catch (\Throwable $e) {
            // Email failure must not break provisioning; the credentials file
            // is the source of truth.
            $this->logger->log(
                'tenant_provisioning_mail_failed',
                'Could not send provisioning email; credentials file remains the source of truth.',
                ['tenant_id' => $tenant->id, 'error' => $e->getMessage()],
                $tenant->id,
                Tenant::class,
                $tenant->id
            );
        }
    }
}
