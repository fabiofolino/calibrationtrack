<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const props = defineProps({
    intent: Object,
    stripe_key: String,
});

const stripe = ref(null);
const elements = ref(null);
const cardElement = ref(null);
const paymentProcessing = ref(false);

const form = useForm({
    payment_method: '',
});

onMounted(async () => {
    // Load Stripe
    if (window.Stripe) {
        stripe.value = window.Stripe(props.stripe_key);
        setupStripeElements();
    } else {
        // Load Stripe script
        const script = document.createElement('script');
        script.src = 'https://js.stripe.com/v3/';
        script.onload = () => {
            stripe.value = window.Stripe(props.stripe_key);
            setupStripeElements();
        };
        document.head.appendChild(script);
    }
});

const setupStripeElements = () => {
    elements.value = stripe.value.elements({
        clientSecret: props.intent.client_secret,
    });
    
    cardElement.value = elements.value.create('card');
    cardElement.value.mount('#card-element');
};

const submit = async () => {
    if (!stripe.value || !cardElement.value) return;
    
    paymentProcessing.value = true;
    
    try {
        const { error, setupIntent } = await stripe.value.confirmCardSetup(
            props.intent.client_secret,
            {
                payment_method: {
                    card: cardElement.value,
                }
            }
        );

        if (error) {
            alert('Payment setup failed: ' + error.message);
        } else {
            form.payment_method = setupIntent.payment_method;
            form.post(route('subscription.store'));
        }
    } catch (e) {
        alert('An error occurred: ' + e.message);
    }
    
    paymentProcessing.value = false;
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Subscribe" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="max-w-md mx-auto">
                            <h1 class="text-2xl font-bold mb-6">Subscribe to CalibrationTrack</h1>
                            
                            <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-lg mb-6">
                                <h2 class="text-lg font-semibold text-blue-900 dark:text-blue-100">Basic Plan</h2>
                                <p class="text-blue-700 dark:text-blue-200">$29/month</p>
                                <ul class="mt-2 text-sm text-blue-600 dark:text-blue-300">
                                    <li>• Unlimited departments and gages</li>
                                    <li>• Calibration tracking and records</li>
                                    <li>• 14-day free trial</li>
                                </ul>
                            </div>

                            <form @submit.prevent="submit">
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Payment Information
                                    </label>
                                    <div id="card-element" class="p-3 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700">
                                        <!-- Stripe Elements will create form elements here -->
                                    </div>
                                </div>

                                <PrimaryButton 
                                    type="submit" 
                                    class="w-full"
                                    :class="{ 'opacity-25': paymentProcessing }"
                                    :disabled="paymentProcessing"
                                >
                                    {{ paymentProcessing ? 'Processing...' : 'Start Subscription' }}
                                </PrimaryButton>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>