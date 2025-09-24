<?php
// Updated bootstrap/app.php with enhanced error handling

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // The incorrect line has been removed from here.

        $middleware->alias([
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'superadmin' => \App\Http\Middleware\CheckSuperAdmin::class,
            'agency_admin' => \App\Http\Middleware\CheckAgencyAdmin::class,
            'timezone' => \App\Http\Middleware\SetTimezone::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        
        // Enhanced error reporting and logging
        $exceptions->reportable(function (Throwable $e) {
            // Log critical errors with context
            if (!$e instanceof ValidationException && !$e instanceof NotFoundHttpException) {
                $user = Auth::user();
                Log::error('Application Error', [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'user_id' => $user?->id,
                    'agency_id' => $user?->agency_id,
                    'url' => request()->fullUrl(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        });

        // Custom error responses for different exception types
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'The requested resource was not found.',
                    'error_code' => 'RESOURCE_NOT_FOUND'
                ], 404);
            }
            
            return response()->view('errors.404', [
                'exception' => $e,
                'title' => 'Page Not Found'
            ], 404);
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Authentication required.',
                    'error_code' => 'AUTHENTICATION_REQUIRED'
                ], 401);
            }
            
            return redirect()->guest(route('login'))->with('error', 'Please log in to access this page.');
        });

        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'You do not have permission to access this resource.',
                    'error_code' => 'ACCESS_DENIED'
                ], 403);
            }
            
            return response()->view('errors.403', [
                'exception' => $e,
                'title' => 'Access Denied'
            ], 403);
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'The requested record was not found or you do not have permission to access it.',
                    'error_code' => 'RECORD_NOT_FOUND'
                ], 404);
            }
            
            return response()->view('errors.404', [
                'exception' => $e,
                'title' => 'Record Not Found',
                'message' => 'The requested record was not found or you do not have permission to access it.'
            ], 404);
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'The provided data is invalid.',
                    'errors' => $e->errors(),
                    'error_code' => 'VALIDATION_FAILED'
                ], 422);
            }
            
            // For non-JSON requests, let Laravel handle validation errors normally
            return null;
        });

        // Generic server error handler
        $exceptions->render(function (Throwable $e, Request $request) {
            // Only handle 500 errors in production, let debug page show in development
            if (!app()->isProduction() || $e instanceof ValidationException || $e instanceof NotFoundHttpException) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'An unexpected error occurred. Please try again later.',
                    'error_code' => 'INTERNAL_SERVER_ERROR'
                ], 500);
            }
            
            return response()->view('errors.500', [
                'exception' => $e,
                'title' => 'Server Error'
            ], 500);
        });

    })->create();

