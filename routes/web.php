<?php

use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExamResultController;
use App\Http\Controllers\InstituteController;
use App\Http\Controllers\InternalEvaluationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentPortalController;
use App\Http\Controllers\StudyMaterialController;
use App\Http\Controllers\TransactionController;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Institute;
use App\Models\Project;
use App\Models\Resource;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/apply', [StudentController::class, 'showForm'])->name('apply');
Route::post('/apply/store', [StudentController::class, 'store'])->name('apply.store');
Route::get('/apply/success/{referenceNo}', [StudentController::class, 'success'])->name('apply.success');
Route::get('/api/courses', [StudentController::class, 'getCourses'])->name('api.courses');
Route::get('/api/batches', [StudentController::class, 'getBatches'])->name('api.batches');

// Public Student Portal
Route::get('/portal', [StudentPortalController::class, 'index'])->name('portal.index');
Route::post('/portal', [StudentPortalController::class, 'show'])->name('portal.show');
Route::post('/portal/verify', [StudentPortalController::class, 'verify'])->name('portal.verify');
Route::get('/portal/material/{studyMaterial}/download', [StudentPortalController::class, 'downloadMaterial'])->name('portal.material.download');
Route::post('/portal/evaluation/store', [StudentPortalController::class, 'submitEvaluation'])->name('portal.evaluation.store');

// Public QR self-attendance landing page (BRD module #10)
Route::get('/attendance/scan/{token}', [AttendanceController::class, 'scanForm'])->name('attendance.scan');
Route::post('/attendance/scan/{token}', [AttendanceController::class, 'scanStore'])->name('attendance.scan.store');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        $institute = Auth::user()->institute;
        $instituteId = $institute?->id;
        $projects = $institute ? Project::where('institute_id', $institute->id)->get() : collect();

        $courseCount = $institute ? Course::where('institute_id', $institute->id)->count() : 0;
        $batchCount = $institute ? Batch::where('institute_id', $institute->id)->count() : 0;
        $studentCount = $institute ? Student::where('institute_id', $institute->id)->count() : 0;
        $appliedCount = $institute ? Student::where('institute_id', $institute->id)->where('status', 'applied')->count() : 0;
        $admittedCount = $institute ? Student::where('institute_id', $institute->id)->where('status', 'admitted')->count() : 0;
        $rejectedCount = $institute ? Student::where('institute_id', $institute->id)->where('status', 'rejected')->count() : 0;

        $projectStats = $projects->map(fn ($p) => [
            'project' => $p,
            'courses' => Course::where('project_id', $p->id)->count(),
            'batches' => Batch::where('project_id', $p->id)->count(),
            'students' => Student::where('project_id', $p->id)->count(),
            'applied' => Student::where('project_id', $p->id)->where('status', 'applied')->count(),
            'admitted' => Student::where('project_id', $p->id)->where('status', 'admitted')->count(),
        ]);

        $allStudents = $institute ? Student::with(['course', 'batch', 'project'])
            ->where('institute_id', $institute->id)
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get() : collect();

        $institutesCount = Institute::count();

        $todaySessions = $institute ? AttendanceSession::where('institute_id', $institute->id)
            ->whereDate('session_date', today())->count() : 0;

        $todayRows = $institute ? Attendance::where('institute_id', $institute->id)
            ->whereDate('created_at', today())
            ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status') : collect();

        $todayPresent = (int) ($todayRows->get(Attendance::STATUS_PRESENT, 0) + $todayRows->get(Attendance::STATUS_LATE, 0));
        $todayAbsent = (int) $todayRows->get(Attendance::STATUS_ABSENT, 0);
        $todayTotal = $todayPresent + $todayAbsent;

        $examCount = $institute ? Exam::where('institute_id', $institute->id)->count() : 0;
        $publishedExamCount = $institute ? Exam::where('institute_id', $institute->id)
            ->where('status', Exam::STATUS_PUBLISHED)->count() : 0;
        $awaitingPublication = $examCount - $publishedExamCount;

        // ===== Financial metrics (current month income/expense) — Admin Dashboard upgrade (BRD module #1/#3) =====
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $monthlyIncome = (float) Transaction::where('institute_id', $instituteId)
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->sum('amount');

        $monthlyExpense = (float) Transaction::where('institute_id', $instituteId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->sum('amount');

        $netCashflow = $monthlyIncome - $monthlyExpense;

        // ===== Financial performance trend (last 6 months) for analytics chart =====
        $financialTrend = collect();
        for ($i = 5; $i >= 0; $i--) {
            $start = now()->subMonths($i)->startOfMonth();
            $end = now()->subMonths($i)->endOfMonth();

            $income = (float) Transaction::where('institute_id', $instituteId)
                ->where('type', 'income')
                ->whereBetween('transaction_date', [$start, $end])
                ->sum('amount');

            $expense = (float) Transaction::where('institute_id', $instituteId)
                ->where('type', 'expense')
                ->whereBetween('transaction_date', [$start, $end])
                ->sum('amount');

            $financialTrend->push([
                'label' => now()->subMonths($i)->translatedFormat('M Y'),
                'income' => $income,
                'expense' => $expense,
            ]);
        }

        // ===== Trainer / Instructor count =====
        $trainerCount = User::where('institute_id', $instituteId)
            ->where('role', 'trainer')
            ->count();

        // ===== Damaged / Maintenance lab resources alert =====
        $damagedResourcesCount = Resource::where('institute_id', $instituteId)
            ->whereIn('condition', ['damaged', 'under_maintenance'])
            ->count();

        $damagedResources = Resource::where('institute_id', $instituteId)
            ->whereIn('condition', ['damaged', 'under_maintenance'])
            ->orderBy('condition', 'asc')
            ->take(8)
            ->get();

        // ===== Course-wise student distribution (chart data) =====
        $courseDistribution = Course::where('institute_id', $instituteId)
            ->withCount('students')
            ->get();

        // ===== Low attendance warnings (<80%), statuses admitted/active (BRD module #10 fix) =====
        $lowAttendanceStudents = Student::where('institute_id', $instituteId)
            ->whereIn('status', ['admitted', 'active'])
            ->has('attendances')
            ->with(['batch.course'])
            ->get()
            ->filter(fn ($student) => ($student->attendanceSummary()['percentage'] ?? 0) < 80)
            ->take(10);

        return view('dashboard', compact(
            'institute', 'projects', 'projectStats', 'courseCount', 'batchCount', 'studentCount',
            'appliedCount', 'admittedCount', 'rejectedCount', 'allStudents', 'institutesCount',
            'todaySessions', 'todayPresent', 'todayAbsent', 'todayTotal',
            'examCount', 'publishedExamCount', 'awaitingPublication',
            'monthlyIncome', 'monthlyExpense', 'netCashflow', 'financialTrend',
            'damagedResourcesCount', 'damagedResources', 'trainerCount', 'courseDistribution',
            'lowAttendanceStudents'
        ));
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Standalone "create" pages were replaced by Bootstrap modals on each index page.
    Route::get('/{module}/create', fn () => abort(404))
        ->whereIn('module', [
            'projects', 'courses', 'batches', 'attendance', 'exams', 'schedules',
            'transactions', 'study-materials', 'resources', 'certificates',
            'notifications', 'notices', 'evaluations',
        ]);

    // Projects
    Route::post('/projects/switch-context', [ProjectController::class, 'switchContext'])->name('projects.switch-context');
    Route::post('/projects/{project}/switch', [ProjectController::class, 'switch'])->name('projects.switch');
    Route::get('/projects/{project}/dashboard', [ProjectController::class, 'dashboard'])->name('projects.dashboard');
    Route::resource('projects', ProjectController::class)->except(['create', 'show']);

    Route::resource('courses', CourseController::class)->except(['create', 'show']);
    Route::resource('batches', BatchController::class)->except(['create', 'show']);
    Route::post('/projects/{project}/courses/inline-store', [CourseController::class, 'inlineStore'])->name('courses.inline-store');
    Route::post('/projects/{project}/batches/inline-store', [BatchController::class, 'inlineStore'])->name('batches.inline-store');

    Route::get('/applications', [AdmissionController::class, 'applications'])->name('admissions.applications');
    Route::get('/applications/{student}', [AdmissionController::class, 'show'])->name('admissions.show');
    Route::get('/applications/{student}/print', [AdmissionController::class, 'print'])->name('admissions.print');
    Route::post('/applications/{student}/approve', [AdmissionController::class, 'approve'])->name('admissions.approve');
    Route::post('/applications/{student}/reject', [AdmissionController::class, 'reject'])->name('admissions.reject');
    Route::post('/applications/{student}/change-batch', [AdmissionController::class, 'changeBatch'])->name('admissions.change-batch');
    Route::post('/applications/{student}/remove-batch', [AdmissionController::class, 'removeFromBatch'])->name('admissions.remove-batch');
    Route::get('/students', [AdmissionController::class, 'students'])->name('admissions.students');

    Route::get('/institute/edit', [InstituteController::class, 'edit'])->name('institute.edit');
    Route::patch('/institute/update', [InstituteController::class, 'update'])->name('institute.update');

    // Attendance (BRD module #10)
    Route::get('/attendance/monthly-report', [AttendanceController::class, 'monthlyReport'])->name('attendance.monthly-report');
    Route::get('/attendance/monthly-report.csv', [AttendanceController::class, 'monthlyReportCsv'])->name('attendance.monthly-report.csv');
    Route::get('/attendance/project-report', [AttendanceController::class, 'projectReport'])->name('attendance.project-report');
    Route::get('/attendance/project-report.csv', [AttendanceController::class, 'projectReportCsv'])->name('attendance.project-report.csv');
    Route::post('/attendance/{attendance_session}/mark', [AttendanceController::class, 'mark'])->name('attendance.mark');
    Route::post('/attendance/{attendance_session}/close', [AttendanceController::class, 'close'])->name('attendance.close');
    Route::post('/attendance/{attendance_session}/reopen', [AttendanceController::class, 'reopen'])->name('attendance.reopen');
    Route::post('/attendance/{attendance_session}/qr/regenerate', [AttendanceController::class, 'regenerateQr'])->name('attendance.qr.regenerate');
    Route::resource('attendance', AttendanceController::class)
        ->parameters(['attendance' => 'attendance_session'])
        ->except(['edit', 'update', 'create']);

    // Exams and results (BRD module #15)
    Route::post('/exams/{exam}/publish', [ExamController::class, 'publish'])->name('exams.publish');
    Route::post('/exams/{exam}/revert', [ExamController::class, 'revert'])->name('exams.revert');
    Route::get('/exams/{exam}/marks', [ExamResultController::class, 'edit'])->name('exams.marks.edit');
    Route::post('/exams/{exam}/marks', [ExamResultController::class, 'update'])->name('exams.marks.update');
    Route::get('/exams/{exam}/results', [ExamResultController::class, 'results'])->name('exams.results');
    Route::get('/exams/{exam}/results.csv', [ExamResultController::class, 'resultsCsv'])->name('exams.results.csv');
    Route::get('/exams/{exam}/report-card/{student}', [ExamResultController::class, 'reportCard'])->name('exams.report-card');
    Route::resource('exams', ExamController::class)->except(['create']);

    // Class routine (BRD module #11)
    Route::get('/schedules/print', [ScheduleController::class, 'print'])->name('schedules.print');
    Route::resource('schedules', ScheduleController::class)->except(['show', 'create']);

    // Transactions
    Route::get('/transactions/export', [TransactionController::class, 'export'])->name('export.transactions');
    Route::get('/transactions/receipt/{transaction}', [TransactionController::class, 'receipt'])->name('transactions.receipt');
    Route::resource('transactions', TransactionController::class)->except(['create']);

    // Study Materials
    Route::get('/study-materials/{studyMaterial}/download', [StudyMaterialController::class, 'download'])->name('study-materials.download');
    Route::resource('study-materials', StudyMaterialController::class)->except(['create', 'show']);

    // Resources
    Route::resource('resources', ResourceController::class)->except(['create', 'show']);
    Route::resource('certificates', CertificateController::class)->except(['create']);


    // Notifications
    Route::post('/notifications/send-single', [NotificationController::class, 'sendSingle'])->name('notifications.send-single');
    Route::post('/notifications/send-bulk', [NotificationController::class, 'sendBulk'])->name('notifications.send-bulk');
    Route::resource('notifications', NotificationController::class)->only(['index']);
    Route::resource('notices', NoticeController::class)->except(['create']);

    // Internal Evaluations
    Route::get('/evaluations/report', [InternalEvaluationController::class, 'report'])->name('evaluations.report');
    Route::resource('evaluations', InternalEvaluationController::class)->except(['create', 'show']);
});

require __DIR__.'/auth.php';
