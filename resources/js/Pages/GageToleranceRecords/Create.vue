<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    calibrationRecord: Object,
});

const page = usePage();
const failedMeasurements = page.props.flash?.failed_measurements || [];
const measurementDeviation = page.props.flash?.measurement_deviation || null;

const form = useForm({
    calibration_record_id: props.calibrationRecord.id,
    risk_assessment: '',
    corrective_actions: '',
});

const submit = () => {
    form.post(route('gage-tolerance-records.store'));
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Create Out-of-Tolerance Record" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex items-center mb-6">
                            <Link 
                                :href="route('gage-tolerance-records.index')"
                                class="text-blue-600 hover:text-blue-800 mr-4"
                            >
                                ← Back to Tolerance Records
                            </Link>
                            <h1 class="text-2xl font-bold">Create Out-of-Tolerance Record</h1>
                        </div>

                        <!-- Alert for automatic detection -->
                        <div v-if="failedMeasurements.length > 0 || measurementDeviation" class="bg-orange-50 dark:bg-orange-900 border border-orange-200 dark:border-orange-700 rounded-lg p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-orange-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-orange-800 dark:text-orange-200">
                                        Out-of-Tolerance Condition Detected
                                    </h3>
                                    <div class="mt-2 text-sm text-orange-700 dark:text-orange-300">
                                        <p>The system has automatically detected measurements that are outside acceptable tolerance limits. Please complete the risk analysis below.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Calibration Record Info -->
                        <div class="bg-red-50 dark:bg-red-900 rounded-lg p-6 mb-6">
                            <h2 class="text-lg font-semibold mb-4 text-red-800 dark:text-red-200">Calibration Record</h2>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <span class="font-medium">Gage ID:</span>
                                    <span class="ml-2">{{ calibrationRecord.gage.gage_id }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">Description:</span>
                                    <span class="ml-2">{{ calibrationRecord.gage.description || 'N/A' }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">Calibration Date:</span>
                                    <span class="ml-2">{{ new Date(calibrationRecord.calibrated_at).toLocaleDateString() }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">Measured Value:</span>
                                    <span class="ml-2">{{ calibrationRecord.measured_value }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">Calibrated Value:</span>
                                    <span class="ml-2">{{ calibrationRecord.calibrated_value }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">Technician:</span>
                                    <span class="ml-2">{{ calibrationRecord.user?.name }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Failed Measurements Details (for detailed workflow) -->
                        <div v-if="failedMeasurements.length > 0" class="bg-red-50 dark:bg-red-900 rounded-lg p-6 mb-6">
                            <h3 class="text-lg font-semibold mb-4 text-red-800 dark:text-red-200">Failed Measurements</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-red-200 dark:divide-red-700">
                                    <thead>
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-red-700 dark:text-red-300 uppercase tracking-wider">Point</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-red-700 dark:text-red-300 uppercase tracking-wider">Nominal</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-red-700 dark:text-red-300 uppercase tracking-wider">As Found</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-red-700 dark:text-red-300 uppercase tracking-wider">As Left</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-red-700 dark:text-red-300 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-red-200 dark:divide-red-700">
                                        <tr v-for="measurement in failedMeasurements" :key="measurement.description" class="bg-white dark:bg-red-800">
                                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ measurement.description }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ measurement.nominal }}</td>
                                            <td class="px-4 py-2 text-sm">
                                                <span :class="measurement.as_found_pass === false ? 'text-red-600 font-semibold' : 'text-gray-900 dark:text-gray-100'">
                                                    {{ measurement.as_found_value || 'N/A' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-sm">
                                                <span :class="measurement.as_left_pass === false ? 'text-red-600 font-semibold' : 'text-gray-900 dark:text-gray-100'">
                                                    {{ measurement.as_left_value || 'N/A' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-sm">
                                                <span v-if="measurement.as_found_pass === false" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">As Found: Fail</span>
                                                <span v-if="measurement.as_left_pass === false" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 ml-1">As Left: Fail</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Measurement Deviation Details (for simple workflow) -->
                        <div v-if="measurementDeviation" class="bg-red-50 dark:bg-red-900 rounded-lg p-6 mb-6">
                            <h3 class="text-lg font-semibold mb-4 text-red-800 dark:text-red-200">Measurement Deviation</h3>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <span class="font-medium text-red-700 dark:text-red-300">Gage:</span>
                                    <span class="ml-2 text-gray-900 dark:text-gray-100">{{ measurementDeviation.gage_name }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-red-700 dark:text-red-300">Difference:</span>
                                    <span class="ml-2 text-red-600 font-semibold">{{ measurementDeviation.difference.toFixed(4) }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-red-700 dark:text-red-300">Measured:</span>
                                    <span class="ml-2 text-gray-900 dark:text-gray-100">{{ measurementDeviation.measured_value }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-red-700 dark:text-red-300">Calibrated:</span>
                                    <span class="ml-2 text-gray-900 dark:text-gray-100">{{ measurementDeviation.calibrated_value }}</span>
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent="submit" class="max-w-2xl">
                            <div class="mb-6">
                                <InputLabel for="risk_assessment" value="Risk Assessment *" />
                                <textarea
                                    id="risk_assessment"
                                    v-model="form.risk_assessment"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    rows="4"
                                    required
                                    placeholder="Describe the potential risks associated with this out-of-tolerance condition..."
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.risk_assessment" />
                            </div>

                            <div class="mb-6">
                                <InputLabel for="corrective_actions" value="Corrective Actions *" />
                                <textarea
                                    id="corrective_actions"
                                    v-model="form.corrective_actions"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    rows="4"
                                    required
                                    placeholder="Describe the corrective actions taken or planned to address this issue..."
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.corrective_actions" />
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton 
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    Create Tolerance Record
                                </PrimaryButton>
                                
                                <Link 
                                    :href="route('gage-tolerance-records.index')"
                                    class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
                                >
                                    Cancel
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>