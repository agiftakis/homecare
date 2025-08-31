<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CaregiverController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\Auth\AgencyRegistrationController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');

Route::get('/register-agency', [AgencyRegistrationController::class, 'showRegistrationForm'])->name('agency.register');

Route::post('/register-agency', [AgencyRegistrationController::class, 'store'])->name('agency.store');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ADD THIS LINE FOR CLIENTS
    Route::resource('clients', ClientController::class);
    //ADD THIS FOR CAREGIVERS
    Route::resource('caregivers', CaregiverController::class);

    // Scheduling Routes
    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');
    Route::post('/shifts', [ScheduleController::class, 'store'])->name('shifts.store');
    Route::put('/shifts/{shift}', [ScheduleController::class, 'update'])->name('shifts.update'); // <-- Add this
    Route::delete('/shifts/{shift}', [ScheduleController::class, 'destroy'])->name('shifts.destroy'); // <-- Add this
    // We will add update and destroy routes later
});

require __DIR__ . '/auth.php';
