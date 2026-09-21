<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WfhRequestController;
use App\Models\Attendance;
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
        $approvedThisMonth = WfhRequest::where('status', 'approved')->whereMonth('created_at', now()->month)->count();
        $todayAttendance = Attendance::whereDate('date', today())->count();

        return view('dashboard.index', compact('employeeCount', 'pendingWfhRequests', 'approvedThisMonth', 'todayAttendance'));
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
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Employees
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.profile');
    Route::patch('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.profile');
    Route::get('/employees/{employee}/work-schedule', [EmployeeController::class, 'workSchedule'])->name('employees.work-schedule');
    Route::post('/employees/{employee}/work-schedule', [EmployeeController::class, 'storeScheduleEntry'])->name('employees.store-schedule');
    Route::patch('/work-schedule-entries/{scheduleEntry}', [EmployeeController::class, 'updateScheduleEntry'])->name('schedule-entries.update');
    Route::delete('/work-schedule-entries/{scheduleEntry}', [EmployeeController::class, 'destroyScheduleEntry'])->name('schedule-entries.destroy');

    // My Profile (for regular employees)
    Route::get('/my-profile', [EmployeeController::class, 'myProfile'])->name('my-profile');
    Route::patch('/my-profile', [EmployeeController::class, 'updateMyProfile'])->name('my-profile.update');
    Route::post('/my-profile/change-password', [EmployeeController::class, 'changePassword'])->name('my-profile.change-password');
    Route::get('/my-work-schedule', [EmployeeController::class, 'myWorkSchedule'])->name('my-work-schedule');
    Route::post('/my-work-schedule', [EmployeeController::class, 'storeMyScheduleEntry'])->name('my-work-schedule.store');
    Route::patch('/my-schedule-entries/{id}', [EmployeeController::class, 'updateMyScheduleEntry'])->name('my-schedule-entries.update');

    // WFH Management
    Route::prefix('wfh')->name('wfh.')->group(function () {
        Route::get('/my-requests', [WfhRequestController::class, 'index'])->name('my-requests');
        Route::get('/requests/create', [WfhRequestController::class, 'create'])->name('requests.create');
        Route::post('/requests', [WfhRequestController::class, 'store'])->name('requests.store');
        Route::get('/requests/{wfhRequest}', [WfhRequestController::class, 'show'])->name('requests.show');
        Route::get('/approval', [WfhRequestController::class, 'approval'])->name('approval');
        Route::patch('/requests/{wfhRequest}/approval', [WfhRequestController::class, 'updateApproval'])->name('requests.approval');
        Route::get('/calendar', [WfhRequestController::class, 'calendar'])->name('calendar');
    });

    // Attendance
    Route::get('/attendance/time-in-out', function () {
        return view('attendance.time-in-out');
    })->name('attendance.time-in-out');
    Route::get('/attendance/daily', function () {
        return view('attendance.daily');
    })->name('attendance.daily');
    Route::get('/attendance/corrections', function () {
        return view('attendance.corrections');
    })->name('attendance.corrections');

    // Accomplishments
    Route::get('/accomplishments/daily-tasks', function () {
        return view('accomplishments.daily-tasks');
    })->name('accomplishments.daily-tasks');
    Route::get('/accomplishments/reports', function () {
        return view('accomplishments.reports');
    })->name('accomplishments.reports');
    Route::get('/accomplishments/attachments', function () {
        return view('accomplishments.attachments');
    })->name('accomplishments.attachments');

    // Approvals
    Route::redirect('/approvals/wfh-requests', '/wfh/approval')->name('approvals.wfh-requests');
    Route::get('/approvals/attendance-corrections', function () {
        return view('approvals.attendance-corrections');
    })->name('approvals.attendance-corrections');
    Route::get('/approvals/accomplishment-reports', function () {
        return view('approvals.accomplishment-reports');
    })->name('approvals.accomplishment-reports');

    // Reports
    Route::get('/reports/attendance', function () {
        return view('reports.attendance');
    })->name('reports.attendance');
    Route::get('/reports/wfh', function () {
        return view('reports.wfh');
    })->name('reports.wfh');
    Route::get('/reports/accomplishments', function () {
        return view('reports.accomplishments');
    })->name('reports.accomplishments');
    Route::get('/reports/payroll', function () {
        return view('reports.payroll');
    })->name('reports.payroll');

    // Administration
    Route::get('/admin/users', function () {
        return view('admin.users');
    })->name('admin.users');
    Route::get('/admin/roles', function () {
        return view('admin.roles');
    })->name('admin.roles');
    Route::get('/admin/departments', function () {
        return view('admin.departments');
    })->name('admin.departments');
    Route::get('/admin/work-schedules', function () {
        return view('admin.work-schedules');
    })->name('admin.work-schedules');
    Route::get('/admin/holidays', function () {
        return view('admin.holidays');
    })->name('admin.holidays');

    // Documents
    Route::get('/documents', function () {
        return view('documents.index');
    })->name('documents.index');

    // Notifications
    Route::get('/notifications', function () {
        return view('notifications.index');
    })->name('notifications.index');

    // Audit Logs
    Route::get('/audit-logs', function () {
        return view('audit-logs.index');
    })->name('audit-logs.index');
});

require __DIR__.'/auth.php';
