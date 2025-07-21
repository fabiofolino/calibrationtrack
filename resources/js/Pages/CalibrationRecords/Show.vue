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
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center">
                                <Link 
                                    :href="route('calibration-records.index', { gage_id: calibrationRecord.gage_id })"
                                    class="text-blue-600 hover:text-blue-800 mr-4"
                                >
                                    ← Back to Calibration Records
                                </Link>
                                <h1 class="text-2xl font-bold">Calibration Record Details</h1>
                            </div>
                            <Link :href="route('calibration-records.edit', calibrationRecord.id)">
                                <PrimaryButton>Edit Record</PrimaryButton>
                            </Link>
                        </div>

                        <!-- Record Details -->
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                                <h2 class="text-lg font-semibold mb-4">Gage Information</h2>
                                <div class="space-y-3">
                                    <div>
                                        <span class="font-medium">Gage Name:</span>
                                        <span class="ml-2">{{ calibrationRecord.gage.name }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Serial Number:</span>
                                        <span class="ml-2">{{ calibrationRecord.gage.serial_number }}</span>
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

                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 md:col-span-2">
                                <h2 class="text-lg font-semibold mb-4">Measurement Values</h2>
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

                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 md:col-span-2">
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
                        </div>

                        <!-- Actions -->
                        <div class="mt-6 flex space-x-4">
                            <Link :href="route('gages.show', calibrationRecord.gage.id)">
                                <PrimaryButton>View Gage Details</PrimaryButton>
                            </Link>
                            <Link :href="route('calibration-records.create', { gage_id: calibrationRecord.gage_id })">
                                <PrimaryButton>Add Another Calibration</PrimaryButton>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>