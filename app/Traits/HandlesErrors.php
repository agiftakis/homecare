<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Exception;

trait HandlesErrors
{
    /**
     * Handle exceptions with user-friendly messages and proper logging
     */
    protected function handleException(Exception $e, string $userMessage = 'An unexpected error occurred.', string $context = ''): JsonResponse|RedirectResponse
    {
        // Log the detailed error
        $this->logError($e, $context);

        // Return appropriate response based on request type
        if (request()->expectsJson()) {
            return response()->json([
                'message' => $userMessage,
                'error_code' => $this->getErrorCode($e),
                'success' => false
            ], $this->getHttpStatusCode($e));
        }

        return back()
            ->withInput()
            ->with('error', $userMessage);
    }

    /**
     * Handle database transaction with error handling
     */
    protected function handleDatabaseTransaction(callable $callback, string $successMessage = 'Operation completed successfully.', string $errorMessage = 'An error occurred while processing your request.')
    {
        try {
            DB::beginTransaction();

            $result = $callback();

            DB::commit();

            if (request()->expectsJson()) {
                return response()->json([
                    'message' => $successMessage,
                    'data' => $result,
                    'success' => true
                ]);
            }

            return back()->with('success', $successMessage);
        } catch (Exception $e) {
            DB::rollback();
            return $this->handleException($e, $errorMessage, 'database_transaction');
        }
    }

    /**
     * Handle file upload errors specifically
     */
    protected function handleFileUploadError(Exception $e, string $context = 'file_upload'): JsonResponse|RedirectResponse
    {
        $this->logError($e, $context);

        $userMessage = 'File upload failed. Please try again with a different file.';

        // Customize message based on error type
        if (str_contains($e->getMessage(), 'size')) {
            $userMessage = 'The uploaded file is too large. Please choose a smaller file.';
        } elseif (str_contains($e->getMessage(), 'type') || str_contains($e->getMessage(), 'extension')) {
            $userMessage = 'Invalid file type. Please upload a supported file format.';
        } elseif (str_contains($e->getMessage(), 'firebase') || str_contains($e->getMessage(), 'storage')) {
            $userMessage = 'File storage error. Please try uploading again.';
        }

        if (request()->expectsJson()) {
            return response()->json([
                'message' => $userMessage,
                'error_code' => 'FILE_UPLOAD_ERROR',
                'success' => false
            ], 422);
        }

        return back()
            ->withInput()
            ->with('error', $userMessage);
    }

    /**
     * Handle validation errors with enhanced feedback
     */
    protected function handleValidationError(array $errors, string $message = 'Please correct the highlighted errors and try again.'): JsonResponse|RedirectResponse
    {
        if (request()->expectsJson()) {
            return response()->json([
                'message' => $message,
                'errors' => $errors,
                'error_code' => 'VALIDATION_FAILED',
                'success' => false
            ], 422);
        }

        return back()
            ->withInput()
            ->withErrors($errors)
            ->with('error', $message);
    }

    /**
     * Log error with comprehensive context
     */
    protected function logError(Exception $e, string $context = ''): void
    {
        $errorContext = [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'context' => $context,
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString(),
        ];

        // Add user context if authenticated
        $user = Auth::user();
        if ($user) {
            $errorContext['user_id'] = $user->id;
            $errorContext['user_email'] = $user->email;
            $errorContext['user_role'] = $user->role;
            $errorContext['agency_id'] = $user->agency_id;
        }

        // Add request data (excluding sensitive fields)
        $requestData = request()->except(['password', 'password_confirmation', '_token', 'current_password']);
        if (!empty($requestData)) {
            $errorContext['request_data'] = $requestData;
        }

        Log::error("Application Error [{$context}]", $errorContext);
    }

    /**
     * Get appropriate HTTP status code for exception
     */
    protected function getHttpStatusCode(Exception $e): int
    {
        if ($e instanceof HttpException) {
            return $e->getStatusCode();
        }

        $exceptionClass = get_class($e);

        return match ($exceptionClass) {
            'Illuminate\Auth\AuthenticationException' => 401,
            'Illuminate\Auth\Access\AuthorizationException' => 403,
            'Illuminate\Database\Eloquent\ModelNotFoundException' => 404,
            'Illuminate\Validation\ValidationException' => 422,
            'Illuminate\Http\Exceptions\ThrottleRequestsException' => 429,
            default => 500
        };
    }

    /**
     * Get error code for client-side handling
     */
    protected function getErrorCode(Exception $e): string
    {
        $exceptionClass = get_class($e);

        return match ($exceptionClass) {
            'Illuminate\Auth\AuthenticationException' => 'AUTHENTICATION_REQUIRED',
            'Illuminate\Auth\Access\AuthorizationException' => 'ACCESS_DENIED',
            'Illuminate\Database\Eloquent\ModelNotFoundException' => 'RECORD_NOT_FOUND',
            'Illuminate\Validation\ValidationException' => 'VALIDATION_FAILED',
            'Illuminate\Http\Exceptions\ThrottleRequestsException' => 'RATE_LIMIT_EXCEEDED',
            'Illuminate\Database\QueryException' => 'DATABASE_ERROR',
            default => 'INTERNAL_SERVER_ERROR'
        };
    }

    /**
     * Success response helper
     */
    protected function successResponse(string $message, $data = null, int $statusCode = 200): JsonResponse|RedirectResponse
    {
        if (request()->expectsJson()) {
            $response = [
                'message' => $message,
                'success' => true
            ];

            if ($data !== null) {
                $response['data'] = $data;
            }

            return response()->json($response, $statusCode);
        }

        return back()->with('success', $message);
    }

    /**
     * Check if user has permission with better error handling
     */
    protected function authorizeWithError(string $ability, $model = null, string $errorMessage = 'You do not have permission to perform this action.'): void
    {
        try {
            $this->authorize($ability, $model);
        } catch (Exception $e) {
            if (request()->expectsJson()) {
                response()->json([
                    'message' => $errorMessage,
                    'error_code' => 'ACCESS_DENIED',
                    'success' => false
                ], 403)->send();
                exit;
            }

            abort(403, $errorMessage);
        }
    }
}