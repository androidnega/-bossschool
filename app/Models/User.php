<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use App\Models\Concerns\BelongsToTenant;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['tenant_id', 'student_id', 'name', 'email', 'password', 'role', 'is_active', 'force_password_reset', 'last_login_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use BelongsToTenant, HasApiTokens, HasFactory, Notifiable;

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'parent_student', 'user_id', 'student_id')
            ->withPivot('tenant_id')
            ->withTimestamps();
    }

    public function assignedClasses(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'teacher_class', 'user_id', 'class_id')
            ->withPivot('tenant_id')
            ->withTimestamps();
    }

    public function assignedSubjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'teacher_subject', 'user_id', 'subject_id')
            ->withPivot('tenant_id')
            ->withTimestamps();
    }

    public function hasRole(UserRole ...$roles): bool
    {
        foreach ($roles as $role) {
            if ($this->role === $role->value) {
                return true;
            }
        }

        return false;
    }

    public function roleEnum(): ?UserRole
    {
        return UserRole::tryFrom((string) $this->role);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin->value;
    }

    public function isSchoolStaff(): bool
    {
        return in_array($this->role, [
            UserRole::Admin->value,
            UserRole::Proprietor->value,
            UserRole::Accountant->value,
            UserRole::Teacher->value,
        ], true);
    }

    public function isFinanceRole(): bool
    {
        return in_array($this->role, [
            UserRole::Admin->value,
            UserRole::Proprietor->value,
            UserRole::Accountant->value,
        ], true);
    }

    public function homeRoute(): string
    {
        return match ($this->role) {
            UserRole::Parent->value => route('portal.parent.index'),
            UserRole::Student->value => route('portal.student.index'),
            null, '' => route('login'),
            default => route('dashboard'),
        };
    }

    public function sidebarKey(): string
    {
        return match ($this->role) {
            UserRole::SuperAdmin->value => 'superadmin',
            UserRole::Proprietor->value => 'proprietor',
            UserRole::Admin->value => 'admin',
            UserRole::Accountant->value => 'accountant',
            UserRole::Teacher->value => 'teacher',
            UserRole::Parent->value => 'parent',
            UserRole::Student->value => 'student',
            default => 'admin',
        };
    }

    public function teachesStudent(Student $student): bool
    {
        if ($this->role !== UserRole::Teacher->value) {
            return false;
        }

        return $this->assignedClasses()->where('classes.id', $student->class_id)->exists();
    }

    public function teachesSubjectId(int $subjectId): bool
    {
        if ($this->role !== UserRole::Teacher->value) {
            return false;
        }

        return $this->assignedSubjects()->where('subjects.id', $subjectId)->exists();
    }

    public function isGuardianOf(Student $student): bool
    {
        if ($this->role !== UserRole::Parent->value) {
            return false;
        }

        return $this->children()->where('students.id', $student->id)->exists();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions', 'user_id', 'permission_id')->withTimestamps();
    }

    public function canPerform(string $key): bool
    {
        return app(\App\Services\PermissionService::class)->userCan($this, $key);
    }

    public function linkedStudentIds(): array
    {
        if ($this->role === UserRole::Student->value && $this->student_id) {
            return [(int) $this->student_id];
        }

        if ($this->role === UserRole::Parent->value) {
            return $this->children()->pluck('students.id')->map(fn ($id): int => (int) $id)->all();
        }

        return [];
    }

    /**
     * Tenant-scoped credential lookup for web / API login.
     *
     * Behavior:
     * - $tenantId int (host has a tenant subdomain): match only users with
     *   that tenant_id, plus SuperAdmin. If both match, the tenant user wins
     *   so a tenant Admin sharing an email with a SuperAdmin still ends up
     *   in the right tenant.
     * - $tenantId null + production-like environment: only SuperAdmin users
     *   (tenant_id null) may sign in. Tenant users MUST use their subdomain;
     *   this is the explicit, audited fix for cross-tenant email collisions.
     * - $tenantId null + local/testing environment: allow any single match
     *   so the dev workflow (and the test suite) keeps working on bare
     *   localhost without subdomains. If multiple users share the same
     *   email + password across tenants we refuse to guess and return null.
     */
    public static function findForCredentials(string $email, string $plainPassword, ?int $tenantId = null): ?self
    {
        $query = static::withoutGlobalScopes()->where('email', $email);

        $isProduction = ! in_array(app()->environment(), ['local', 'testing'], true);

        if ($tenantId === null && $isProduction) {
            $query->whereNull('tenant_id');
        } elseif ($tenantId !== null) {
            $query->where(function ($q) use ($tenantId): void {
                $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
            });
        }

        $candidates = $query->get();

        $matches = $candidates->filter(fn (self $u): bool => Hash::check($plainPassword, $u->password))->values();

        if ($matches->isEmpty()) {
            return null;
        }

        if ($matches->count() === 1) {
            return $matches->first();
        }

        if ($tenantId !== null) {
            $tenantMatch = $matches->first(fn (self $u): bool => (int) $u->tenant_id === (int) $tenantId);
            if ($tenantMatch !== null) {
                return $tenantMatch;
            }

            return $matches->first();
        }

        // Bare host with multiple candidates across tenants: refuse to guess.
        return null;
    }

    protected static function booted(): void
    {
        static::deleting(function (User $user): bool {
            $actor = auth()->user();
            if ($actor !== null && (int) $actor->id === (int) $user->id && $user->isSuperAdmin()) {
                return false;
            }

            return true;
        });
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'force_password_reset' => 'boolean',
            'password' => 'hashed',
        ];
    }
}
