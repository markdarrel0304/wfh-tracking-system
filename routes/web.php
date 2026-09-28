<?php

use App\Http\Controllers\AccomplishmentReportController;
use App\Http\Controllers\AccomplishmentReportSummaryController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceCorrectionController;
use App\Http\Controllers\AttendanceReportController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DepartmentManagementController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HolidayManagementController;
use App\Http\Controllers\NavigationModeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OutputAttachmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleManagementController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\WfhReportController;
use App\Http\Controllers\WfhRequestController;
use App\Models\AccomplishmentReport;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\Employee;
use App\Models\WfhRequest;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->role === 'admin') {
        $employeeCount = Employee::count();
        $pendingWfhRequests = WfhRequest::where('status', 'pending')->count();
        $pendingAttendanceCorrections = AttendanceCorrection::where('status', 'pending')->count();
        $pendingAccomplishmentReports = AccomplishmentReport::query()
            ->where('status', 'submitted')
            ->whereNotNull('submitted_at')
            ->count();
        $approvedThisMonth = WfhRequest::where('status', 'approved')->whereMonth('created_at', now()->month)->count();
        $todayAttendance = Attendance::whereDate('date', today())->count();
        $todayAttendanceRecords = Attendance::query()
            ->with(['employee.department'])
            ->whereDate('date', today())
            ->latest('time_in')
            ->get();
        $recentPendingWfhRequests = WfhRequest::query()
            ->with(['employee.department'])
            ->where('status', 'pending')
            ->oldest()
            ->limit(5)
            ->get();
        $recentPendingReports = AccomplishmentReport::query()
            ->with(['employee', 'dailyTask'])
            ->withCount('outputAttachments')
            ->where('status', 'submitted')
            ->whereNotNull('submitted_at')
            ->oldest('submitted_at')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'employeeCount',
            'pendingWfhRequests',
            'pendingAttendanceCorrections',
            'pendingAccomplishmentReports',
            'approvedThisMonth',
            'todayAttendance',
            'todayAttendanceRecords',
            'recentPendingWfhRequests',
            'recentPendingReports',
        ));
    } else {
        $employee = $user->employee;
        $myWfhRequests = $employee ? WfhRequest::where('employee_id', $employee->id)->count() : 0;
        $myAttendance = $employee ? Attendance::where('employee_id', $employee->id)->count() : 0;
        $todayStatus = 'Not Clocked In';

        if ($employee) {
            $todayRecord = Attendance::where('employee_id', $employee->id)->whereDate('date', today())->first();
            if ($todayRecord) {
                $todayStatus = $todayRecord->time_out ? 'Clocked Out' : 'Clocked In';
            }
        }

        return view('dashboard.index', compact('myWfhRequests', 'myAttendance', 'todayStatus'));
    }
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::patch('/navigation-mode', [NavigationModeController::class, 'update'])->name('navigation-mode.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Employees
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.profile');
    Route::patch('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.profile');
    Route::get('/my-profile', [EmployeeController::class, 'myProfile'])->name('my-profile');
    Route::patch('/my-profile', [EmployeeController::class, 'updateMyProfile'])->name('my-profile.update');
    Route::post('/my-profile/change-password', [EmployeeController::class, 'changePassword'])->name('my-profile.change-password');
    Route::get('/my-work-schedule', [EmployeeController::class, 'myWorkSchedule'])->name('my-work-schedule');

    // WFH Management
    Route::prefix('wfh')->name('wfh.')->group(function () {
        Route::get('/my-requests', [WfhRequestController::class, 'index'])->name('my-requests');
        Route::get('/requests/create', [WfhRequestController::class, 'create'])->name('requests.create');
        Route::post('/requests', [WfhRequestController::class, 'store'])->name('requests.store');
        Route::get('/requests/{wfhRequest}', [WfhRequestController::class, 'show'])->name('requests.show');
        Route::get('/requests/{wfhRequest}/supporting-document', [WfhRequestController::class, 'downloadSupportingDocument'])->name('requests.supporting-document');
        Route::get('/requests/{wfhRequest}/reviewer-document', [WfhRequestController::class, 'downloadReviewerDocument'])->name('requests.reviewer-document');
        Route::get('/approval', [WfhRequestController::class, 'approval'])->name('approval');
        Route::get('/approval/{wfhRequest}', [WfhRequestController::class, 'review'])->name('approval.review');
        Route::patch('/requests/{wfhRequest}/approval', [WfhRequestController::class, 'updateApproval'])->name('requests.approval');
        Route::get('/calendar', [WfhRequestController::class, 'calendar'])->name('calendar');
    });

    // Attendance
    Route::get('/attendance/time-in-out', [AttendanceController::class, 'index'])->name('attendance.time-in-out');
    Route::post('/attendance/time-in', [AttendanceController::class, 'timeIn'])->name('attendance.time-in');
    Route::patch('/attendance/lunch/start', [AttendanceController::class, 'startLunch'])->name('attendance.lunch.start');
    Route::patch('/attendance/lunch/end', [AttendanceController::class, 'endLunch'])->name('attendance.lunch.end');
    Route::patch('/attendance/time-out', [AttendanceController::class, 'timeOut'])->name('attendance.time-out');
    Route::patch('/attendance/time-out-early', [AttendanceController::class, 'earlyTimeOut'])->name('attendance.time-out-early');
    Route::patch('/attendance/overtime/start', [AttendanceController::class, 'startOvertime'])->name('attendance.overtime.start');
    Route::patch('/attendance/overtime/end', [AttendanceController::class, 'endOvertime'])->name('attendance.overtime.end');
    Route::get('/attendance/daily', [AttendanceController::class, 'history'])->name('attendance.daily');
    Route::get('/attendance/corrections', [AttendanceCorrectionController::class, 'index'])->name('attendance.corrections');
    Route::post('/attendance/corrections', [AttendanceCorrectionController::class, 'store'])->name('attendance.corrections.store');
    Route::get('/attendance/corrections/{attendanceCorrection}/supporting-document', [AttendanceCorrectionController::class, 'downloadSupportingDocument'])->name('attendance.corrections.supporting-document');

    // Accomplishments
    Route::get('/accomplishments/reports', [AccomplishmentReportController::class, 'index'])->name('accomplishments.reports');
    Route::post('/accomplishments/reports', [AccomplishmentReportController::class, 'store'])->name('accomplishments.reports.store');
    Route::get('/accomplishments/attachments', [OutputAttachmentController::class, 'index'])->name('accomplishments.attachments');
    Route::post('/accomplishments/attachments', [OutputAttachmentController::class, 'store'])->name('accomplishments.attachments.store');
    Route::get('/accomplishments/attachments/{outputAttachment}/download', [OutputAttachmentController::class, 'download'])->name('accomplishments.attachments.download');
    Route::delete('/accomplishments/attachments/{outputAttachment}', [OutputAttachmentController::class, 'destroy'])->name('accomplishments.attachments.destroy');

    // Approvals
    Route::redirect('/approvals/wfh-requests', '/wfh/approval')->name('approvals.wfh-requests');
    Route::get('/approvals/attendance-corrections', [AttendanceCorrectionController::class, 'approval'])->name('approvals.attendance-corrections');
    Route::get('/approvals/attendance-corrections/{attendanceCorrection}', [AttendanceCorrectionController::class, 'review'])->name('approvals.attendance-corrections.review');
    Route::patch('/approvals/attendance-corrections/{attendanceCorrection}', [AttendanceCorrectionController::class, 'updateApproval'])->name('approvals.attendance-corrections.update');
    Route::get('/approvals/accomplishment-reports', [AccomplishmentReportController::class, 'approval'])->name('approvals.accomplishment-reports');
    Route::patch('/approvals/accomplishment-reports/{accomplishmentReport}', [AccomplishmentReportController::class, 'updateApproval'])->name('approvals.accomplishment-reports.update');
    Route::patch('/approvals/accomplishment-reports/{accomplishmentReport}/request-revision', [AccomplishmentReportController::class, 'requestRevision'])->name('approvals.accomplishment-reports.request-revision');

    // Reports
    Route::get('/reports/attendance', [AttendanceReportController::class, 'index'])->name('reports.attendance');
    Route::get('/reports/attendance/export', [AttendanceReportController::class, 'export'])->name('reports.attendance.export');
    Route::get('/reports/attendance/{employee}', [AttendanceReportController::class, 'show'])->name('reports.attendance.employee');
    Route::get('/reports/wfh', [WfhReportController::class, 'index'])->name('reports.wfh');
    Route::get('/reports/wfh/export', [WfhReportController::class, 'export'])->name('reports.wfh.export');
    Route::get('/reports/wfh/{employee}', [WfhReportController::class, 'show'])->name('reports.wfh.employee');
    Route::get('/reports/accomplishments', [AccomplishmentReportSummaryController::class, 'index'])->name('reports.accomplishments');
    Route::get('/reports/accomplishments/export', [AccomplishmentReportSummaryController::class, 'export'])->name('reports.accomplishments.export');
    Route::get('/reports/accomplishments/{employee}', [AccomplishmentReportSummaryController::class, 'show'])->name('reports.accomplishments.employee');
    Route::get('/reports/payroll', function () {
        return view('reports.payroll');
    })->name('reports.payroll');

    // Administration
    Route::get('/admin/users', [UserManagementController::class, 'index'])->name('admin.users');
    Route::post('/admin/users', [UserManagementController::class, 'store'])->name('admin.users.store');
    Route::patch('/admin/users/{user}', [UserManagementController::class, 'update'])->name('admin.users.update');
    Route::patch('/admin/users/{user}/password', [UserManagementController::class, 'resetPassword'])->name('admin.users.password');
    Route::get('/admin/roles', [RoleManagementController::class, 'index'])->name('admin.roles');
    Route::get('/admin/departments', [DepartmentManagementController::class, 'index'])->name('admin.departments');
    Route::post('/admin/departments', [DepartmentManagementController::class, 'store'])->name('admin.departments.store');
    Route::patch('/admin/departments/{department}', [DepartmentManagementController::class, 'update'])->name('admin.departments.update');
    Route::post('/admin/departments/{department}/employees', [DepartmentManagementController::class, 'assignEmployee'])->name('admin.departments.employees.store');
    Route::get('/admin/holidays', [HolidayManagementController::class, 'index'])->name('admin.holidays');
    Route::post('/admin/holidays', [HolidayManagementController::class, 'store'])->name('admin.holidays.store');
    Route::patch('/admin/holidays/{holiday}', [HolidayManagementController::class, 'update'])->name('admin.holidays.update');

    // Documents
    Route::get('/documents', function () {
        return view('documents.index');
    })->name('documents.index');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}', [NotificationController::class, 'show'])->name('notifications.show');
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // Audit Logs
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
});

require __DIR__.'/auth.php';
