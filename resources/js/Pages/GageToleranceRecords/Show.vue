<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    toleranceRecord: Object,
});

const showResolveForm = ref(false);
const resolveForm = useForm({
    resolution_notes: '',
});

const submitResolve = () => {
    resolveForm.post(route('gage-tolerance-records.resolve', props.toleranceRecord.id), {
        onSuccess: () => {
            showResolveForm.value = false;
            resolveForm.reset();
        }
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Out-of-Tolerance Record Details" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center">
                                <Link 
                                    :href="route('gage-tolerance-records.index')"
                                    class="text-blue-600 hover:text-blue-800 mr-4"
                                >
                                    ← Back to Tolerance Records
                                </Link>
                                <h1 class="text-2xl font-bold">Out-of-Tolerance Record Details</h1>
                            </div>
                            <div class="flex space-x-2">
                                <Link 
                                    v-if="toleranceRecord.status !== 'resolved'"
                                    :href="route('gage-tolerance-records.edit', toleranceRecord.id)"
                                >
                                    <PrimaryButton>Edit Record</PrimaryButton>
                                </Link>
                                <button 
                                    v-if="toleranceRecord.status !== 'resolved'"
                                    @click="showResolveForm = true"
                                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    Mark as Resolved
                                </button>
                            </div>
                        </div>

                        <!-- Status Banner -->
                        <div class="mb-6">
                            <span 
                                class="inline-flex px-4 py-2 text-lg font-semibold rounded-full"
                                :class="{
                                    'bg-red-100 text-red-800': toleranceRecord.status === 'out_of_tolerance',
                                    'bg-yellow-100 text-yellow-800': toleranceRecord.status === 'under_review',
                                    'bg-green-100 text-green-800': toleranceRecord.status === 'resolved'
                                }"
                            >
                                {{ toleranceRecord.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                            </span>
                        </div>

                        <!-- Gage Information -->
                        <div class="grid md:grid-cols-2 gap-6 mb-8">
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                                <h2 class="text-lg font-semibold mb-4">Gage Information</h2>
                                <div class="space-y-3">
                                    <div>
                                        <span class="font-medium">Gage ID:</span>
                                        <span class="ml-2">{{ toleranceRecord.gage.gage_id }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Description:</span>
                                        <span class="ml-2">{{ toleranceRecord.gage.description || 'N/A' }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Serial Number:</span>
                                        <span class="ml-2">{{ toleranceRecord.gage.serial_number }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Location:</span>
                                        <span class="ml-2">{{ toleranceRecord.gage.location }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Custodian:</span>
                                        <span class="ml-2">{{ toleranceRecord.gage.custodian }}</span>
                                    </div>
                                    <div class="pt-2">
                                        <Link 
                                            :href="route('gages.show', toleranceRecord.gage.id)"
                                            class="text-blue-600 hover:text-blue-800"
                                        >
                                            View Gage Details →
                                        </Link>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                                <h2 class="text-lg font-semibold mb-4">Calibration Record</h2>
                                <div class="space-y-3">
                                    <div>
                                        <span class="font-medium">Calibration Date:</span>
                                        <span class="ml-2">{{ new Date(toleranceRecord.calibration_record.calibrated_at).toLocaleDateString() }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Measured Value:</span>
                                        <span class="ml-2">{{ toleranceRecord.calibration_record.measured_value }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Calibrated Value:</span>
                                        <span class="ml-2">{{ toleranceRecord.calibration_record.calibrated_value }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Technician:</span>
                                        <span class="ml-2">{{ toleranceRecord.calibration_record.user?.name }}</span>
                                    </div>
                                    <div class="pt-2">
                                        <Link 
                                            :href="route('calibration-records.show', toleranceRecord.calibration_record.id)"
                                            class="text-blue-600 hover:text-blue-800"
                                        >
                                            View Calibration Record →
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Record Details -->
                        <div class="space-y-6 mb-8">
                            <div class="bg-red-50 dark:bg-red-900 rounded-lg p-6">
                                <h2 class="text-lg font-semibold mb-4 text-red-800 dark:text-red-200">Risk Assessment</h2>
                                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ toleranceRecord.risk_assessment }}</p>
                            </div>

                            <div class="bg-blue-50 dark:bg-blue-900 rounded-lg p-6">
                                <h2 class="text-lg font-semibold mb-4 text-blue-800 dark:text-blue-200">Corrective Actions</h2>
                                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ toleranceRecord.corrective_actions }}</p>
                            </div>

                            <div v-if="toleranceRecord.resolution_notes" class="bg-green-50 dark:bg-green-900 rounded-lg p-6">
                                <h2 class="text-lg font-semibold mb-4 text-green-800 dark:text-green-200">Resolution Notes</h2>
                                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ toleranceRecord.resolution_notes }}</p>
                                <p class="text-sm text-gray-500 mt-4">
                                    Resolved on: {{ new Date(toleranceRecord.resolved_at).toLocaleDateString() }}
                                </p>
                            </div>
                        </div>

                        <!-- Timeline -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 mb-8">
                            <h2 class="text-lg font-semibold mb-4">Timeline</h2>
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-2 h-2 mt-2 bg-red-500 rounded-full"></div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium">Out-of-tolerance condition identified</p>
                                        <p class="text-xs text-gray-500">
                                            {{ new Date(toleranceRecord.identified_at).toLocaleDateString() }} by {{ toleranceRecord.user.name }}
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-2 h-2 mt-2 bg-blue-500 rounded-full"></div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium">Record created with risk assessment and corrective actions</p>
                                        <p class="text-xs text-gray-500">
                                            {{ new Date(toleranceRecord.created_at).toLocaleDateString() }}
                                        </p>
                                    </div>
                                </div>

                                <div v-if="toleranceRecord.resolved_at" class="flex items-start">
                                    <div class="flex-shrink-0 w-2 h-2 mt-2 bg-green-500 rounded-full"></div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium">Issue resolved</p>
                                        <p class="text-xs text-gray-500">
                                            {{ new Date(toleranceRecord.resolved_at).toLocaleDateString() }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Resolve Form -->
                        <div v-if="showResolveForm" class="bg-green-50 dark:bg-green-900 rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-4 text-green-800 dark:text-green-200">Mark as Resolved</h3>
                            <form @submit.prevent="submitResolve" class="max-w-2xl">
                                <div class="mb-4">
                                    <InputLabel for="resolution_notes" value="Resolution Notes *" />
                                    <textarea
                                        id="resolution_notes"
                                        v-model="resolveForm.resolution_notes"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                        rows="4"
                                        required
                                        placeholder="Describe how the issue was resolved and any follow-up actions taken..."
                                    ></textarea>
                                </div>
                                <div class="flex items-center gap-4">
                                    <PrimaryButton 
                                        :class="{ 'opacity-25': resolveForm.processing }"
                                        :disabled="resolveForm.processing"
                                    >
                                        Mark as Resolved
                                    </PrimaryButton>
                                    <button 
                                        type="button"
                                        @click="showResolveForm = false"
                                        class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>