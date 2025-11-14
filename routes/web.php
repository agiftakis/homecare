<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CaregiverController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\AgencyRegistrationController; // Still needed for now, leave as is
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\PasswordSetupController;
use App\Http\Controllers\VisitVerificationController;
use App\Http\Controllers\AgencySettingsController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalesContactController; // <-- ADDED


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// --- NEW SALES CONTACT ROUTES ---
Route::get('/contact-us', [SalesContactController::class, 'showContactForm'])->name('sales.contact');
Route::post('/contact-us', [SalesContactController::class, 'submitContactForm'])->name('sales.contact.submit');
// --- END NEW SALES CONTACT ROUTES ---


// Publicly accessible routes
// Route::get('/pricing', [PricingController::class, 'index'])->name('pricing'); // Removed Stripe route
// Route::get('/register-agency', [AgencyRegistrationController::class, 'create'])->name('agency.register'); // <-- COMMENTED OUT
// Route::post('/register-agency', [AgencyRegistrationController::class, 'store'])->name('agency.store'); // <-- COMMENTED OUT

//new routes for user- client or caregiver- registration password setup
Route::get('/setup-password/{token}', [PasswordSetupController::class, 'show'])->name('password.setup.show');
Route::post('/setup-password', [PasswordSetupController::class, 'store'])->name('password.setup.store');

// ✅ UPDATED: Main authenticated group, still protected by our modified 'subscription' middleware.
// Authenticated Routes
Route::middleware(['auth', 'timezone', 'subscription'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- START SECURITY FIX ---
    // Routes that are ONLY accessible to Agency Admins.
    // The 'agency_admin' middleware we created now protects this group.
    Route::middleware('agency_admin')->group(function () {
        // Route::get('/clients/check-limit', [ClientController::class, 'checkLimit'])->name('clients.checkLimit'); // Removed client limit route
        Route::resource('clients', ClientController::class);

        Route::resource('caregivers', CaregiverController::class);
        // This route is also protected as it is part of caregiver management.
        Route::post('/caregivers/{caregiver}/resend-onboarding', [CaregiverController::class, 'resendOnboardingLink'])->name('caregivers.resendOnboarding');
        // ✅ NEW: Client onboarding resend route
        Route::post('/clients/{client}/resend-onboarding', [ClientController::class, 'resendOnboardingLink'])->name('clients.resendOnboarding');

        // ✅ SETTINGS PAGE: Add the new routes for the agency settings page.
        Route::get('/settings', [AgencySettingsController::class, 'edit'])->name('settings.edit');
        Route::patch('/settings', [AgencySettingsController::class, 'update'])->name('settings.update');

        // ✅ NEW: Reporting Routes
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/operational', [ReportController::class, 'operationalDashboard'])->name('operational');
            Route::get('/revenue', [ReportController::class, 'revenueDashboard'])->name('revenue');
            // ✅ NEW: Export route for operational report
            Route::get('/operational/export', [ReportController::class, 'exportOperationalReport'])->name('operational.export');
        });

        // ✅ FIXED: Routes for managing caregiver notes - corrected URL structure
        Route::patch('/clients/notes/{visit}', [ClientController::class, 'updateNote'])->name('clients.notes.update');
        Route::delete('/clients/notes/{visit}', [ClientController::class, 'destroyNote'])->name('clients.notes.destroy');

        // Route::get('/subscription/manage', [SubscriptionController::class, 'manage'])->name('subscription.manage'); // Removed Stripe route

        // ✅ CRITICAL FIX: Custom invoice routes MUST be defined BEFORE Route::resource()
        // This ensures Laravel matches specific routes before trying generic resource routes
        Route::post('/invoices/generate', [InvoiceController::class, 'generate'])->name('invoices.generate');
        Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
        Route::post('/invoices/{invoice}/send', [InvoiceController::class, 'sendInvoice'])->name('invoices.send'); // PRESERVED ROUTE FOR EMAILING
        Route::post('/invoices/{invoice}/mark-as-sent', [InvoiceController::class, 'markAsSent'])->name('invoices.markAsSent');
        Route::post('/invoices/{invoice}/mark-as-paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.markAsPaid');
        Route::post('/invoices/{invoice}/void', [InvoiceController::class, 'voidInvoice'])->name('invoices.void');
        Route::post('/invoices/{invoice}/reissue', [InvoiceController::class, 'reissueInvoice'])->name('invoices.reissue');

        // Resource route comes AFTER custom routes
        Route::resource('invoices', InvoiceController::class);

        // ✅ NEW: API route for unbilled visits (AJAX endpoint)
        Route::post('/api/unbilled-visits', [InvoiceController::class, 'getUnbilledVisits'])->name('api.unbilled-visits');
    });
    // ✅ --- END SECURITY FIX ---


    // Scheduling Routes (Accessible to Admins, Caregivers, and now Clients)
    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');
    // ✅ NEW: Client-specific read-only schedule route
    Route::get('/my-schedule', [ScheduleController::class, 'clientSchedule'])->name('schedule.client');
    Route::resource('shifts', ScheduleController::class)->only(['store', 'show', 'update', 'destroy']);

    // Subscription Routes
    // Route::get('/subscription', [SubscriptionController::class, 'create'])->name('subscription.create'); // Removed Stripe route
    // Route::post('/subscription', [SubscriptionController::class, 'store'])->name('subscription.store'); // Removed Stripe route

    // Visit Verification Routes
    Route::get('/shifts/{shift}/verify', [VisitVerificationController::class, 'show'])->name('visits.show');
    Route::post('/shifts/{shift}/clock-in', [VisitVerificationController::class, 'clockIn'])->name('visits.clockin');
    Route::post('/visits/{visit}/clock-out', [VisitVerificationController::class, 'clockOut'])->name('visits.clockout');
});

// ✅ NEW: Route for locked-out agencies.
// This route MUST be outside the 'subscription' middleware group to avoid a redirect loop.
// It still requires 'auth' because only logged-in (but non-activated) users can see it.
Route::middleware(['auth', 'timezone'])->group(function () {
    Route::get('/subscription/required', [SubscriptionController::class, 'required'])->name('subscription.required');
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
    // START: NEW ROUTES FOR AGENCY MANAGEMENT
    Route::get('/agencies', [SuperAdminController::class, 'agenciesIndex'])->name('agencies.index');
    Route::get('/agencies/create', [SuperAdminController::class, 'agencyCreate'])->name('agencies.create');
    Route::post('/agencies', [SuperAdminController::class, 'agencyStore'])->name('agencies.store');
    Route::get('/agencies/{agency}/edit', [SuperAdminController::class, 'agencyEdit'])->name('agencies.edit');
    Route::patch('/agencies/{agency}', [SuperAdminController::class, 'agencyUpdate'])->name('agencies.update');
    // END: NEW ROUTES FOR AGENCY MANAGEMENT
    Route::delete('/agencies/{agency}', [SuperAdminController::class, 'destroyAgency'])->name('agencies.destroy');

    // Schedule Management Routes for SuperAdmin
    Route::get('/schedule', [SuperAdminController::class, 'scheduleIndex'])->name('schedule.index');

    // ✅ NEW: Agency suspension toggle
    Route::post('/agencies/{agency}/toggle-suspension', [SuperAdminController::class, 'toggleSuspension'])->name('agencies.toggleSuspension');
});

require __DIR__ . '/auth.php';
