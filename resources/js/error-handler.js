/**
 * VitaLink Enhanced Error Handler
 * Provides better user feedback for JavaScript errors and AJAX failures
 */

class VitaLinkErrorHandler {
    constructor() {
        this.init();
        this.setupGlobalErrorHandlers();
        this.setupAjaxErrorHandlers();
    }

    init() {
        // Configure toastr if available
        if (typeof toastr !== 'undefined') {
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": true,
                "progressBar": true,
                "positionClass": "toast-bottom-right",
                "preventDuplicates": true,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };
        }
    }

    setupGlobalErrorHandlers() {
        // Global JavaScript error handler
        window.addEventListener('error', (event) => {
            console.error('JavaScript Error:', event.error);
            
            // Only show user-friendly message for certain types of errors
            if (this.shouldShowErrorToUser(event.error)) {
                this.showError('Something went wrong. Please refresh the page and try again.');
            }
            
            // Log error for debugging
            this.logError({
                type: 'javascript_error',
                message: event.error?.message || 'Unknown error',
                filename: event.filename,
                lineno: event.lineno,
                colno: event.colno,
                stack: event.error?.stack,
                url: window.location.href,
                userAgent: navigator.userAgent
            });
        });

        // Promise rejection handler
        window.addEventListener('unhandledrejection', (event) => {
            console.error('Unhandled Promise Rejection:', event.reason);
            
            if (this.shouldShowErrorToUser(event.reason)) {
                this.showError('An unexpected error occurred. Please try again.');
            }
            
            this.logError({
                type: 'promise_rejection',
                message: event.reason?.message || 'Promise rejected',
                stack: event.reason?.stack,
                url: window.location.href
            });
        });
    }

    setupAjaxErrorHandlers() {
        // jQuery AJAX error handler (if jQuery is available)
        if (typeof $ !== 'undefined') {
            $(document).ajaxError((event, xhr, settings, thrownError) => {
                this.handleAjaxError(xhr, settings, thrownError);
            });
        }

        // Fetch API error wrapper
        const originalFetch = window.fetch;
        window.fetch = async (...args) => {
            try {
                const response = await originalFetch(...args);
                
                if (!response.ok) {
                    this.handleFetchError(response, args[0]);
                }
                
                return response;
            } catch (error) {
                this.handleFetchError(null, args[0], error);
                throw error;
            }
        };
    }

    handleAjaxError(xhr, settings, thrownError) {
        const status = xhr.status;
        const url = settings.url;

        // Parse error response
        let errorMessage = 'An unexpected error occurred.';
        let errorDetails = null;

        try {
            const response = JSON.parse(xhr.responseText);
            if (response.message) {
                errorMessage = response.message;
            }
            if (response.errors) {
                errorDetails = response.errors;
            }
        } catch (e) {
            // Response is not JSON, use default message
        }

        // Show appropriate error based on status
        switch (status) {
            case 400:
                this.showError('Invalid request. Please check your input and try again.');
                break;
            case 401:
                this.showError('Your session has expired. Please log in again.');
                setTimeout(() => {
                    window.location.href = '/login';
                }, 2000);
                break;
            case 403:
                this.showError('You do not have permission to perform this action.');
                break;
            case 404:
                this.showError('The requested resource was not found.');
                break;
            case 422:
                if (errorDetails) {
                    this.showValidationErrors(errorDetails);
                } else {
                    this.showError(errorMessage);
                }
                break;
            case 429:
                this.showError('Too many requests. Please wait a moment and try again.');
                break;
            case 500:
                this.showError('A server error occurred. Our team has been notified.');
                break;
            case 503:
                this.showError('Service temporarily unavailable. Please try again later.');
                break;
            default:
                if (status === 0) {
                    this.showError('Network error. Please check your internet connection.');
                } else {
                    this.showError(errorMessage);
                }
        }

        // Log error details
        this.logError({
            type: 'ajax_error',
            status: status,
            url: url,
            message: errorMessage,
            response: xhr.responseText,
            thrownError: thrownError
        });
    }

    handleFetchError(response, url, error = null) {
        if (error) {
            // Network or other fetch error
            this.showError('Network error. Please check your internet connection.');
            this.logError({
                type: 'fetch_error',
                url: url,
                message: error.message,
                stack: error.stack
            });
        } else if (response) {
            // HTTP error response
            this.handleAjaxError({
                status: response.status,
                responseText: ''
            }, { url: url }, '');
        }
    }

    showError(message) {
        if (typeof toastr !== 'undefined') {
            toastr.error(message);
        } else {
            // Fallback to alert if toastr is not available
            alert('Error: ' + message);
        }
    }

    showSuccess(message) {
        if (typeof toastr !== 'undefined') {
            toastr.success(message);
        } else {
            console.log('Success: ' + message);
        }
    }

    showWarning(message) {
        if (typeof toastr !== 'undefined') {
            toastr.warning(message);
        } else {
            console.warn('Warning: ' + message);
        }
    }

    showValidationErrors(errors) {
        if (typeof errors === 'object') {
            Object.keys(errors).forEach(field => {
                const fieldErrors = Array.isArray(errors[field]) ? errors[field] : [errors[field]];
                fieldErrors.forEach(error => {
                    this.showError(error);
                });
            });
        } else {
            this.showError('Please correct the highlighted errors and try again.');
        }
    }

    shouldShowErrorToUser(error) {
        // Don't show errors for script loading failures or third-party scripts
        if (error && error.message) {
            const message = error.message.toLowerCase();
            return !message.includes('script error') && 
                   !message.includes('non-error promise rejection') &&
                   !message.includes('loading chunk') &&
                   !message.includes('network error');
        }
        return true;
    }

    logError(errorData) {
        // Send error to server for logging (optional)
        if (window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
            fetch('/api/log-client-error', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({
                    ...errorData,
                    timestamp: new Date().toISOString(),
                    user_agent: navigator.userAgent,
                    url: window.location.href
                })
            }).catch(() => {
                // Silently fail if logging endpoint is not available
            });
        }
        
        // Always log to console for development
        console.error('Client Error Logged:', errorData);
    }

    // Public methods for manual error handling
    handleFormSubmissionError(response) {
        if (response.status === 422 && response.errors) {
            this.showValidationErrors(response.errors);
        } else {
            this.showError(response.message || 'An error occurred while processing your request.');
        }
    }

    handleFileUploadError(error) {
        if (error.code === 'FILE_TOO_LARGE') {
            this.showError('The selected file is too large. Please choose a smaller file.');
        } else if (error.code === 'INVALID_FILE_TYPE') {
            this.showError('Invalid file type. Please select a supported file format.');
        } else {
            this.showError('File upload failed. Please try again.');
        }
    }

    handleFirebaseError(error) {
        console.error('Firebase Error:', error);
        this.showError('File storage error. Please try uploading again.');
        
        this.logError({
            type: 'firebase_error',
            message: error.message,
            code: error.code
        });
    }
}

// Initialize error handler when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.vitaLinkErrorHandler = new VitaLinkErrorHandler();
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = VitaLinkErrorHandler;
}