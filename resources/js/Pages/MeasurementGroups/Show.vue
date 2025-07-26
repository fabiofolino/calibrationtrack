<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    measurementGroup: Object,
});

const measurementForm = useForm({
    measurements: props.measurementGroup.measurements.map(measurement => ({
        id: measurement.id,
        as_found_value: measurement.as_found_value,
        as_left_value: measurement.as_left_value,
        uncertainty: measurement.uncertainty,
        standards_used: measurement.standards_used,
    }))
});

const updateMeasurements = () => {
    measurementForm.post(route('measurement-groups.update-measurements', props.measurementGroup.id), {
        preserveState: true,
        onSuccess: () => {
            // Refresh the page to show updated pass/fail status
            window.location.reload();
        }
    });
};

const getPassFailStatus = (measurement, type) => {
    const value = type === 'found' ? measurement.as_found_value : measurement.as_left_value;
    if (value === null || value === undefined) return null;
    
    const pass = value >= measurement.lower_limit && value <= measurement.upper_limit;
    return pass;
};

const getStatusClass = (measurement, type) => {
    const status = getPassFailStatus(measurement, type);
    if (status === null) return 'bg-gray-100 text-gray-800';
    return status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
};

const getError = (measurement, type) => {
    const value = type === 'found' ? measurement.as_found_value : measurement.as_left_value;
    if (value === null || value === undefined) return null;
    return (value - measurement.nominal).toFixed(4);
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Measurement Group: ${measurementGroup.group_name}`" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center">
                                <Link 
                                    :href="route('calibration-records.show', measurementGroup.calibration_record.id)"
                                    class="text-blue-600 hover:text-blue-800 mr-4"
                                >
                                    ← Back to Calibration Record
                                </Link>
                                <h1 class="text-2xl font-bold">{{ measurementGroup.group_name }}</h1>
                            </div>
                            <div class="flex space-x-2">
                                <Link :href="route('measurement-groups.edit', measurementGroup.id)">
                                    <PrimaryButton>Edit Group</PrimaryButton>
                                </Link>
                            </div>
                        </div>

                        <!-- Group Information -->
                        <div class="grid md:grid-cols-2 gap-6 mb-8">
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                                <h2 class="text-lg font-semibold mb-4">Group Information</h2>
                                <div class="space-y-3">
                                    <div>
                                        <span class="font-medium">Group Number:</span>
                                        <span class="ml-2">{{ measurementGroup.group_number }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Type:</span>
                                        <span class="ml-2">{{ measurementGroup.type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Units:</span>
                                        <span class="ml-2">{{ measurementGroup.units }}</span>
                                    </div>
                                    <div v-if="measurementGroup.plus_value">
                                        <span class="font-medium">Tolerance:</span>
                                        <span class="ml-2">
                                            <template v-if="measurementGroup.type === 'tolerance_percent'">
                                                ±{{ measurementGroup.plus_value }}%
                                            </template>
                                            <template v-else-if="measurementGroup.type === 'tolerance_plus_minus'">
                                                +{{ measurementGroup.plus_value }} / -{{ measurementGroup.minus_value }}
                                            </template>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                                <h2 class="text-lg font-semibold mb-4">Gage Information</h2>
                                <div class="space-y-3">
                                    <div>
                                        <span class="font-medium">Gage ID:</span>
                                        <span class="ml-2">{{ measurementGroup.calibration_record.gage.gage_id }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Description:</span>
                                        <span class="ml-2">{{ measurementGroup.calibration_record.gage.description || 'N/A' }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Calibration Date:</span>
                                        <span class="ml-2">{{ new Date(measurementGroup.calibration_record.calibrated_at).toLocaleDateString() }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Technician:</span>
                                        <span class="ml-2">{{ measurementGroup.calibration_record.user?.name }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Measurements Table -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
                            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                                <h3 class="text-lg font-semibold">Measurements</h3>
                            </div>
                            
                            <form @submit.prevent="updateMeasurements">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th v-if="measurementGroup.show_sequence" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    #
                                                </th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Nominal
                                                </th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Upper Limit
                                                </th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Lower Limit
                                                </th>
                                                <th v-if="measurementGroup.show_description" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Description
                                                </th>
                                                
                                                <!-- As Found columns -->
                                                <th class="px-4 py-3 text-center text-xs font-medium text-blue-600 dark:text-blue-400 uppercase tracking-wider" colspan="3">
                                                    As Found
                                                </th>
                                                
                                                <!-- As Left columns -->
                                                <th class="px-4 py-3 text-center text-xs font-medium text-green-600 dark:text-green-400 uppercase tracking-wider" colspan="3">
                                                    As Left
                                                </th>
                                                
                                                <th v-if="measurementGroup.show_uncertainty" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Uncertainty
                                                </th>
                                                <th v-if="measurementGroup.show_standards" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Standards
                                                </th>
                                            </tr>
                                            <tr class="bg-gray-100 dark:bg-gray-600">
                                                <th v-if="measurementGroup.show_sequence" class="px-4 py-2"></th>
                                                <th class="px-4 py-2"></th>
                                                <th class="px-4 py-2"></th>
                                                <th class="px-4 py-2"></th>
                                                <th v-if="measurementGroup.show_description" class="px-4 py-2"></th>
                                                
                                                <!-- As Found sub-headers -->
                                                <th class="px-3 py-2 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Value</th>
                                                <th class="px-3 py-2 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Error</th>
                                                <th class="px-3 py-2 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">P/F</th>
                                                
                                                <!-- As Left sub-headers -->
                                                <th class="px-3 py-2 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Value</th>
                                                <th class="px-3 py-2 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Error</th>
                                                <th class="px-3 py-2 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">P/F</th>
                                                
                                                <th v-if="measurementGroup.show_uncertainty" class="px-4 py-2"></th>
                                                <th v-if="measurementGroup.show_standards" class="px-4 py-2"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                                            <tr 
                                                v-for="(measurement, index) in measurementGroup.measurements" 
                                                :key="measurement.id"
                                                class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                            >
                                                <td v-if="measurementGroup.show_sequence" class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                                    {{ measurement.sequence }}
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                                    {{ measurement.nominal }} {{ measurementGroup.units }}
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                                                    {{ measurement.upper_limit }} {{ measurementGroup.units }}
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                                                    {{ measurement.lower_limit }} {{ measurementGroup.units }}
                                                </td>
                                                <td v-if="measurementGroup.show_description" class="px-4 py-4 whitespace-nowrap text-sm">
                                                    {{ measurement.description || '-' }}
                                                </td>
                                                
                                                <!-- As Found columns -->
                                                <td class="px-3 py-4">
                                                    <TextInput
                                                        type="number"
                                                        step="0.0001"
                                                        v-model="measurementForm.measurements[index].as_found_value"
                                                        class="w-24 text-sm"
                                                        placeholder="0.0000"
                                                    />
                                                </td>
                                                <td class="px-3 py-4 text-sm text-center">
                                                    {{ getError(measurement, 'found') || '-' }}
                                                </td>
                                                <td class="px-3 py-4 text-center">
                                                    <span 
                                                        v-if="getPassFailStatus(measurement, 'found') !== null"
                                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                                        :class="getStatusClass(measurement, 'found')"
                                                    >
                                                        {{ getPassFailStatus(measurement, 'found') ? 'Pass' : 'Fail' }}
                                                    </span>
                                                    <span v-else class="text-gray-400">-</span>
                                                </td>
                                                
                                                <!-- As Left columns -->
                                                <td class="px-3 py-4">
                                                    <TextInput
                                                        type="number"
                                                        step="0.0001"
                                                        v-model="measurementForm.measurements[index].as_left_value"
                                                        class="w-24 text-sm"
                                                        placeholder="0.0000"
                                                    />
                                                </td>
                                                <td class="px-3 py-4 text-sm text-center">
                                                    {{ getError(measurement, 'left') || '-' }}
                                                </td>
                                                <td class="px-3 py-4 text-center">
                                                    <span 
                                                        v-if="getPassFailStatus(measurement, 'left') !== null"
                                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                                        :class="getStatusClass(measurement, 'left')"
                                                    >
                                                        {{ getPassFailStatus(measurement, 'left') ? 'Pass' : 'Fail' }}
                                                    </span>
                                                    <span v-else class="text-gray-400">-</span>
                                                </td>
                                                
                                                <td v-if="measurementGroup.show_uncertainty" class="px-4 py-4">
                                                    <TextInput
                                                        type="number"
                                                        step="0.0001"
                                                        v-model="measurementForm.measurements[index].uncertainty"
                                                        class="w-24 text-sm"
                                                        placeholder="0.0000"
                                                    />
                                                </td>
                                                <td v-if="measurementGroup.show_standards" class="px-4 py-4">
                                                    <TextInput
                                                        v-model="measurementForm.measurements[index].standards_used"
                                                        class="w-32 text-sm"
                                                        placeholder="Standards used"
                                                    />
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600">
                                    <PrimaryButton 
                                        :class="{ 'opacity-25': measurementForm.processing }"
                                        :disabled="measurementForm.processing"
                                    >
                                        Save Measurements
                                    </PrimaryButton>
                                </div>
                            </form>
                        </div>

                        <!-- Notes -->
                        <div v-if="measurementGroup.notes" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-2">Notes</h3>
                            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ measurementGroup.notes }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>