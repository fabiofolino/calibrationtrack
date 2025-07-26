<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    gage: Object,
});

// Checkout form
const showCheckoutForm = ref(false);
const checkoutForm = useForm({
    notes: '',
});

const submitCheckout = () => {
    checkoutForm.post(route('gages.checkout', props.gage.id), {
        onSuccess: () => {
            showCheckoutForm.value = false;
            checkoutForm.reset();
        }
    });
};

// Checkin form
const showCheckinForm = ref(false);
const checkinForm = useForm({
    notes: '',
});

const submitCheckin = () => {
    checkinForm.post(route('gages.checkin', props.gage.id), {
        onSuccess: () => {
            showCheckinForm.value = false;
            checkinForm.reset();
        }
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Gage: ${gage.gage_id}`" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center">
                                <Link 
                                    :href="route('gages.index')"
                                    class="text-blue-600 hover:text-blue-800 mr-4"
                                >
                                    ← Back to Gages
                                </Link>
                                <h1 class="text-2xl font-bold">{{ gage.gage_id }} - {{ gage.description || 'No Description' }}</h1>
                            </div>
                            <div class="flex space-x-2">
                                <Link :href="route('gages.edit', gage.id)">
                                    <PrimaryButton>Edit Gage</PrimaryButton>
                                </Link>
                                <Link :href="route('calibration-records.create', { gage_id: gage.id })">
                                    <PrimaryButton>Add Calibration</PrimaryButton>
                                </Link>
                                <button 
                                    v-if="!gage.is_checked_out"
                                    @click="showCheckoutForm = true"
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    Check Out
                                </button>
                                <button 
                                    v-if="gage.is_checked_out"
                                    @click="showCheckinForm = true"
                                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    Check In
                                </button>
                            </div>
                        </div>

                        <!-- Gage Details -->
                        <div class="grid md:grid-cols-2 gap-6 mb-8">
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                                <h2 class="text-lg font-semibold mb-4">Gage Information</h2>
                                <div class="space-y-3">
                                    <div>
                                        <span class="font-medium">Gage ID:</span>
                                        <span class="ml-2">{{ gage.gage_id }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Description:</span>
                                        <span class="ml-2">{{ gage.description || 'N/A' }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Model:</span>
                                        <span class="ml-2">{{ gage.model || 'N/A' }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Manufacturer:</span>
                                        <span class="ml-2">{{ gage.manufacturer || 'N/A' }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Serial Number:</span>
                                        <span class="ml-2">{{ gage.serial_number }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Location:</span>
                                        <span class="ml-2">{{ gage.location }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Custodian:</span>
                                        <span class="ml-2">{{ gage.custodian }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Department:</span>
                                        <span class="ml-2">{{ gage.department.name }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Calibration Frequency:</span>
                                        <span class="ml-2">{{ gage.frequency_days }} days</span>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                                <h2 class="text-lg font-semibold mb-4">Current Status</h2>
                                <div class="space-y-3">
                                    <!-- Checkout Status -->
                                    <div>
                                        <span class="font-medium">Checkout Status:</span>
                                        <span v-if="gage.is_checked_out" class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Checked Out
                                        </span>
                                        <span v-else class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Available
                                        </span>
                                    </div>
                                    <div v-if="gage.current_checkout">
                                        <span class="font-medium">Checked out by:</span>
                                        <span class="ml-2">{{ gage.current_checkout.user.name }}</span>
                                    </div>
                                    <div v-if="gage.current_checkout">
                                        <span class="font-medium">Checked out at:</span>
                                        <span class="ml-2">{{ new Date(gage.current_checkout.checked_out_at).toLocaleString() }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Checkout Forms -->
                        <div v-if="showCheckoutForm" class="mb-8 bg-blue-50 dark:bg-blue-900 rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-4">Check Out Gage</h3>
                            <form @submit.prevent="submitCheckout" class="max-w-md">
                                <div class="mb-4">
                                    <InputLabel for="checkout_notes" value="Notes (optional)" />
                                    <textarea
                                        id="checkout_notes"
                                        v-model="checkoutForm.notes"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                        rows="3"
                                        placeholder="Purpose of checkout, expected return date, etc."
                                    ></textarea>
                                </div>
                                <div class="flex items-center gap-4">
                                    <PrimaryButton 
                                        :class="{ 'opacity-25': checkoutForm.processing }"
                                        :disabled="checkoutForm.processing"
                                    >
                                        Confirm Check Out
                                    </PrimaryButton>
                                    <button 
                                        type="button"
                                        @click="showCheckoutForm = false"
                                        class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div v-if="showCheckinForm" class="mb-8 bg-green-50 dark:bg-green-900 rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-4">Check In Gage</h3>
                            <form @submit.prevent="submitCheckin" class="max-w-md">
                                <div class="mb-4">
                                    <InputLabel for="checkin_notes" value="Return Notes (optional)" />
                                    <textarea
                                        id="checkin_notes"
                                        v-model="checkinForm.notes"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                        rows="3"
                                        placeholder="Condition of gage, issues encountered, etc."
                                    ></textarea>
                                </div>
                                <div class="flex items-center gap-4">
                                    <PrimaryButton 
                                        :class="{ 'opacity-25': checkinForm.processing }"
                                        :disabled="checkinForm.processing"
                                    >
                                        Confirm Check In
                                    </PrimaryButton>
                                    <button 
                                        type="button"
                                        @click="showCheckinForm = false"
                                        class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Calibration Status -->
                        <div class="grid md:grid-cols-2 gap-6 mb-8">
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                                <h2 class="text-lg font-semibold mb-4">Calibration Status</h2>
                                <div class="space-y-3">
                                    <div v-if="gage.calibration_records.length > 0">
                                        <span class="font-medium">Last Calibration:</span>
                                        <span class="ml-2">{{ new Date(gage.calibration_records[0].calibrated_at).toLocaleDateString() }}</span>
                                    </div>
                                    <div v-else>
                                        <span class="text-yellow-600 dark:text-yellow-400 font-medium">
                                            No calibrations recorded
                                        </span>
                                    </div>
                                    <div v-if="gage.next_due_date">
                                        <span class="font-medium">Next Due Date:</span>
                                        <span class="ml-2">{{ new Date(gage.next_due_date).toLocaleDateString() }}</span>
                                    </div>
                                    <div v-if="gage.status">
                                        <span class="font-medium">Status:</span>
                                        <span 
                                            class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                            :class="{
                                                'bg-red-100 text-red-800': gage.status === 'overdue',
                                                'bg-yellow-100 text-yellow-800': gage.status === 'due_soon',
                                                'bg-green-100 text-green-800': gage.status === 'on_schedule',
                                                'bg-gray-100 text-gray-800': gage.status === 'unknown'
                                            }"
                                        >
                                            {{ gage.status === 'overdue' ? 'Overdue' : 
                                               gage.status === 'due_soon' ? 'Due Soon' : 
                                               gage.status === 'on_schedule' ? 'On Schedule' : 'Unknown' }}
                                            <span v-if="gage.days_until_due !== null" class="ml-1">
                                                ({{ gage.days_until_due < 0 ? Math.abs(gage.days_until_due) + ' days ago' : gage.days_until_due + ' days' }})
                                            </span>
                                        </span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Total Calibrations:</span>
                                        <span class="ml-2">{{ gage.calibration_records.length }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                                <h2 class="text-lg font-semibold mb-4">Actions</h2>
                                <div class="space-y-3">
                                    <Link :href="route('calibration-records.create', { gage_id: gage.id })">
                                        <PrimaryButton class="w-full">Add Calibration Record</PrimaryButton>
                                    </Link>
                                    <Link :href="route('gages.checkout-history', gage.id)">
                                        <PrimaryButton class="w-full">View Checkout History</PrimaryButton>
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Calibration Records -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h2 class="text-lg font-semibold">Calibration History</h2>
                                <Link 
                                    :href="route('calibration-records.index', { gage_id: gage.id })"
                                    class="text-blue-600 hover:text-blue-800"
                                >
                                    View All →
                                </Link>
                            </div>

                            <div v-if="gage.calibration_records.length === 0" class="text-center py-8 text-gray-500">
                                No calibration records found. Add the first calibration to get started.
                            </div>

                            <div v-else class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                    <thead>
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Date
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Measured Value
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Calibrated Value
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Technician
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                        <tr 
                                            v-for="record in gage.calibration_records.slice(0, 5)" 
                                            :key="record.id"
                                        >
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                {{ new Date(record.calibrated_at).toLocaleDateString() }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                {{ record.measured_value }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                {{ record.calibrated_value }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                {{ record.user.name }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tolerance Records -->
                        <div v-if="gage.tolerance_records && gage.tolerance_records.length > 0" class="mb-8 bg-red-50 dark:bg-red-900 rounded-lg p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h2 class="text-lg font-semibold text-red-800 dark:text-red-200">Out-of-Tolerance Records</h2>
                                <Link 
                                    :href="route('gage-tolerance-records.index')"
                                    class="text-red-600 hover:text-red-800"
                                >
                                    View All →
                                </Link>
                            </div>
                            <div class="space-y-3">
                                <div 
                                    v-for="record in gage.tolerance_records.slice(0, 3)" 
                                    :key="record.id"
                                    class="bg-white dark:bg-gray-800 rounded p-4"
                                >
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <span 
                                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full mb-2"
                                                :class="{
                                                    'bg-red-100 text-red-800': record.status === 'out_of_tolerance',
                                                    'bg-yellow-100 text-yellow-800': record.status === 'under_review',
                                                    'bg-green-100 text-green-800': record.status === 'resolved'
                                                }"
                                            >
                                                {{ record.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                                            </span>
                                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                                <strong>Risk:</strong> {{ record.risk_assessment.substring(0, 100) }}...
                                            </p>
                                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                                                <strong>Actions:</strong> {{ record.corrective_actions.substring(0, 100) }}...
                                            </p>
                                            <p class="text-xs text-gray-500 mt-2">
                                                Identified: {{ new Date(record.identified_at).toLocaleDateString() }}
                                            </p>
                                        </div>
                                        <Link 
                                            :href="route('gage-tolerance-records.show', record.id)"
                                            class="text-blue-600 hover:text-blue-800 text-sm"
                                        >
                                            View Details
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>