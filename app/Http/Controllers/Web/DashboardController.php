<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Platform\DashboardController as PlatformDashboardController;
use App\Http\Controllers\Web\Dashboards\AccountantDashboardController;
use App\Http\Controllers\Web\Dashboards\AdminDashboardController;
use App\Http\Controllers\Web\Dashboards\ProprietorDashboardController;
use App\Http\Controllers\Web\Dashboards\TeacherDashboardController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

/**
 * Universal /dashboard entry point.
 *
 * One URL serves every role:
 *   - SuperAdmin → platform overview
 *   - Proprietor / Admin / Accountant / Teacher → tenant role dashboard
 *   - Parent → parent portal home
 *   - Student → student portal home
 *
 * Sub-pages live under /dashboard/<slug> (see routes/web.php — the platform
 * SuperAdmin routes are URL-prefixed with /dashboard, keeping route names
 * as platform.* for internal stability).
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request): mixed
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        // Roles with their own bespoke dashboard view. Anything else (e.g.
        // Headteacher, ExamOfficer, AttendanceOfficer, Librarian,
        // InventoryOfficer, DisciplineOfficer) falls back to the Admin
        // dashboard — they share the same tenant scope; what they can
        // *do* is constrained by the permission layer, not the dashboard
        // they land on.
        return match ($user->role) {
            UserRole::SuperAdmin->value
                => App::call([App::make(PlatformDashboardController::class), '__invoke']),

            UserRole::Proprietor->value
                => App::call([App::make(ProprietorDashboardController::class), '__invoke']),

            UserRole::Accountant->value
                => App::call([App::make(AccountantDashboardController::class), '__invoke']),

            UserRole::Teacher->value
                => App::call([App::make(TeacherDashboardController::class), '__invoke']),

            UserRole::Parent->value
                => $this->portalRedirect('portal.parent.index'),

            UserRole::Student->value
                => $this->portalRedirect('portal.student.index'),

            default
                => App::call([App::make(AdminDashboardController::class), '__invoke']),
        };
    }

    /**
     * Parent and student portals already live at their own URLs (portal-style
     * navigation, distinct UI). Send those users there from /dashboard.
     */
    private function portalRedirect(string $routeName): RedirectResponse
    {
        return redirect()->route($routeName);
    }
}
