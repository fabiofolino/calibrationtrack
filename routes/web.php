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

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'company.scope'])
    ->name('dashboard');

Route::middleware(['auth', 'company.scope'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Subscription routes
    Route::get('/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscription.subscribe');
    Route::post('/subscribe', [SubscriptionController::class, 'store'])->name('subscription.store');
    
    // Resource routes with subscription middleware
    Route::middleware('subscription')->group(function () {
        Route::resource('departments', \App\Http\Controllers\DepartmentController::class);
        Route::resource('gages', \App\Http\Controllers\GageController::class);
        Route::resource('calibration-records', \App\Http\Controllers\CalibrationRecordController::class);
        
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
            Route::get('audit', [\App\Http\Controllers\AuditController::class, 'index'])->name('audit.index');
            Route::get('audit/{activity}', [\App\Http\Controllers\AuditController::class, 'show'])->name('audit.show');
        });
    });
});

require __DIR__.'/auth.php';
