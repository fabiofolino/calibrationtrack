<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    calibrationRecord: Object,
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Calibration Record Details" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex items-center mb-6">
                            <Link 
                                :href="route('calibration-records.index', { gage_id: calibrationRecord.gage_id })"
                                class="text-blue-600 hover:text-blue-800 mr-4"
                            >
                                ← Back to Calibration Records
                            </Link>
                            <div class="flex-1">
                                <h1 class="text-2xl font-bold">Calibration Record Details</h1>
                                <div class="flex items-center mt-2 space-x-4">
                                    <span v-if="calibrationRecord.measurement_groups && calibrationRecord.measurement_groups.length > 0" 
                                          class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                                        Detailed Measurements
                                    </span>
                                    <span v-else-if="calibrationRecord.measured_value && calibrationRecord.calibrated_value" 
                                          class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">
                                        Simple Record
                                    </span>
                                    <span v-else 
                                          class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">
                                        Incomplete - Add Measurements
                                    </span>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <Link :href="route('calibration-records.edit', calibrationRecord.id)">
                                    <PrimaryButton>Edit Record</PrimaryButton>
                                </Link>
                                <Link v-if="!calibrationRecord.measurement_groups || calibrationRecord.measurement_groups.length === 0" 
                                      :href="route('measurement-groups.create', { calibration_record_id: calibrationRecord.id })">
                                    <PrimaryButton class="bg-green-600 hover:bg-green-700">Add Measurement Group</PrimaryButton>
                                </Link>
                            </div>
                        </div>

                        <!-- Record Details -->
                        <div class="grid md:grid-cols-2 gap-6 mb-8">
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                                <h2 class="text-lg font-semibold mb-4">Gage Information</h2>
                                <div class="space-y-3">
                                    <div>
                                        <span class="font-medium">Gage ID:</span>
                                        <span class="ml-2">{{ calibrationRecord.gage.gage_id }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Description:</span>
                                        <span class="ml-2">{{ calibrationRecord.gage.description || 'N/A' }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Serial Number:</span>
                                        <span class="ml-2">{{ calibrationRecord.gage.serial_number }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Location:</span>
                                        <span class="ml-2">{{ calibrationRecord.gage.location }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Custodian:</span>
                                        <span class="ml-2">{{ calibrationRecord.gage.custodian }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Department:</span>
                                        <span class="ml-2">{{ calibrationRecord.gage.department.name }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                                <h2 class="text-lg font-semibold mb-4">Calibration Details</h2>
                                <div class="space-y-3">
                                    <div>
                                        <span class="font-medium">Calibration Date:</span>
                                        <span class="ml-2">{{ new Date(calibrationRecord.calibrated_at).toLocaleDateString() }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Calibration Time:</span>
                                        <span class="ml-2">{{ new Date(calibrationRecord.calibrated_at).toLocaleTimeString() }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Technician:</span>
                                        <span class="ml-2">{{ calibrationRecord.user.name }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Legacy Measurement Values (if no measurement groups) -->
                        <div v-if="!calibrationRecord.measurement_groups || calibrationRecord.measurement_groups.length === 0" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 mb-8">
                            <h2 class="text-lg font-semibold mb-4">Legacy Measurement Values</h2>
                            <div class="grid md:grid-cols-3 gap-6">
                                <div>
                                    <span class="font-medium block text-sm text-gray-600 dark:text-gray-400">Measured Value:</span>
                                    <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ calibrationRecord.measured_value }}</span>
                                </div>
                                <div>
                                    <span class="font-medium block text-sm text-gray-600 dark:text-gray-400">Calibrated Value:</span>
                                    <span class="text-2xl font-bold text-green-600 dark:text-green-400">{{ calibrationRecord.calibrated_value }}</span>
                                </div>
                                <div>
                                    <span class="font-medium block text-sm text-gray-600 dark:text-gray-400">Difference:</span>
                                    <span class="text-2xl font-bold" :class="{
                                        'text-red-600 dark:text-red-400': Math.abs(calibrationRecord.calibrated_value - calibrationRecord.measured_value) > 0.01,
                                        'text-gray-600 dark:text-gray-400': Math.abs(calibrationRecord.calibrated_value - calibrationRecord.measured_value) <= 0.01
                                    }">
                                        {{ (calibrationRecord.calibrated_value - calibrationRecord.measured_value).toFixed(4) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Measurement Groups -->
                        <div v-if="calibrationRecord.measurement_groups && calibrationRecord.measurement_groups.length > 0" class="mb-8">
                            <div class="flex justify-between items-center mb-4">
                                <h2 class="text-xl font-semibold">Measurement Groups</h2>
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ calibrationRecord.measurement_groups.length }} group(s)
                                </span>
                            </div>
                            
                            <div class="grid gap-6">
                                <div 
                                    v-for="group in calibrationRecord.measurement_groups" 
                                    :key="group.id"
                                    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6"
                                >
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <h3 class="text-lg font-semibold">{{ group.group_name }}</h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                Group {{ group.group_number }} • {{ group.measurements.length }} measurement(s) • {{ group.units }}
                                            </p>
                                        </div>
                                        <div class="flex space-x-2">
                                            <Link :href="route('measurement-groups.show', group.id)">
                                                <PrimaryButton class="text-xs py-1 px-3">View/Edit</PrimaryButton>
                                            </Link>
                                        </div>
                                    </div>

                                    <!-- Quick measurement summary -->
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                            <thead class="bg-gray-50 dark:bg-gray-700">
                                                <tr>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">#</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nominal</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">As Found</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">As Left</th>
                                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                                <tr 
                                                    v-for="measurement in group.measurements.slice(0, 5)" 
                                                    :key="measurement.id"
                                                    class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                                >
                                                    <td class="px-3 py-2 text-sm">{{ measurement.sequence }}</td>
                                                    <td class="px-3 py-2 text-sm">{{ measurement.nominal }}</td>
                                                    <td class="px-3 py-2 text-sm">
                                                        {{ measurement.as_found_value || '-' }}
                                                    </td>
                                                    <td class="px-3 py-2 text-sm">
                                                        {{ measurement.as_left_value || '-' }}
                                                    </td>
                                                    <td class="px-3 py-2 text-center">
                                                        <span 
                                                            v-if="measurement.as_found_pass !== null || measurement.as_left_pass !== null"
                                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                                            :class="{
                                                                'bg-green-100 text-green-800': measurement.as_found_pass === true && measurement.as_left_pass === true,
                                                                'bg-red-100 text-red-800': measurement.as_found_pass === false || measurement.as_left_pass === false,
                                                                'bg-yellow-100 text-yellow-800': measurement.as_found_pass === null || measurement.as_left_pass === null
                                                            }"
                                                        >
                                                            {{ 
                                                                (measurement.as_found_pass === true && measurement.as_left_pass === true) ? 'Pass' :
                                                                (measurement.as_found_pass === false || measurement.as_left_pass === false) ? 'Fail' : 'Pending'
                                                            }}
                                                        </span>
                                                        <span v-else class="text-gray-400 text-xs">-</span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <div v-if="group.measurements.length > 5" class="px-3 py-2 text-sm text-gray-500 text-center">
                                            ... and {{ group.measurements.length - 5 }} more measurements
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Certificate Section -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 mb-8">
                            <h2 class="text-lg font-semibold mb-4">Certificate</h2>
                            <div v-if="calibrationRecord.cert_file">
                                <a 
                                    :href="route('calibration-records.download-certificate', calibrationRecord.id)"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    target="_blank"
                                >
                                    📄 Download Certificate (PDF)
                                </a>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    Click the button above to view or download the calibration certificate.
                                </p>
                            </div>
                            <div v-else class="text-gray-500 italic">
                                No certificate uploaded for this calibration record.
                            </div>
                        </div>

                        <!-- Record History -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 mb-8">
                            <h2 class="text-lg font-semibold mb-4">Record History</h2>
                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <span class="font-medium">Created:</span>
                                    <span class="ml-2">{{ new Date(calibrationRecord.created_at).toLocaleString() }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">Last Updated:</span>
                                    <span class="ml-2">{{ new Date(calibrationRecord.updated_at).toLocaleString() }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-wrap gap-4">
                            <Link :href="route('gages.show', calibrationRecord.gage.id)">
                                <PrimaryButton>View Gage Details</PrimaryButton>
                            </Link>
                            <Link :href="route('calibration-records.create', { gage_id: calibrationRecord.gage_id })">
                                <PrimaryButton>Add Another Calibration</PrimaryButton>
                            </Link>
                            <Link :href="route('measurement-groups.create', { calibration_record_id: calibrationRecord.id })">
                                <PrimaryButton class="bg-green-600 hover:bg-green-700">Add Measurement Group</PrimaryButton>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>