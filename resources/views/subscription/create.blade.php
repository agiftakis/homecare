<x-guest-layout>
    <div x-data="subscriptionForm()" x-init="initStripe()">
        <h2 class="text-2xl font-bold text-center text-gray-800 dark:text-gray-200 mb-6">Subscribe to VitaLink</h2>

        <div class="mb-4 text-center">
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
                <x-text-input id="card-holder-name" class="block mt-1 w-full" type="text" required />
            </div>

            <div class="mt-4">
                <x-input-label value="Credit or debit card" />
                <div id="card-element" class="block mt-1 w-full p-3 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm bg-white dark:bg-gray-900"></div>
                <div id="card-errors" role="alert" class="text-red-500 text-sm mt-2"></div>
            </div>

            <div class="flex items-center justify-end mt-6">
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
                cardElement: null,
                initStripe() {
                    this.stripe = Stripe('{{ env("STRIPE_KEY") }}');
                    const elements = this.stripe.elements();
                    this.cardElement = elements.create('card');
                    this.cardElement.mount('#card-element');

                    const cardHolderName = document.getElementById('card-holder-name');
                    const cardButton = document.getElementById('card-button');
                    const clientSecret = cardButton.dataset.secret;
                    const paymentForm = document.getElementById('payment-form');

                    paymentForm.addEventListener('submit', async (e) => {
                        e.preventDefault();
                        cardButton.disabled = true;

                        const { setupIntent, error } = await this.stripe.confirmCardSetup(
                            clientSecret, {
                                payment_method: {
                                    card: this.cardElement,
                                    billing_details: { name: cardHolderName.value }
                                }
                            }
                        );

                        if (error) {
                            const errorDiv = document.getElementById('card-errors');
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

