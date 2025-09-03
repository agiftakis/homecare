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
});

// Super Admin Routes
Route::middleware(['auth', 'superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'index'])->name('dashboard');
});

require __DIR__ . '/auth.php';
