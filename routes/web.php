<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CaregiverController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\AgencyRegistrationController;
use App\Http\Controllers\SubscriptionController;

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
Route::get('/register-agency', [AgencyRegistrationController::class, 'showRegistrationForm'])->name('agency.register');
Route::post('/register-agency', [AgencyRegistrationController::class, 'store'])->name('agency.store');


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('clients', ClientController::class);
    Route::resource('caregivers', CaregiverController::class);

    // Scheduling Routes
    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');
    Route::resource('shifts', ScheduleController::class)->only(['store', 'show', 'update', 'destroy']);

    // **ADDED:** Subscription Routes
    Route::get('/subscription', [SubscriptionController::class, 'create'])->name('subscription.create');
    Route::post('/subscription', [SubscriptionController::class, 'store'])->name('subscription.store');
});

require __DIR__ . '/auth.php';
