<?php

/*
| `/` → public BossSchool homepage (guest or signed-in CTA). `/dashboard` redirects by role.
| School routes use `tenant` middleware. Platform routes use SuperAdmin only (no tenant).
*/

use App\Http\Controllers\HomeRedirectController;
use App\Http\Controllers\Platform\ActivityLogController;
use App\Http\Controllers\Platform\DashboardController as PlatformDashboardController;
use App\Http\Controllers\Platform\FeatureToggleController;
use App\Http\Controllers\Platform\MaintenanceController as PlatformMaintenanceController;
use App\Http\Controllers\Platform\NoticeController as PlatformNoticeController;
use App\Http\Controllers\Platform\PlanController;
use App\Http\Controllers\Platform\PaystackSettingsController;
use App\Http\Controllers\Platform\PlatformSettingsController;
use App\Http\Controllers\Platform\PlatformUserController;
use App\Http\Controllers\Platform\ResetController as PlatformResetController;
use App\Http\Controllers\Platform\SubscriptionController as PlatformSubscriptionController;
use App\Http\Controllers\Platform\TenantAcademicController;
use App\Http\Controllers\Platform\TenantAttendanceController;
use App\Http\Controllers\Platform\TenantControlController;
use App\Http\Controllers\Platform\TenantController as PlatformTenantController;
use App\Http\Controllers\Platform\TenantFinanceController;
use App\Http\Controllers\Platform\TenantMessageController;
use App\Http\Controllers\Platform\TenantSettingsController;
use App\Http\Controllers\Platform\TenantStaffController;
use App\Http\Controllers\Platform\TenantStudentController;
use App\Http\Controllers\Platform\TenantSubscriptionController;
use App\Http\Controllers\Platform\TenantUserController;
use App\Http\Controllers\Web\AcademicYearController;
use App\Http\Controllers\Web\AttendanceController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\BillingController;
use App\Http\Controllers\Web\PasswordResetController;
use App\Http\Controllers\Web\Dashboards\AccountantDashboardController;
use App\Http\Controllers\Web\Dashboards\AdminDashboardController;
use App\Http\Controllers\Web\Dashboards\ProprietorDashboardController;
use App\Http\Controllers\Web\Dashboards\TeacherDashboardController;
use App\Http\Controllers\Web\DebtorsController;
use App\Http\Controllers\Web\FeeController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\MessageController;
use App\Http\Controllers\Web\PaymentController;
use App\Http\Controllers\Web\PromotionController;
use App\Http\Controllers\Web\Portal\ParentPortalController;
use App\Http\Controllers\Web\Portal\StudentPortalController;
use App\Http\Controllers\Web\ReportCardController;
use App\Http\Controllers\Web\ReportCardMetaController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\ResultController;
use App\Http\Controllers\Web\SchoolClassController;
use App\Http\Controllers\Web\SettingsController;
use App\Http\Controllers\Web\StaffController;
use App\Http\Controllers\Web\StudentController;
use App\Http\Controllers\Web\SubjectController;
use App\Http\Controllers\Web\TermController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/health', [\App\Http\Controllers\HealthController::class, 'simple'])->name('health');

// First-run SuperAdmin bootstrap. Self-locks once a SuperAdmin exists; the
// controller redirects to /login when it shouldn't run.
Route::get('/setup/superadmin', [\App\Http\Controllers\Web\SuperAdminSetupController::class, 'show'])
    ->name('superadmin.setup.show');
Route::post('/setup/superadmin', [\App\Http\Controllers\Web\SuperAdminSetupController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('superadmin.setup.store');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'show'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

    Route::get('/password/forgot', [PasswordResetController::class, 'showLinkRequest'])->name('password.request');
    Route::post('/password/forgot', [PasswordResetController::class, 'sendResetLink'])
        ->middleware('throttle:password-reset')
        ->name('password.email');
    Route::get('/password/reset/{token}', [PasswordResetController::class, 'showReset'])->name('password.reset');
    Route::post('/password/reset', [PasswordResetController::class, 'reset'])
        ->middleware('throttle:password-reset')
        ->name('password.update');

    Route::get('/two-factor/challenge', [\App\Http\Controllers\Web\TwoFactorChallengeController::class, 'show'])->name('two-factor.challenge.show');
    Route::post('/two-factor/challenge', [\App\Http\Controllers\Web\TwoFactorChallengeController::class, 'attempt'])
        ->middleware('throttle:login')->name('two-factor.challenge.attempt');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/two-factor', [\App\Http\Controllers\Web\TwoFactorController::class, 'show'])->name('two-factor.show');
    Route::post('/two-factor/enable', [\App\Http\Controllers\Web\TwoFactorController::class, 'enable'])->name('two-factor.enable');
    Route::get('/two-factor/enable',  [\App\Http\Controllers\Web\TwoFactorController::class, 'enableSetup'])->name('two-factor.enable.show');
    Route::post('/two-factor/confirm', [\App\Http\Controllers\Web\TwoFactorController::class, 'confirm'])->name('two-factor.confirm');
    Route::post('/two-factor/disable', [\App\Http\Controllers\Web\TwoFactorController::class, 'disable'])->name('two-factor.disable');

    // Friendly fallbacks: GET /two-factor/confirm or /two-factor/disable (a
    // bookmarked URL or POST refresh) gracefully redirects to the 2FA hub
    // instead of 405-ing.
    Route::get('/two-factor/confirm', [\App\Http\Controllers\Web\TwoFactorController::class, 'redirectToShow']);
    Route::get('/two-factor/disable', [\App\Http\Controllers\Web\TwoFactorController::class, 'redirectToShow']);
});

Route::middleware(['auth', 'maintenance'])->group(function (): void {
    Route::get('/dashboard', HomeRedirectController::class)->name('dashboard');
});

Route::middleware(['auth', 'role:SuperAdmin'])->prefix('platform')->name('platform.')->group(function (): void {
    Route::get('/dashboard', PlatformDashboardController::class)->name('dashboard');

    Route::get('/settings', [PlatformSettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [PlatformSettingsController::class, 'update'])->name('settings.update');

    Route::get('/payments/settings', [PaystackSettingsController::class, 'index'])->name('payments.settings.index');
    Route::put('/payments/settings', [PaystackSettingsController::class, 'update'])->name('payments.settings.update');

    Route::get('/plans', [PlanController::class, 'index'])->name('plans.index');
    Route::get('/plans/create', [PlanController::class, 'create'])->name('plans.create');
    Route::post('/plans', [PlanController::class, 'store'])->name('plans.store');
    Route::get('/plans/{plan}/edit', [PlanController::class, 'edit'])->name('plans.edit');
    Route::put('/plans/{plan}', [PlanController::class, 'update'])->name('plans.update');
    Route::post('/plans/{plan}/disable', [PlanController::class, 'disable'])->name('plans.disable');
    Route::delete('/plans/{plan}', [PlanController::class, 'destroy'])->name('plans.destroy');

    Route::get('/subscriptions', PlatformSubscriptionController::class)->name('subscriptions');
    Route::get('/feature-toggles', [FeatureToggleController::class, 'index'])->name('feature-toggles.index');
    Route::put('/feature-toggles', [FeatureToggleController::class, 'update'])->name('feature-toggles.update');

    Route::get('/maintenance', [PlatformMaintenanceController::class, 'index'])->name('maintenance.index');
    Route::put('/maintenance', [PlatformMaintenanceController::class, 'update'])->name('maintenance.update');
    Route::post('/maintenance/enable', [PlatformMaintenanceController::class, 'enable'])->name('maintenance.enable');
    Route::post('/maintenance/disable', [PlatformMaintenanceController::class, 'disable'])->name('maintenance.disable');

    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

    Route::get('/support', [\App\Http\Controllers\Platform\SupportInboxController::class, 'index'])->name('support.index');
    Route::get('/support/{ticket}', [\App\Http\Controllers\Platform\SupportInboxController::class, 'show'])->name('support.show');

    Route::get('/errors', [\App\Http\Controllers\Platform\ApplicationErrorsController::class, 'index'])->name('errors.index');

    Route::get('/users', PlatformUserController::class)->name('users');
    Route::get('/tenants', [PlatformTenantController::class, 'index'])->name('tenants.index');
    Route::get('/tenants/create', [PlatformTenantController::class, 'create'])->name('tenants.create');
    Route::post('/tenants', [PlatformTenantController::class, 'store'])->name('tenants.store');
    Route::get('/tenants/{tenant}', [TenantControlController::class, 'show'])->name('tenants.show');
    Route::post('/tenants/{tenant}/suspend', [PlatformTenantController::class, 'suspend'])->name('tenants.suspend');
    Route::post('/tenants/{tenant}/activate', [PlatformTenantController::class, 'activate'])->name('tenants.activate');
    Route::delete('/tenants/{tenant}', [PlatformTenantController::class, 'destroy'])->name('tenants.destroy');
    Route::post('/tenants/{tenant}/maintenance/enable', [PlatformTenantController::class, 'enableMaintenance'])->name('tenants.maintenance.enable');
    Route::post('/tenants/{tenant}/maintenance/disable', [PlatformTenantController::class, 'disableMaintenance'])->name('tenants.maintenance.disable');

    Route::prefix('tenants/{tenant}')->scopeBindings()->group(function (): void {
        Route::get('/users', [TenantUserController::class, 'index'])->name('tenants.users.index');
        Route::get('/users/create', [TenantUserController::class, 'create'])->name('tenants.users.create');
        Route::post('/users', [TenantUserController::class, 'store'])->name('tenants.users.store');
        Route::get('/users/{user}/edit', [TenantUserController::class, 'edit'])->name('tenants.users.edit');
        Route::put('/users/{user}', [TenantUserController::class, 'update'])->name('tenants.users.update');
        Route::delete('/users/{user}', [TenantUserController::class, 'destroy'])->name('tenants.users.destroy');

        Route::get('/students', [TenantStudentController::class, 'index'])->name('tenants.students.index');
        Route::get('/students/{student}', [TenantStudentController::class, 'show'])->name('tenants.students.show');

        Route::get('/staff', [TenantStaffController::class, 'index'])->name('tenants.staff.index');

        Route::get('/finance', [TenantFinanceController::class, 'index'])->name('tenants.finance.index');
        Route::get('/academics', [TenantAcademicController::class, 'index'])->name('tenants.academics.index');
        Route::get('/attendance', [TenantAttendanceController::class, 'index'])->name('tenants.attendance.index');
        Route::get('/messages', [TenantMessageController::class, 'index'])->name('tenants.messages.index');

        Route::get('/subscription', [TenantSubscriptionController::class, 'index'])->name('tenants.subscription.index');
        Route::put('/subscription', [TenantSubscriptionController::class, 'update'])->name('tenants.subscription.update');
        Route::post('/subscription/extend', [TenantSubscriptionController::class, 'extend'])->name('tenants.subscription.extend');
        Route::post('/subscription/suspend', [TenantSubscriptionController::class, 'suspend'])->name('tenants.subscription.suspend');
        Route::post('/subscription/activate', [TenantSubscriptionController::class, 'activate'])->name('tenants.subscription.activate');

        Route::get('/settings', [TenantSettingsController::class, 'index'])->name('tenants.settings.index');
        Route::put('/settings', [TenantSettingsController::class, 'update'])->name('tenants.settings.update');
    });

    Route::get('/reset', [PlatformResetController::class, 'index'])->name('reset.index');
    Route::post('/reset/tenant', [PlatformResetController::class, 'resetTenant'])->name('reset.tenant');
    Route::post('/reset/all-school-data', [PlatformResetController::class, 'resetAll'])->name('reset.all');

    Route::get('/backups', [\App\Http\Controllers\Platform\TenantBackupController::class, 'index'])->name('backups.index');
    Route::post('/backups', [\App\Http\Controllers\Platform\TenantBackupController::class, 'store'])->name('backups.store');
    Route::get('/backups/{backup}', [\App\Http\Controllers\Platform\TenantBackupController::class, 'show'])->name('backups.show');
    Route::get('/backups/{backup}/download', [\App\Http\Controllers\Platform\TenantBackupController::class, 'download'])->name('backups.download');
    Route::post('/backups/{backup}/restore', [\App\Http\Controllers\Platform\TenantBackupController::class, 'restore'])->name('backups.restore');

    Route::get('/health', [\App\Http\Controllers\Platform\HealthController::class, 'detailed'])->name('health.detailed');
    Route::get('/production-checklist', [\App\Http\Controllers\Platform\ProductionChecklistController::class, 'index'])->name('production-checklist.index');

    Route::get('/notices', [PlatformNoticeController::class, 'index'])->name('notices.index');
    Route::post('/notices', [PlatformNoticeController::class, 'store'])->name('notices.store');
});

Route::middleware(['auth', 'maintenance', 'tenant', 'role:Proprietor'])->group(function (): void {
    Route::get('/dashboard/proprietor', ProprietorDashboardController::class)->name('dashboard.proprietor');
});

Route::middleware(['auth', 'maintenance', 'tenant', 'role:Admin'])->group(function (): void {
    Route::get('/dashboard/admin', AdminDashboardController::class)->name('dashboard.admin');
});

Route::middleware(['auth', 'maintenance', 'tenant', 'role:Accountant'])->group(function (): void {
    Route::get('/dashboard/accountant', AccountantDashboardController::class)->name('dashboard.accountant');
});

Route::middleware(['auth', 'maintenance', 'tenant', 'role:Teacher'])->group(function (): void {
    Route::get('/dashboard/teacher', TeacherDashboardController::class)->name('dashboard.teacher');
});

Route::middleware(['auth', 'maintenance', 'feature:parent_portal', 'tenant', 'role:Parent'])->prefix('portal/parent')->name('portal.parent.')->group(function (): void {
    Route::get('/', [ParentPortalController::class, 'index'])->name('index');
    Route::get('/children/{student}', [ParentPortalController::class, 'child'])->name('child');
    Route::get('/children/{student}/statement', [\App\Http\Controllers\Web\FeeStatementController::class, 'parent'])->name('child.statement');
    Route::get('/children/{student}/statement.pdf', [\App\Http\Controllers\Web\FeeStatementController::class, 'parentPdf'])->name('child.statement.pdf');
});

Route::middleware(['auth', 'maintenance', 'feature:parent_portal', 'feature:report_cards', 'tenant', 'role:Parent'])->prefix('portal/parent')->name('portal.parent.')->group(function (): void {
    Route::get('/children/{student}/report-card', [ParentPortalController::class, 'reportCard'])->name('child.report-card');
    Route::get('/children/{student}/report-card.pdf', [ParentPortalController::class, 'reportCardPdf'])->name('child.report-card.pdf');
});

Route::middleware(['auth', 'maintenance', 'feature:student_portal', 'tenant', 'role:Student'])->prefix('portal/student')->name('portal.student.')->group(function (): void {
    Route::get('/', [StudentPortalController::class, 'index'])->name('index');
    Route::get('/statement', [\App\Http\Controllers\Web\FeeStatementController::class, 'student'])->name('statement');
    Route::get('/statement.pdf', [\App\Http\Controllers\Web\FeeStatementController::class, 'studentPdf'])->name('statement.pdf');
});

Route::middleware(['auth', 'maintenance', 'feature:student_portal', 'feature:report_cards', 'tenant', 'role:Student'])->prefix('portal/student')->name('portal.student.')->group(function (): void {
    Route::get('/report-card', [StudentPortalController::class, 'reportCard'])->name('report-card');
    Route::get('/report-card.pdf', [StudentPortalController::class, 'reportCardPdf'])->name('report-card.pdf');
});

// Support tickets — open to school staff roles AND SuperAdmin (who replies
// to tickets from the platform inbox via the same handler). Policy enforces
// per-ticket visibility so non-admins only see their own tickets.
Route::middleware(['auth', 'maintenance', 'role:SuperAdmin,Admin,Proprietor,Headteacher,Accountant,Teacher'])->group(function (): void {
    Route::get('/support/tickets', [\App\Http\Controllers\Web\SupportTicketController::class, 'index'])->name('support.index');
    Route::get('/support/tickets/create', [\App\Http\Controllers\Web\SupportTicketController::class, 'create'])->name('support.create');
    Route::post('/support/tickets', [\App\Http\Controllers\Web\SupportTicketController::class, 'store'])->name('support.store');
    Route::get('/support/tickets/{ticket}', [\App\Http\Controllers\Web\SupportTicketController::class, 'show'])->name('support.show');
    Route::post('/support/tickets/{ticket}/reply', [\App\Http\Controllers\Web\SupportTicketController::class, 'reply'])->name('support.reply');
    Route::post('/support/tickets/{ticket}/status', [\App\Http\Controllers\Web\SupportTicketController::class, 'changeStatus'])->name('support.change-status');
    Route::get('/support/attachments/{attachment}', [\App\Http\Controllers\Web\SupportTicketController::class, 'downloadAttachment'])->name('support.attachment.download');
});

Route::middleware(['auth', 'maintenance', 'feature:report_cards', 'tenant', 'role:Admin,Proprietor,Accountant,Teacher'])->group(function (): void {
    Route::get('students/{student}/report-card', [ReportCardController::class, 'show'])->name('students.report-card');
    Route::get('students/{student}/report-card.pdf', [ReportCardController::class, 'downloadPdf'])->name('students.report-card.pdf');
    Route::get('classes/{schoolClass}/report-cards.pdf', [ReportCardController::class, 'bulkPdf'])->name('classes.report-cards.pdf');
});

Route::middleware(['auth', 'maintenance', 'tenant', 'role:Admin,Proprietor,Teacher'])->group(function (): void {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/{schoolClass}/mark', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/attendance/{schoolClass}', [AttendanceController::class, 'store'])->name('attendance.store');

    Route::get('/report-card-meta', [ReportCardMetaController::class, 'index'])->name('report-card-meta.index');
    Route::get('/report-card-meta/bulk', [\App\Http\Controllers\Web\ReportCardBulkController::class, 'edit'])->name('report-card-meta.bulk.edit');
    Route::post('/report-card-meta/bulk', [\App\Http\Controllers\Web\ReportCardBulkController::class, 'update'])->name('report-card-meta.bulk.update');
    Route::get('/report-card-meta/{student}/edit', [ReportCardMetaController::class, 'edit'])->name('report-card-meta.edit');
    Route::put('/report-card-meta/{student}', [ReportCardMetaController::class, 'update'])->name('report-card-meta.update');
});

Route::middleware(['auth', 'maintenance', 'tenant', 'role:Admin,Proprietor,Accountant,Teacher'])->group(function (): void {
    Route::resource('students', StudentController::class);

    Route::resource('subjects', SubjectController::class)->except(['show']);
    Route::resource('results', ResultController::class)->except(['show']);

    Route::resource('fees', FeeController::class)->except(['show']);
    Route::post('/payments/{payment}/reverse', [PaymentController::class, 'reverse'])->name('payments.reverse');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
    Route::get('/debtors', [DebtorsController::class, 'index'])->name('debtors.index');

    Route::post('/fee-invoices', [\App\Http\Controllers\Web\FeeInvoiceController::class, 'store'])->name('fee-invoices.store');
    Route::get('/fee-invoices/create', [\App\Http\Controllers\Web\FeeInvoiceController::class, 'create'])->name('fee-invoices.create');
    Route::put('/fee-invoices/{feeInvoice}', [\App\Http\Controllers\Web\FeeInvoiceController::class, 'update'])->name('fee-invoices.update');
    Route::delete('/fee-invoices/{feeInvoice}', [\App\Http\Controllers\Web\FeeInvoiceController::class, 'destroy'])->name('fee-invoices.destroy');
    Route::post('/fee-invoices/{feeInvoice}/issue', [\App\Http\Controllers\Web\FeeInvoiceController::class, 'issue'])->name('fee-invoices.issue');
    Route::post('/fee-invoices/{feeInvoice}/cancel', [\App\Http\Controllers\Web\FeeInvoiceController::class, 'cancel'])->name('fee-invoices.cancel');

    Route::post('/fee-invoices/{feeInvoice}/items', [\App\Http\Controllers\Web\FeeInvoiceItemController::class, 'store'])->name('fee-invoice-items.store');
    Route::put('/fee-invoices/{feeInvoice}/items/{item}', [\App\Http\Controllers\Web\FeeInvoiceItemController::class, 'update'])->name('fee-invoice-items.update');
    Route::delete('/fee-invoices/{feeInvoice}/items/{item}', [\App\Http\Controllers\Web\FeeInvoiceItemController::class, 'destroy'])->name('fee-invoice-items.destroy');

    Route::resource('fee-adjustments', \App\Http\Controllers\Web\FeeAdjustmentController::class)
        ->parameters(['fee-adjustments' => 'feeAdjustment'])
        ->only(['index', 'create', 'store', 'destroy']);
    Route::post('/fee-adjustments/{feeAdjustment}/approve', [\App\Http\Controllers\Web\FeeAdjustmentController::class, 'approve'])->name('fee-adjustments.approve');
    Route::post('/fee-adjustments/{feeAdjustment}/reject', [\App\Http\Controllers\Web\FeeAdjustmentController::class, 'reject'])->name('fee-adjustments.reject');

    Route::get('/communication-logs', [\App\Http\Controllers\Web\CommunicationLogController::class, 'index'])->name('communication-logs.index');

    Route::get('/imports', [\App\Http\Controllers\Web\BulkImportExportController::class, 'index'])->name('imports.index');
    Route::get('/imports/template/{kind}', [\App\Http\Controllers\Web\BulkImportExportController::class, 'template'])->name('imports.template');
    Route::post('/imports/{kind}', [\App\Http\Controllers\Web\BulkImportExportController::class, 'import'])->name('imports.import');
    Route::get('/exports/{kind}', [\App\Http\Controllers\Web\BulkImportExportController::class, 'export'])->name('exports.kind');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/finance', [ReportController::class, 'finance'])->name('reports.finance');
    Route::get('/reports/students', [ReportController::class, 'students'])->name('reports.students');
    Route::get('/reports/academic', [ReportController::class, 'academic'])->name('reports.academic');

    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::get('/billing/plans', [BillingController::class, 'plans'])->name('billing.plans');
    Route::post('/billing/subscribe/{plan}', [BillingController::class, 'subscribe'])->name('billing.subscribe');
    Route::post('/billing/subscribe/{plan}/paystack', [BillingController::class, 'subscribeWithPaystack'])->name('billing.subscribe.paystack');
    Route::get('/billing/history', [BillingController::class, 'history'])->name('billing.history');

    // SMS credits (Paystack top-up)
    Route::get('/billing/sms-credits', [\App\Http\Controllers\Web\SmsCreditController::class, 'index'])->name('billing.sms-credits.index');
    Route::post('/billing/sms-credits/buy', [\App\Http\Controllers\Web\SmsCreditController::class, 'purchase'])->name('billing.sms-credits.purchase');

    // Synchronous Paystack callback (single endpoint for both purposes)
    Route::get('/payments/callback/paystack', [\App\Http\Controllers\Web\PaystackCallbackController::class, 'handle'])
        ->name('billing.paystack.callback');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/school', [SettingsController::class, 'updateSchool'])->name('settings.school.update');
    Route::get('/settings/tenant', [\App\Http\Controllers\Web\TenantSettingsController::class, 'index'])->name('settings.tenant');
    Route::put('/settings/tenant', [\App\Http\Controllers\Web\TenantSettingsController::class, 'update'])->name('settings.tenant.update');

    Route::get('/classes', [SchoolClassController::class, 'index'])->name('classes.index');
    Route::post('/classes', [SchoolClassController::class, 'store'])->name('classes.store');
    Route::put('/classes/{schoolClass}', [SchoolClassController::class, 'update'])->name('classes.update');
    Route::delete('/classes/{schoolClass}', [SchoolClassController::class, 'destroy'])->name('classes.destroy');

    Route::get('/terms', [TermController::class, 'index'])->name('terms.index');
    Route::post('/terms', [TermController::class, 'store'])->name('terms.store');
    Route::put('/terms/{term}', [TermController::class, 'update'])->name('terms.update');
    Route::delete('/terms/{term}', [TermController::class, 'destroy'])->name('terms.destroy');
    Route::post('/terms/{term}/set-current', [TermController::class, 'setCurrent'])->name('terms.set-current');
});

Route::middleware(['auth', 'maintenance', 'feature:messaging', 'tenant', 'role:Admin,Proprietor,Accountant,Teacher'])->group(function (): void {
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
});

/*
| Discipline incidents
| - Admin/Proprietor/Teacher can record incidents (teacher only for assigned students).
| - Parent can view incidents for linked children when the tenant setting allows it.
*/
Route::middleware(['auth', 'maintenance', 'tenant', 'role:Admin,Proprietor,Teacher'])->group(function (): void {
    Route::get('/discipline/create', [\App\Http\Controllers\Web\DisciplineIncidentController::class, 'create'])->name('discipline.create');
    Route::post('/discipline', [\App\Http\Controllers\Web\DisciplineIncidentController::class, 'store'])->name('discipline.store');
    Route::put('/discipline/{discipline_incident}', [\App\Http\Controllers\Web\DisciplineIncidentController::class, 'update'])->name('discipline.update');
});

Route::middleware(['auth', 'maintenance', 'tenant'])->group(function (): void {
    Route::get('/discipline', [\App\Http\Controllers\Web\DisciplineIncidentController::class, 'index'])->name('discipline.index');
    Route::get('/discipline/{discipline_incident}', [\App\Http\Controllers\Web\DisciplineIncidentController::class, 'show'])->name('discipline.show');
});

/*
| Finance read-only views (invoice, payment, receipt). The Policy decides who
| sees what — parents see only their children, students see only themselves,
| Admin/Proprietor/Accountant see everything in their tenant. Teachers are
| denied via policy.
*/
Route::middleware(['auth', 'maintenance', 'tenant'])->group(function (): void {
    Route::get('/fee-invoices', [\App\Http\Controllers\Web\FeeInvoiceController::class, 'index'])->name('fee-invoices.index');
    Route::get('/fee-invoices/{feeInvoice}', [\App\Http\Controllers\Web\FeeInvoiceController::class, 'show'])->name('fee-invoices.show');
    Route::get('/fee-invoices/{feeInvoice}/pdf', [\App\Http\Controllers\Web\FeeInvoiceController::class, 'pdf'])->name('fee-invoices.pdf');

    Route::post('/fee-invoices/{feeInvoice}/pay-online', [\App\Http\Controllers\Web\PaymentTransactionController::class, 'initiate'])->name('fee-invoices.pay-online');

    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::get('/payments/{payment}/receipt.pdf', [\App\Http\Controllers\Web\ReceiptController::class, 'pdf'])->name('payments.receipt.pdf');
});

Route::middleware(['auth', 'maintenance', 'tenant', 'role:Admin,Proprietor,Accountant'])->group(function (): void {
    Route::get('/payment-transactions', [\App\Http\Controllers\Web\PaymentTransactionController::class, 'index'])->name('payment-transactions.index');
});

/*
| Library — books are listed for everyone in the school; loans are filtered
| by role inside the controller. Admin/Proprietor can write new books/loans
| via the Policy. Returns are also Admin/Proprietor.
*/
Route::middleware(['auth', 'maintenance', 'tenant'])->group(function (): void {
    Route::get('/library/books', [\App\Http\Controllers\Web\LibraryController::class, 'books'])->name('library.books');
    Route::post('/library/books', [\App\Http\Controllers\Web\LibraryController::class, 'storeBook'])->name('library.books.store');
    Route::get('/library/loans', [\App\Http\Controllers\Web\LibraryController::class, 'loans'])->name('library.loans');
    Route::post('/library/loans', [\App\Http\Controllers\Web\LibraryController::class, 'storeLoan'])->name('library.loans.store');
    Route::post('/library/loans/{loan}/return', [\App\Http\Controllers\Web\LibraryController::class, 'returnLoan'])->name('library.loans.return');
});

/*
| Inventory — Admin/Proprietor manage items and movements. Accountant can
| view (low-stock / valuation) but not adjust.
*/
Route::middleware(['auth', 'maintenance', 'tenant', 'role:Admin,Proprietor,Accountant'])->group(function (): void {
    Route::get('/inventory/items', [\App\Http\Controllers\Web\InventoryController::class, 'items'])->name('inventory.items');
    Route::get('/inventory/low-stock', [\App\Http\Controllers\Web\InventoryController::class, 'lowStock'])->name('inventory.low-stock');
    Route::get('/inventory/movements', [\App\Http\Controllers\Web\InventoryController::class, 'movements'])->name('inventory.movements');
});

Route::middleware(['auth', 'maintenance', 'tenant', 'role:Admin,Proprietor'])->group(function (): void {
    Route::post('/inventory/items', [\App\Http\Controllers\Web\InventoryController::class, 'storeItem'])->name('inventory.items.store');
    Route::post('/inventory/movements', [\App\Http\Controllers\Web\InventoryController::class, 'storeMovement'])->name('inventory.movements.store');
});

Route::middleware(['auth', 'maintenance', 'tenant', 'role:Admin,Proprietor,Accountant'])->group(function (): void {
    Route::get('/students/{student}/statement', [\App\Http\Controllers\Web\FeeStatementController::class, 'show'])->name('students.statement');
    Route::get('/students/{student}/statement.pdf', [\App\Http\Controllers\Web\FeeStatementController::class, 'showPdf'])->name('students.statement.pdf');
});


Route::middleware(['auth', 'maintenance', 'tenant', 'role:Teacher'])->group(function (): void {
    Route::get('/my-assignments', [\App\Http\Controllers\Web\MyAssignmentsController::class, 'index'])->name('staff.assignments.mine');
});

Route::middleware(['auth', 'maintenance', 'tenant', 'role:Admin,Proprietor'])->group(function (): void {
    Route::get('/backups', [\App\Http\Controllers\Web\BackupController::class, 'index'])->name('backups.index');
    Route::post('/backups', [\App\Http\Controllers\Web\BackupController::class, 'store'])->name('backups.store');
    Route::get('/backups/{backup}/download', [\App\Http\Controllers\Web\BackupController::class, 'download'])->name('backups.download');

    Route::get('/onboarding', [\App\Http\Controllers\Web\OnboardingChecklistController::class, 'index'])->name('onboarding.index');

    Route::get('/onboarding/wizard', [\App\Http\Controllers\Web\OnboardingWizardController::class, 'index'])->name('onboarding.wizard.index');
    Route::get('/onboarding/wizard/{step}', [\App\Http\Controllers\Web\OnboardingWizardController::class, 'show'])->whereNumber('step')->name('onboarding.wizard.show');
    Route::post('/onboarding/wizard/{step}/mark', [\App\Http\Controllers\Web\OnboardingWizardController::class, 'markStep'])->whereNumber('step')->name('onboarding.wizard.mark');
    Route::post('/onboarding/wizard/finish', [\App\Http\Controllers\Web\OnboardingWizardController::class, 'finish'])->name('onboarding.wizard.finish');

    Route::get('/user-access-review', [\App\Http\Controllers\Web\UserAccessReviewController::class, 'index'])->name('user-access-review.index');
    Route::post('/user-access-review/{user}/force-reset', [\App\Http\Controllers\Web\UserAccessReviewController::class, 'forceReset'])->name('user-access-review.force-reset');
    Route::post('/user-access-review/{user}/revoke-sessions', [\App\Http\Controllers\Web\UserAccessReviewController::class, 'revokeSessions'])->name('user-access-review.revoke-sessions');
    Route::post('/user-access-review/{user}/deactivate', [\App\Http\Controllers\Web\UserAccessReviewController::class, 'deactivate'])->name('user-access-review.deactivate');

    Route::get('/students/{student}/personal-data-export', [\App\Http\Controllers\Web\StudentPrivacyController::class, 'export'])->name('students.personal-data-export');
    Route::post('/students/{student}/anonymize', [\App\Http\Controllers\Web\StudentPrivacyController::class, 'anonymize'])->name('students.anonymize');

    Route::get('/audit-logs', [\App\Http\Controllers\Web\TenantAuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/audit-logs/export.csv', [\App\Http\Controllers\Web\TenantAuditLogController::class, 'export'])->name('audit-logs.export');

    Route::get('/user-permissions', [\App\Http\Controllers\Web\UserPermissionsController::class, 'index'])->name('user-permissions.index');
    Route::get('/user-permissions/{user}/edit', [\App\Http\Controllers\Web\UserPermissionsController::class, 'edit'])->name('user-permissions.edit');
    Route::put('/user-permissions/{user}', [\App\Http\Controllers\Web\UserPermissionsController::class, 'update'])->name('user-permissions.update');

    Route::get('/staff/assignments', [\App\Http\Controllers\Web\TeacherSubjectAssignmentController::class, 'index'])->name('staff.assignments.index');
    Route::put('/staff/{user}/assignments', [\App\Http\Controllers\Web\TeacherSubjectAssignmentController::class, 'update'])->name('staff.assignments.update');

    Route::get('/staff-attendance', [\App\Http\Controllers\Web\StaffAttendanceController::class, 'index'])->name('staff-attendance.index');
    Route::get('/staff-attendance/create', [\App\Http\Controllers\Web\StaffAttendanceController::class, 'create'])->name('staff-attendance.create');
    Route::post('/staff-attendance', [\App\Http\Controllers\Web\StaffAttendanceController::class, 'store'])->name('staff-attendance.store');

    Route::get('/end-of-term', [\App\Http\Controllers\Web\EndOfTermController::class, 'index'])->name('end-of-term.index');
    Route::post('/end-of-term', [\App\Http\Controllers\Web\EndOfTermController::class, 'store'])->name('end-of-term.store');
    Route::get('/end-of-term/{end_of_term_run}', [\App\Http\Controllers\Web\EndOfTermController::class, 'show'])->name('end-of-term.show');
    Route::put('/end-of-term/{end_of_term_run}', [\App\Http\Controllers\Web\EndOfTermController::class, 'update'])->name('end-of-term.update');
    Route::post('/end-of-term/{end_of_term_run}/reopen', [\App\Http\Controllers\Web\EndOfTermController::class, 'reopen'])->name('end-of-term.reopen');

    Route::resource('staff', StaffController::class)->except(['show']);

    Route::get('/promotions', [PromotionController::class, 'index'])->name('promotions.index');
    Route::post('/promotions', [PromotionController::class, 'store'])->name('promotions.store');

    Route::get('/academic-years', [AcademicYearController::class, 'index'])->name('academic-years.index');
    Route::get('/academic-years/create', [AcademicYearController::class, 'create'])->name('academic-years.create');
    Route::post('/academic-years', [AcademicYearController::class, 'store'])->name('academic-years.store');
    Route::get('/academic-years/{academic_year}/edit', [AcademicYearController::class, 'edit'])->name('academic-years.edit');
    Route::put('/academic-years/{academic_year}', [AcademicYearController::class, 'update'])->name('academic-years.update');
    Route::delete('/academic-years/{academic_year}', [AcademicYearController::class, 'destroy'])->name('academic-years.destroy');
    Route::post('/academic-years/{academic_year}/set-current', [AcademicYearController::class, 'setCurrent'])->name('academic-years.set-current');
});
