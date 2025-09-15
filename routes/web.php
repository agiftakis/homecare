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
use App\Http\Controllers\PasswordSetupController;
use App\Http\Controllers\VisitVerificationController;
// ✅ SETTINGS PAGE: Import the new controller we will create.
use App\Http\Controllers\AgencySettingsController;


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

//new routes for user- client or caregiver- registration password setup
Route::get('/setup-password/{token}', [PasswordSetupController::class, 'show'])->name('password.setup.show');
Route::post('/setup-password', [PasswordSetupController::class, 'store'])->name('password.setup.store');

// ✅ TIMEZONE FIX: Apply the 'timezone' middleware to all authenticated routes.
// Authenticated Routes
Route::middleware(['auth', 'timezone'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ✅ --- START SECURITY FIX ---
    // Routes that are ONLY accessible to Agency Admins.
    // The 'agency_admin' middleware we created now protects this group.
    Route::middleware('agency_admin')->group(function () {
        Route::resource('clients', ClientController::class);
        Route::resource('caregivers', CaregiverController::class);
        // This route is also protected as it is part of caregiver management.
        Route::post('/caregivers/{caregiver}/resend-onboarding', [CaregiverController::class, 'resendOnboardingLink'])->name('caregivers.resendOnboarding');
        // ✅ NEW: Client onboarding resend route
        Route::post('/clients/{client}/resend-onboarding', [ClientController::class, 'resendOnboardingLink'])->name('clients.resendOnboarding');

        // ✅ SETTINGS PAGE: Add the new routes for the agency settings page.
        Route::get('/settings', [AgencySettingsController::class, 'edit'])->name('settings.edit');
        Route::patch('/settings', [AgencySettingsController::class, 'update'])->name('settings.update');
    });
    // ✅ --- END SECURITY FIX ---


    // Scheduling Routes (Accessible to Admins, Caregivers, and now Clients)
    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');
    // ✅ NEW: Client-specific read-only schedule route
    Route::get('/my-schedule', [ScheduleController::class, 'clientSchedule'])->name('schedule.client');
    Route::resource('shifts', ScheduleController::class)->only(['store', 'show', 'update', 'destroy']);

    // Subscription Routes
    Route::get('/subscription', [SubscriptionController::class, 'create'])->name('subscription.create');
    Route::post('/subscription', [SubscriptionController::class, 'store'])->name('subscription.store');

    // Visit Verification Routes
    Route::get('/shifts/{shift}/verify', [VisitVerificationController::class, 'show'])->name('visits.show');
    Route::post('/shifts/{shift}/clock-in', [VisitVerificationController::class, 'clockIn'])->name('visits.clockin');
    Route::post('/visits/{visit}/clock-out', [VisitVerificationController::class, 'clockOut'])->name('visits.clockout');
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