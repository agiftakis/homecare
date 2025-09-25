<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeWebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Stripe Webhook Route - This is stateless and not subject to CSRF protection
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])
    ->name('stripe.webhook');

// Example authenticated API route (if you need it in the future)
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });