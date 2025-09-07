<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CaregiverController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\AgencyRegistrationController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SuperAdminController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// Publicly accessible routes
Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');
Route::get('/register-agency', [AgencyRegistrationController::class, 'create'])->name('agency.register');
Route::post('/register-agency', [AgencyRegistrationController::class, 'store'])->name('agency.store');

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('clients', ClientController::class);
    Route::resource('caregivers', CaregiverController::class);

    // Scheduling Routes
    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');
    Route::resource('shifts', ScheduleController::class)->only(['store', 'show', 'update', 'destroy']);

    // Subscription Routes
    Route::get('/subscription', [SubscriptionController::class, 'create'])->name('subscription.create');
    Route::post('/subscription', [SubscriptionController::class, 'store'])->name('subscription.store');

    // ✅ CORRECT PLACEMENT FOR VISIT ROUTES
    // Visit Verification Routes
    Route::get('/shifts/{shift}/verify', [\App\Http\Controllers\VisitVerificationController::class, 'show'])->name('visits.show');
    Route::post('/shifts/{shift}/clock-in', [\App\Http\Controllers\VisitVerificationController::class, 'clockIn'])->name('visits.clockin');
    Route::post('/visits/{visit}/clock-out', [\App\Http\Controllers\VisitVerificationController::class, 'clockOut'])->name('visits.clockout');
});


// Super Admin Routes
Route::middleware(['auth', 'superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'index'])->name('dashboard');

    // Client Management Routes for SuperAdmin
    Route::get('/clients', [SuperAdminController::class, 'clientsIndex'])->name('clients.index');
    Route::get('/clients/{client}', [SuperAdminController::class, 'clientShow'])->name('clients.show');
    Route::put('/clients/{client}', [SuperAdminController::class, 'clientUpdate'])->name('clients.update');
    Route::delete('/clients/{client}', [SuperAdminController::class, 'clientDestroy'])->name('clients.destroy');

    // Caregiver Management Routes for SuperAdmin
    Route::get('/caregivers', [SuperAdminController::class, 'caregiversIndex'])->name('caregivers.index');
    Route::get('/caregivers/{caregiver}', [SuperAdminController::class, 'caregiverShow'])->name('caregivers.show');
    Route::put('/caregivers/{caregiver}', [SuperAdminController::class, 'caregiverUpdate'])->name('caregivers.update');
    Route::delete('/caregivers/{caregiver}', [SuperAdminController::class, 'caregiverDestroy'])->name('caregivers.destroy');

    // Agency Management Routes for SuperAdmin
    Route::delete('/agencies/{agency}', [SuperAdminController::class, 'destroyAgency'])->name('agencies.destroy');

    // Schedule Management Routes for SuperAdmin
    Route::get('/schedule', [SuperAdminController::class, 'scheduleIndex'])->name('schedule.index');
});

require __DIR__ . '/auth.php';
