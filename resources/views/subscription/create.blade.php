<x-guest-layout>
    <div x-data="subscriptionForm()" x-init="initStripe()">
        <h2 class="text-2xl font-bold text-center text-gray-800 dark:text-gray-200 mb-2">Subscribe to VitaLink</h2>

        <div class="mb-6 text-center">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300">
                You've selected the <span class="font-bold mx-1">{{ ucfirst($plan) }}</span> Plan
            </span>
        </div>

        <!-- Stripe Credit Card Form -->
        <form id="payment-form" action="{{ route('subscription.store') }}" method="POST">
            @csrf
            <input type="hidden" name="plan" value="{{ $plan }}">
            <input type="hidden" name="payment_method" x-ref="payment_method">

            <div>
                <x-input-label for="card-holder-name" value="Card Holder Name" />
                <x-text-input id="card-holder-name" class="block mt-1 w-full" type="text" required placeholder="Full Name as on Card" />
            </div>

            <div class="mt-4">
                <x-input-label value="Card Number" />
                <!-- YOUR FIX APPLIED HERE: Changed dark:bg-gray-900 to bg-white for readability -->
                <div class="mt-1 p-3 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm bg-white">
                    <div id="card-number-element"></div>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-4">
                <div>
                    <x-input-label value="Expiration Date" />
                    <!-- YOUR FIX APPLIED HERE: Changed dark:bg-gray-900 to bg-white for readability -->
                    <div class="mt-1 p-3 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm bg-white">
                        <div id="card-expiry-element"></div>
                    </div>
                </div>
                <div>
                    <x-input-label value="CVC" />
                    <!-- YOUR FIX APPLIED HERE: Changed dark:bg-gray-900 to bg-white for readability -->
                    <div class="mt-1 p-3 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm bg-white">
                        <div id="card-cvc-element"></div>
                    </div>
                </div>
            </div>

            <div id="card-errors" role="alert" class="text-red-500 text-sm mt-4 min-h-[1.25rem]"></div>

            <div class="flex items-center justify-end mt-4">
                <x-primary-button id="card-button" data-secret="{{ $intent->client_secret }}">
                    {{ __('Subscribe Now') }}
                </x-primary-button>
            </div>
        </form>
    </div>

    <!-- Stripe.js and form handling script -->
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        function subscriptionForm() {
            return {
                stripe: null,
                initStripe() {
                    this.stripe = Stripe('{{ env("STRIPE_KEY") }}');
                    
                    // **THE FIX:** Removed the complex appearance object. Your HTML fix is better.
                    // We will use Stripe's default 'stripe' theme which works perfectly on a white background.
                    const elements = this.stripe.elements();

                    const cardNumber = elements.create('cardNumber');
                    cardNumber.mount('#card-number-element');

                    const cardExpiry = elements.create('cardExpiry');
                    cardExpiry.mount('#card-expiry-element');

                    const cardCvc = elements.create('cardCvc');
                    cardCvc.mount('#card-cvc-element');

                    const cardHolderName = document.getElementById('card-holder-name');
                    const cardButton = document.getElementById('card-button');
                    const clientSecret = cardButton.dataset.secret;
                    const paymentForm = document.getElementById('payment-form');
                    const errorDiv = document.getElementById('card-errors');

                    paymentForm.addEventListener('submit', async (e) => {
                        e.preventDefault();
                        cardButton.disabled = true;
                        errorDiv.textContent = '';

                        const { setupIntent, error } = await this.stripe.confirmCardSetup(
                            clientSecret, {
                                payment_method: {
                                    card: cardNumber,
                                    billing_details: { name: cardHolderName.value }
                                }
                            }
                        );

                        if (error) {
                            errorDiv.textContent = error.message;
                            cardButton.disabled = false;
                        } else {
                            this.$refs.payment_method.value = setupIntent.payment_method;
                            paymentForm.submit();
                        }
                    });
                }
            }
        }
    </script>
</x-guest-layout>

