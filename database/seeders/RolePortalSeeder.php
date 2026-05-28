<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class RolePortalSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->where('subdomain', 'demo')->firstOrFail();
        $tid = (int) $tenant->id;

        $portalStudent = Student::query()
            ->where('tenant_id', $tid)
            ->where('status', 'active')
            ->orderBy('id')
            ->first();

        if ($portalStudent === null) {
            $this->command?->warn('RolePortalSeeder: no active students for demo tenant; skipping portal users.');

            return;
        }

        $guardianLabel = (string) $portalStudent->parent_name;

        $childrenForParent = Student::query()
            ->where('tenant_id', $tid)
            ->where('parent_name', $guardianLabel)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        if ($childrenForParent->isEmpty()) {
            $childrenForParent = collect([$portalStudent]);
        }

        $parent = User::query()->updateOrCreate(
            [
                'tenant_id' => $tid,
                'email' => 'parent@demo.com',
            ],
            [
                'name' => $guardianLabel,
                'password' => 'password',
                'role' => UserRole::Parent->value,
                'email_verified_at' => now(),
            ]
        );

        $parent->children()->sync(
            $childrenForParent->mapWithKeys(fn (Student $s): array => [$s->id => ['tenant_id' => $tid]])->all()
        );

        User::query()->updateOrCreate(
            [
                'tenant_id' => $tid,
                'email' => 'student@demo.com',
            ],
            [
                'name' => $portalStudent->name,
                'password' => 'password',
                'role' => UserRole::Student->value,
                'student_id' => $portalStudent->id,
                'email_verified_at' => now(),
            ]
        );

        $teacher = User::query()->where('tenant_id', $tid)->where('email', 'teacher@demo.com')->first();
        if ($teacher) {
            $classIds = SchoolClass::query()->where('tenant_id', $tid)->orderBy('id')->take(2)->pluck('id');
            if ($classIds->isNotEmpty()) {
                $teacher->assignedClasses()->sync(
                    $classIds->mapWithKeys(fn (int $id): array => [$id => ['tenant_id' => $tid]])->all()
                );

                $subjectIds = Subject::query()
                    ->where('tenant_id', $tid)
                    ->whereIn('class_id', $classIds->all())
                    ->orderBy('id')
                    ->take(6)
                    ->pluck('id');

                if ($subjectIds->isNotEmpty()) {
                    $teacher->assignedSubjects()->sync(
                        $subjectIds->mapWithKeys(fn (int $id): array => [$id => ['tenant_id' => $tid]])->all()
                    );
                }
            }
        }
    }
}
