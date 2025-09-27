{{-- Success Banner Component with Alpine.js --}}
<div 
    x-data="successBanner()" 
    x-show="showBanner" 
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform -translate-y-2"
    x-transition:enter-end="opacity-100 transform translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 transform translate-y-0"
    x-transition:leave-end="opacity-0 transform -translate-y-2"
    class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg border border-green-600"
    style="display: none;"
>
    <div class="flex items-center space-x-3">
        <!-- Success Icon -->
        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        
        <!-- Success Message -->
        <span x-text="message" class="font-medium text-lg"></span>
    </div>
</div>

<script>
function successBanner() {
    return {
        showBanner: false,
        message: '',
        redirectUrl: '',
        
        init() {
            // Check for flashed success message
            @if(session('success_message'))
                this.message = '{{ session('success_message') }}';
                this.redirectUrl = '{{ session('redirect_to', route('dashboard')) }}';
                this.displayBanner();
            @endif
        },
        
        displayBanner() {
            this.showBanner = true;
            
            // Hide banner and redirect after 1.5 seconds
            setTimeout(() => {
                this.showBanner = false;
                
                // Wait for fade out animation to complete before redirecting
                setTimeout(() => {
                    if (this.redirectUrl) {
                        window.location.href = this.redirectUrl;
                    }
                }, 200); // Match the leave transition duration
                
            }, 1500); // 1.5 seconds display time
        }
    }
}
</script>