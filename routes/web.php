<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AuthOtpController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PrefectController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentApiController;
use App\Http\Controllers\IncidentMapController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\ReportExportController;

Route::get('/', function () {
    return view('landing');
})->name('landing');

Route::get('/redirect-after-login', function () {
    $user = Auth::user();

    return match ($user->role) {
        'admin', 'super_admin', 'officer' => redirect()->route('dashboard'),
        'prefect' => redirect()->route('prefect'),
        'user', 'student', 'teacher' , 'staff' => redirect()->route('user.dashboard'),
        default => abort(403, 'Unauthorized'),
    };
})->middleware('auth')->name('redirect.after.login');

Route::get('/otp-verify', [AuthOtpController::class, 'showOtpForm'])->name('otp.verify.form');
Route::post('/otp-verify', [AuthOtpController::class, 'verifyOtp'])->name('otp.verify');
Route::post('/otp/cancel', [AuthOtpController::class, 'cancel'])->name('otp.cancel');

Route::post('/otp/resend', [AuthOtpController::class, 'resendOtp'])->name('otp.resend');

Route::post('/verify-password', function (\Illuminate\Http\Request $request) {
    if (\Illuminate\Support\Facades\Hash::check($request->password, auth()->user()->password)) {
        return response()->json(['valid' => true]);
    }
    return response()->json(['valid' => false]);
})->name('verify.password');

// Group for users
Route::middleware(['auth', 'verified', 'otp.verified'])->group(function () {
    Route::get('/user/dashboard', function () {
        if (!in_array(Auth::user()->role, ['user', 'student', 'teacher', 'staff'])) {
            abort(403);
        }
        return view('user.dashboard');
    })->name('user.dashboard');

    Route::get('/user/profile', [UserProfileController::class, 'show'])->name('user.profile');
    Route::get('/user/report', [ReportController::class, 'userReport'])->name('user.report');
    Route::post('/user/reports', [ReportController::class, 'store'])->name('user.reports.store');
    Route::delete('/user/reports/{report}', [ReportController::class, 'destroy'])->name('user.reports.destroy');

    Route::get('/user/reports/{report}', [ReportController::class, 'show'])->name('user.reports.show');
    Route::post('/reports/{report}/comments', [CommentController::class, 'store'])->name('comments.store');
});

// Group for admins
Route::middleware(['auth', 'verified', 'otp.verified'])->group(function () {
    Route::get('/dashboard', function () {
    if (!in_array(Auth::user()->role, ['admin', 'super_admin', 'prefect', 'officer'])) {
        abort(403);
    }

    return app(DashboardController::class)->dashboard(
            app(App\Services\GeminiAnalyticsService::class) 
        );
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/reports/completed', [ReportController::class, 'completed'])->name('reports.completed');
    Route::get('/reports/in-progress', [ReportController::class, 'inProgress'])->name('reports.in_progress');
    Route::get('/reports/pending', [ReportController::class, 'pending'])->name('reports.pending');
    Route::get('/reports/deny', [ReportController::class, 'deny'])->name('reports.deny');
    Route::get('/reports/archived', function () {
    if (Auth::user()->role !== 'super_admin' && Auth::user()->role !== 'admin') {
            abort(403);
        }
        return app(ReportController::class)->archived();
    })->name('reports.archived');
    Route::get('/reports/{report}/edit', [ReportController::class, 'edit'])->name('reports.edit');
    Route::get('/reports/export/pdf', [ReportExportController::class, 'exportPdf'])->name('reports.export.pdf');
    Route::get('/reports/export-excel', [ReportExportController::class, 'exportExcel'])->name('reports.export.excel');
    Route::get('/reports/export-xls', [ReportExportController::class, 'exportXls'])->name('reports.export.xls');
    Route::post('/reports/view-pdf', [ReportExportController::class, 'viewPdf'])->name('reports.view-pdf');
    Route::post('/reports/bulk-delete', [ReportController::class, 'bulkDelete'])->name('reports.bulkDelete');
    Route::post('/reports/bulk-archive', [ReportController::class, 'bulkArchive'])->name('reports.bulkArchive');
    Route::post('/reports/bulk-unarchive', [ReportController::class, 'bulkUnarchive'])->name('reports.bulk-unarchive');
    Route::get('/reports/chart-data', [DashboardController::class, 'reportsChartData']);
    Route::get('/reports/incident-type-chart-data', [DashboardController::class, 'incidentTypeChartData']);
    Route::post('/reports/archive-by-month', [ReportController::class, 'archiveByMonth'])
        ->name('reports.archive_by_month');
    Route::patch('/reports/{id}/archive', [ReportController::class, 'archive'])->name('reports.archive');
    Route::post('/reports/{report}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::post('/module-unlock/{module}', function (\Illuminate\Http\Request $request, $module) {
        if (!\Hash::check($request->password, $request->user()->password)) {
            return response()->json(['error' => 'Invalid password'], 401);
        }

        $request->session()->put("unlocked_$module", true);
        return response()->json(['success' => true]);
    })->name('module.unlock');
    Route::resource('reports', ReportController::class)
        ->middleware(['auth', 'module.password:reports']);

    Route::get('/student', [StudentApiController::class, 'index'])
        ->name('student.index')
        ->middleware(['auth', 'module.password:student']);

    Route::get('/prefect', [PrefectController::class, 'index'])
        ->name('prefect')
        ->middleware(['auth', 'module.password:prefect']);

    Route::get('/clinic', [ClinicController::class, 'index'])
        ->name('clinic')
        ->middleware(['auth', 'module.password:clinic']);


    Route::resource('employees', EmployeeController::class);

    Route::prefix('list-of-user')->name('users.')->group(function () {
        Route::get('/user', [UsersController::class, 'index'])->name('index');
        Route::post('/user', [UsersController::class, 'store'])->name('store');
    });

    Route::put('/users/{user}', [UsersController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UsersController::class, 'destroy'])->name('users.destroy');

    Route::get('/incidents/map', [IncidentMapController::class, 'index'])->name('incidents.map');
});

    Route::get('/api/floorplan/{floor}', function($floor) {
        $allowedFloors = ['ground', 'second', 'third', 'fourth', 'fifth'];
        
        if (!in_array($floor, $allowedFloors)) {
            return response()->json(['error' => 'Invalid floor'], 404);
        }
        
        // Point to HTML version instead of SVG
        $htmlPath = storage_path("app/public/floorplans/{$floor}.html");
        
        $htmlContent = file_get_contents($htmlPath);
        return response($htmlContent)->header('Content-Type', 'text/html');
    });


// Chatbot routes (outside middleware groups for now)
Route::get('/chatbot', function () {
    return view('chatbot');
})->name('chatbot.ui');

Route::post('/api/chatbot/report', [ChatbotController::class, 'report'])->name('chatbot.report');
Route::post('/chatbot/reset', [ChatbotController::class, 'reset'])->name('chatbot.reset');

require __DIR__.'/auth.php';