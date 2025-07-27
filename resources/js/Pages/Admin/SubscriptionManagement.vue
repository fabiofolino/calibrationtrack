<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps({
    subscriptionDetails: Object,
    company: Object,
    stripeKey: String,
    setupIntent: Object,
});

const showExtendTrialModal = ref(false);
const showSubscribeModal = ref(false);
const stripe = ref(null);
const elements = ref(null);
const cardElement = ref(null);
const paymentProcessing = ref(false);

const extendTrialForm = useForm({
    days: 14,
});

const subscriptionForm = useForm({
    payment_method: '',
});

onMounted(async () => {
    if (props.stripeKey && props.setupIntent) {
        await loadStripe();
    }
});

const loadStripe = async () => {
    if (window.Stripe) {
        stripe.value = window.Stripe(props.stripeKey);
        setupStripeElements();
    } else {
        const script = document.createElement('script');
        script.src = 'https://js.stripe.com/v3/';
        script.onload = () => {
            stripe.value = window.Stripe(props.stripeKey);
            setupStripeElements();
        };
        document.head.appendChild(script);
    }
};

const setupStripeElements = () => {
    if (!stripe.value || !props.setupIntent) return;
    
    elements.value = stripe.value.elements({
        clientSecret: props.setupIntent.client_secret,
    });
    
    cardElement.value = elements.value.create('card');
};

const mountCardElement = () => {
    if (cardElement.value) {
        cardElement.value.mount('#admin-card-element');
    }
};

const unmountCardElement = () => {
    if (cardElement.value) {
        cardElement.value.unmount();
    }
};

const handleSubscribe = async () => {
    if (!stripe.value || !cardElement.value || !props.setupIntent) {
        alert('Payment system not ready. Please try again.');
        return;
    }
    
    paymentProcessing.value = true;
    
    try {
        const { error, setupIntent } = await stripe.value.confirmCardSetup(
            props.setupIntent.client_secret,
            {
                payment_method: {
                    card: cardElement.value,
                }
            }
        );

        if (error) {
            alert('Payment setup failed: ' + error.message);
        } else {
            subscriptionForm.payment_method = setupIntent.payment_method;
            subscriptionForm.post(route('admin.subscription.store'), {
                onSuccess: () => {
                    showSubscribeModal.value = false;
                    paymentProcessing.value = false;
                },
                onError: () => {
                    paymentProcessing.value = false;
                }
            });
        }
    } catch (e) {
        alert('An error occurred: ' + e.message);
        paymentProcessing.value = false;
    }
};

const cancelSubscription = () => {
    if (confirm('Are you sure you want to cancel the subscription? It will remain active until the end of the current billing period.')) {
        router.post(route('admin.subscription.cancel'));
    }
};

const resumeSubscription = () => {
    if (confirm('Are you sure you want to resume the subscription?')) {
        router.post(route('admin.subscription.resume'));
    }
};

const extendTrial = () => {
    extendTrialForm.post(route('admin.subscription.extend-trial'), {
        onSuccess: () => {
            showExtendTrialModal.value = false;
            extendTrialForm.reset();
        },
    });
};

const getStatusColor = (status) => {
    switch (status) {
        case 'active':
            return 'text-green-600 bg-green-100';
        case 'canceled':
        case 'incomplete':
        case 'past_due':
            return 'text-red-600 bg-red-100';
        case 'trialing':
            return 'text-blue-600 bg-blue-100';
        default:
            return 'text-gray-600 bg-gray-100';
    }
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Subscription Management" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Subscription Management</h1>
                            <p class="mt-2 text-gray-600 dark:text-gray-400">Manage your company's subscription and billing</p>
                        </div>
                        <Link :href="route('admin.dashboard')" 
                              class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                            ← Back to Admin Dashboard
                        </Link>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Subscription Status Card -->
                    <div class="lg:col-span-2">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Subscription Status</h2>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <!-- Current Status -->
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</span>
                                        <div v-if="subscriptionDetails.has_subscription" class="flex items-center space-x-2">
                                            <span :class="getStatusColor(subscriptionDetails.subscription_status)" 
                                                  class="px-2 py-1 text-xs font-semibold rounded-full">
                                                {{ subscriptionDetails.subscription_status || 'Active' }}
                                            </span>
                                            <span v-if="subscriptionDetails.cancel_at_period_end" 
                                                  class="px-2 py-1 text-xs font-semibold rounded-full text-orange-600 bg-orange-100">
                                                Canceling at period end
                                            </span>
                                        </div>
                                        <div v-else-if="subscriptionDetails.on_trial">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full text-blue-600 bg-blue-100">
                                                Trial Active
                                            </span>
                                        </div>
                                        <div v-else>
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full text-gray-600 bg-gray-100">
                                                No Subscription
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Trial Information -->
                                    <div v-if="subscriptionDetails.on_trial" class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Trial Ends</span>
                                        <span class="text-sm text-gray-900 dark:text-gray-100">
                                            {{ formatDate(subscriptionDetails.trial_ends_at) }}
                                        </span>
                                    </div>

                                    <!-- Subscription Dates -->
                                    <div v-if="subscriptionDetails.has_subscription && subscriptionDetails.subscription_created" 
                                         class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Subscription Started</span>
                                        <span class="text-sm text-gray-900 dark:text-gray-100">
                                            {{ formatDate(subscriptionDetails.subscription_created) }}
                                        </span>
                                    </div>

                                    <div v-if="subscriptionDetails.has_subscription && subscriptionDetails.current_period_end" 
                                         class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Next Billing Date</span>
                                        <span class="text-sm text-gray-900 dark:text-gray-100">
                                            {{ formatDate(subscriptionDetails.current_period_end) }}
                                        </span>
                                    </div>

                                    <!-- Gage Usage -->
                                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Gage Usage</span>
                                            <span class="text-sm text-gray-900 dark:text-gray-100">
                                                {{ subscriptionDetails.gage_count }} 
                                                <span v-if="!subscriptionDetails.has_subscription && !subscriptionDetails.on_trial">
                                                    / {{ subscriptionDetails.gage_limit }}
                                                </span>
                                                <span v-else>
                                                    (Unlimited)
                                                </span>
                                            </span>
                                        </div>
                                        <div v-if="!subscriptionDetails.has_subscription && !subscriptionDetails.on_trial" 
                                             class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full" 
                                                 :style="`width: ${(subscriptionDetails.gage_count / subscriptionDetails.gage_limit) * 100}%`">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Card -->
                    <div class="space-y-6">
                        <!-- Subscription Actions -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Actions</h3>
                            </div>
                            <div class="p-6 space-y-4">
                                <!-- Subscribe Button -->
                                <div v-if="!subscriptionDetails.has_subscription && !subscriptionDetails.on_trial">
                                    <button 
                                        @click="showSubscribeModal = true; $nextTick(() => mountCardElement())"
                                        class="w-full justify-center inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    >
                                        Subscribe Now
                                    </button>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 text-center">
                                        Start your subscription to unlock unlimited gages
                                    </p>
                                </div>

                                <!-- Trial Actions with Subscribe Option -->
                                <div v-if="subscriptionDetails.on_trial">
                                    <div class="space-y-2">
                                        <button 
                                            @click="showSubscribeModal = true; $nextTick(() => mountCardElement())"
                                            class="w-full justify-center inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                        >
                                            Subscribe Now
                                        </button>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                                            Subscribe before your trial ends
                                        </p>
                                        
                                        <button 
                                            @click="showExtendTrialModal = true"
                                            class="w-full justify-center inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                        >
                                            Extend Trial
                                        </button>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                                            Or extend the trial period
                                        </p>
                                    </div>
                                </div>

                                <!-- Active Subscription Actions -->
                                <div v-if="subscriptionDetails.has_subscription">
                                    <div v-if="subscriptionDetails.cancel_at_period_end" class="space-y-2">
                                        <SecondaryButton @click="resumeSubscription" class="w-full justify-center">
                                            Resume Subscription
                                        </SecondaryButton>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                                            Resume your cancelled subscription
                                        </p>
                                    </div>
                                    <div v-else class="space-y-2">
                                        <DangerButton @click="cancelSubscription" class="w-full justify-center">
                                            Cancel Subscription
                                        </DangerButton>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                                            Cancel at the end of billing period
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Company Info -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Company Info</h3>
                            </div>
                            <div class="p-6 space-y-3">
                                <div>
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Company Name</span>
                                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ company.name }}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Account Created</span>
                                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ formatDate(company.created_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Extend Trial Modal -->
        <Modal :show="showExtendTrialModal" @close="showExtendTrialModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Extend Trial Period</h2>
                <p class="mt-1 text-sm text-gray-600">
                    How many days would you like to extend the trial period?
                </p>

                <form @submit.prevent="extendTrial" class="mt-6 space-y-6">
                    <div>
                        <InputLabel for="days" value="Days to extend" />
                        <TextInput
                            id="days"
                            v-model.number="extendTrialForm.days"
                            type="number"
                            min="1"
                            max="90"
                            class="mt-1 block w-full"
                            required
                        />
                        <p class="mt-1 text-xs text-gray-500">Maximum 90 days</p>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <SecondaryButton @click="showExtendTrialModal = false">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton :disabled="extendTrialForm.processing">
                            Extend Trial
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Subscribe Modal -->
        <Modal :show="showSubscribeModal" @close="showSubscribeModal = false; unmountCardElement()">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Subscribe to CalibrationTrack</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Subscribe to unlock unlimited gages and all features.
                </p>

                <!-- Plan Details -->
                <div class="mt-4 bg-blue-50 dark:bg-blue-900 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100">Basic Plan</h3>
                    <p class="text-blue-700 dark:text-blue-200">$29/month</p>
                    <ul class="mt-2 text-sm text-blue-600 dark:text-blue-300">
                        <li>• Unlimited departments and gages</li>
                        <li>• Calibration tracking and records</li>
                        <li>• Export capabilities</li>
                        <li>• User management</li>
                        <li>• Audit logs</li>
                    </ul>
                </div>

                <form @submit.prevent="handleSubscribe" class="mt-6">
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Payment Information
                        </label>
                        <div id="admin-card-element" class="p-3 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700">
                            <!-- Stripe Elements will create form elements here -->
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <SecondaryButton 
                            type="button"
                            @click="showSubscribeModal = false; unmountCardElement()"
                        >
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton 
                            type="submit" 
                            :class="{ 'opacity-25': paymentProcessing }"
                            :disabled="paymentProcessing"
                        >
                            {{ paymentProcessing ? 'Processing...' : 'Start Subscription' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>