<?php

namespace Database\Seeders;

use App\Enums\MessageRecipientType;
use App\Models\Message;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Runs after DemoDataSeeder and RolePortalSeeder so teachers, parents, and students are linked.
 */
class DemoMessagesSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->where('subdomain', 'demo')->first();
        if (! $tenant) {
            return;
        }

        $tid = (int) $tenant->id;

        $admin = User::query()->where('tenant_id', $tid)->where('email', 'admin@demo.com')->first();
        $proprietor = User::query()->where('tenant_id', $tid)->where('email', 'proprietor@demo.com')->first();
        $accountant = User::query()->where('tenant_id', $tid)->where('email', 'accountant@demo.com')->first();
        $teacher = User::query()->where('tenant_id', $tid)->where('email', 'teacher@demo.com')->first();
        $parent = User::query()->where('tenant_id', $tid)->where('email', 'parent@demo.com')->first();

        if (! $admin) {
            return;
        }

        $classKg1 = SchoolClass::query()->where('tenant_id', $tid)->where('name', 'KG1')->first();
        $classP1 = SchoolClass::query()->where('tenant_id', $tid)->where('name', 'Primary 1')->first();
        $classP2 = SchoolClass::query()->where('tenant_id', $tid)->where('name', 'Primary 2')->first();

        Message::query()->updateOrCreate(
            ['tenant_id' => $tid, 'channel' => 'demo_notice_all_parents'],
            [
                'sender_id' => $proprietor?->id ?? $admin->id,
                'title' => __('PTA & assembly'),
                'audience' => null,
                'recipient_type' => MessageRecipientType::AllParents->value,
                'recipient_id' => null,
                'school_class_id' => null,
                'notice_kind' => Message::CHANNEL_SCHOOL_NOTICE,
                'content' => __('PTA general meeting Saturday 10:00 at the hall. All parents are welcome.'),
                'status' => 'sent',
                'sent_at' => Carbon::now()->subDays(6),
            ]
        );

        Message::query()->updateOrCreate(
            ['tenant_id' => $tid, 'channel' => 'demo_notice_all_students'],
            [
                'sender_id' => $admin->id,
                'title' => __('Sports day'),
                'audience' => null,
                'recipient_type' => MessageRecipientType::AllStudents->value,
                'recipient_id' => null,
                'school_class_id' => null,
                'notice_kind' => Message::CHANNEL_SCHOOL_NOTICE,
                'content' => __('Inter-house athletics next month. PE kits required on Fridays.'),
                'status' => 'sent',
                'sent_at' => Carbon::now()->subDays(4),
            ]
        );

        Message::query()->updateOrCreate(
            ['tenant_id' => $tid, 'channel' => 'demo_notice_teachers'],
            [
                'sender_id' => $admin->id,
                'title' => __('Staff room'),
                'audience' => null,
                'recipient_type' => MessageRecipientType::Teachers->value,
                'recipient_id' => null,
                'school_class_id' => null,
                'notice_kind' => Message::CHANNEL_SCHOOL_NOTICE,
                'content' => __('Staff briefing moved to Wednesday 3:30 PM. Agenda: term reporting.'),
                'status' => 'sent',
                'sent_at' => Carbon::now()->subDays(3),
            ]
        );

        if ($teacher && $classP1) {
            Message::query()->updateOrCreate(
                ['tenant_id' => $tid, 'channel' => 'demo_class_parents_p1'],
                [
                    'sender_id' => $teacher->id,
                    'title' => __('Primary 1 homework'),
                    'audience' => null,
                    'recipient_type' => MessageRecipientType::ClassParents->value,
                    'recipient_id' => null,
                    'school_class_id' => $classP1->id,
                    'notice_kind' => Message::CHANNEL_CLASS_NOTICE,
                    'content' => __('Please supervise reading logs over the weekend. Thank you.'),
                    'status' => 'sent',
                    'sent_at' => Carbon::now()->subDays(2),
                ]
            );
        }

        if ($teacher && $classKg1) {
            Message::query()->updateOrCreate(
                ['tenant_id' => $tid, 'channel' => 'demo_class_students_kg1'],
                [
                    'sender_id' => $teacher->id,
                    'title' => __('KG1 reminder'),
                    'audience' => null,
                    'recipient_type' => MessageRecipientType::ClassStudents->value,
                    'recipient_id' => null,
                    'school_class_id' => $classKg1->id,
                    'notice_kind' => Message::CHANNEL_CLASS_NOTICE,
                    'content' => __('Bring crayons and a water bottle for art day on Tuesday.'),
                    'status' => 'sent',
                    'sent_at' => Carbon::now()->subDay(),
                ]
            );
        }

        if ($accountant) {
            Message::query()->updateOrCreate(
                ['tenant_id' => $tid, 'channel' => 'demo_fee_all_parents'],
                [
                    'sender_id' => $accountant->id,
                    'title' => __('Fee window'),
                    'audience' => null,
                    'recipient_type' => MessageRecipientType::AllParents->value,
                    'recipient_id' => null,
                    'school_class_id' => null,
                    'notice_kind' => Message::CHANNEL_FEE_REMINDER,
                    'content' => __('Term fees are due by the 15th. Use cash, MoMo, or bank transfer at the bursary.'),
                    'status' => 'sent',
                    'sent_at' => Carbon::now()->subDays(5),
                ]
            );

            if ($classP2) {
                Message::query()->updateOrCreate(
                    ['tenant_id' => $tid, 'channel' => 'demo_fee_class_p2'],
                    [
                        'sender_id' => $accountant->id,
                        'title' => __('Primary 2 balances'),
                        'audience' => null,
                        'recipient_type' => MessageRecipientType::ClassParents->value,
                        'recipient_id' => null,
                        'school_class_id' => $classP2->id,
                        'notice_kind' => Message::CHANNEL_FEE_REMINDER,
                        'content' => __('Some Primary 2 accounts still show partial payment. Kindly settle balances this week.'),
                        'status' => 'sent',
                        'sent_at' => Carbon::now()->subHours(20),
                    ]
                );
            }
        }

        if ($parent) {
            Message::query()->updateOrCreate(
                ['tenant_id' => $tid, 'channel' => 'demo_selected_parent'],
                [
                    'sender_id' => $admin->id,
                    'title' => __('Direct message'),
                    'audience' => null,
                    'recipient_type' => MessageRecipientType::SelectedParent->value,
                    'recipient_id' => $parent->id,
                    'school_class_id' => null,
                    'notice_kind' => Message::CHANNEL_SCHOOL_NOTICE,
                    'content' => __('Thank you for attending the last PTA session. Minutes are on the notice board.'),
                    'status' => 'sent',
                    'sent_at' => Carbon::now()->subHours(8),
                ]
            );
        }

        $student = Student::query()->where('tenant_id', $tid)->orderBy('id')->first();
        if ($student) {
            Message::query()->updateOrCreate(
                ['tenant_id' => $tid, 'channel' => 'demo_student_fee_notice'],
                [
                    'sender_id' => $accountant?->id ?? $admin->id,
                    'title' => __('Fee reminder'),
                    'audience' => null,
                    'recipient_type' => Student::class,
                    'recipient_id' => $student->id,
                    'school_class_id' => null,
                    'notice_kind' => Message::CHANNEL_FEE_REMINDER,
                    'content' => __('Fee reminder: kindly clear any outstanding balance at the bursary this week.'),
                    'status' => 'sent',
                    'sent_at' => Carbon::now()->subDays(3),
                ]
            );
        }

        Message::query()->updateOrCreate(
            ['tenant_id' => $tid, 'channel' => 'demo_all_users'],
            [
                'sender_id' => $admin->id,
                'title' => __('Public holiday'),
                'audience' => null,
                'recipient_type' => MessageRecipientType::AllUsers->value,
                'recipient_id' => null,
                'school_class_id' => null,
                'notice_kind' => Message::CHANNEL_SCHOOL_NOTICE,
                'content' => __('School will be closed next Monday for a national holiday.'),
                'status' => 'sent',
                'sent_at' => Carbon::now()->subHours(12),
            ]
        );

        $super = User::query()->whereNull('tenant_id')->where('email', 'superadmin@bossschool.com')->first();
        if ($super) {
            Message::query()->withoutGlobalScopes()->updateOrCreate(
                [
                    'tenant_id' => null,
                    'channel' => 'demo_platform_release',
                ],
                [
                    'sender_id' => $super->id,
                    'title' => __('BossSchool platform'),
                    'audience' => MessageRecipientType::PlatformTenants->label(),
                    'recipient_type' => MessageRecipientType::PlatformTenants->value,
                    'recipient_id' => null,
                    'school_class_id' => null,
                    'notice_kind' => Message::CHANNEL_PLATFORM,
                    'content' => __('Welcome to BossSchool. This is a one-way platform notice — replies are not enabled yet.'),
                    'status' => 'sent',
                    'sent_at' => Carbon::now()->subDay(),
                ]
            );
        }
    }
}
