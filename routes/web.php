<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Health check endpoint (public, no authentication required)
Route::get('/healthz', [\App\Http\Controllers\HealthController::class, 'check'])->name('health.check');

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'company.scope'])
    ->name('dashboard');

Route::middleware(['auth', 'company.scope'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Two-Factor Authentication routes
    Route::get('/profile/two-factor-auth', [\App\Http\Controllers\TwoFactorAuthController::class, 'show'])->name('profile.two-factor-auth');
    Route::post('/profile/two-factor-auth/enable', [\App\Http\Controllers\TwoFactorAuthController::class, 'enable'])->name('profile.two-factor-auth.enable');
    Route::post('/profile/two-factor-auth/confirm', [\App\Http\Controllers\TwoFactorAuthController::class, 'confirm'])->name('profile.two-factor-auth.confirm');
    Route::delete('/profile/two-factor-auth/disable', [\App\Http\Controllers\TwoFactorAuthController::class, 'disable'])->name('profile.two-factor-auth.disable');
    Route::post('/profile/two-factor-auth/recovery-codes', [\App\Http\Controllers\TwoFactorAuthController::class, 'generateRecoveryCodes'])->name('profile.two-factor-auth.recovery-codes');
    
    // Subscription routes
    Route::get('/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscription.subscribe');
    Route::post('/subscribe', [SubscriptionController::class, 'store'])->name('subscription.store');
    
    // Resource routes with subscription middleware
    Route::middleware('subscription')->group(function () {
        Route::resource('departments', \App\Http\Controllers\DepartmentController::class);
        Route::resource('gages', \App\Http\Controllers\GageController::class);
        Route::resource('calibration-records', \App\Http\Controllers\CalibrationRecordController::class);
        Route::get('calibration-records/{calibrationRecord}/download-certificate', [\App\Http\Controllers\CalibrationRecordController::class, 'downloadCertificate'])->name('calibration-records.download-certificate');
        
        // Measurement groups routes
        Route::resource('measurement-groups', \App\Http\Controllers\MeasurementGroupController::class);
        Route::post('measurement-groups/{measurementGroup}/update-measurements', [\App\Http\Controllers\MeasurementGroupController::class, 'updateMeasurements'])->name('measurement-groups.update-measurements');
        
        // Gage tolerance records routes
        Route::resource('gage-tolerance-records', \App\Http\Controllers\GageToleranceRecordController::class);
        Route::post('gage-tolerance-records/{gageToleranceRecord}/resolve', [\App\Http\Controllers\GageToleranceRecordController::class, 'resolve'])->name('gage-tolerance-records.resolve');
        
        // Gage checkout routes
        Route::post('gages/{gage}/checkout', [\App\Http\Controllers\GageCheckoutController::class, 'checkout'])->name('gages.checkout');
        Route::post('gages/{gage}/checkin', [\App\Http\Controllers\GageCheckoutController::class, 'checkin'])->name('gages.checkin');
        Route::get('gages/{gage}/checkout-history', [\App\Http\Controllers\GageCheckoutController::class, 'history'])->name('gages.checkout-history');
        
        // Export routes
        Route::get('export/gages/csv', [\App\Http\Controllers\ExportController::class, 'exportGagesCSV'])->name('export.gages.csv');
        Route::get('export/gages/pdf', [\App\Http\Controllers\ExportController::class, 'exportGagesPDF'])->name('export.gages.pdf');
        Route::get('export/calibration-records/csv', [\App\Http\Controllers\ExportController::class, 'exportCalibrationRecordsCSV'])->name('export.calibration-records.csv');
        Route::get('export/calibration-records/pdf', [\App\Http\Controllers\ExportController::class, 'exportCalibrationRecordsPDF'])->name('export.calibration-records.pdf');
        
        // Admin routes (admin only)
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::get('/', [\App\Http\Controllers\AdminController::class, 'index'])->name('dashboard');
            Route::get('audit', [\App\Http\Controllers\AuditController::class, 'index'])->name('audit.index');
            Route::get('audit/{activity}', [\App\Http\Controllers\AuditController::class, 'show'])->name('audit.show');
            
            // User management routes
            Route::get('users', [\App\Http\Controllers\UserManagementController::class, 'index'])->name('users.index');
            Route::post('users', [\App\Http\Controllers\UserManagementController::class, 'store'])->name('users.store');
            Route::put('users/{user}', [\App\Http\Controllers\UserManagementController::class, 'update'])->name('users.update');
            Route::delete('users/{user}', [\App\Http\Controllers\UserManagementController::class, 'destroy'])->name('users.destroy');
            Route::post('users/{user}/suspend', [\App\Http\Controllers\UserManagementController::class, 'suspend'])->name('users.suspend');
            Route::post('users/{user}/reactivate', [\App\Http\Controllers\UserManagementController::class, 'reactivate'])->name('users.reactivate');
        });
    });
});

require __DIR__.'/auth.php';
