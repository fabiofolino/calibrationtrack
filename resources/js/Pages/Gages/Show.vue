<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    gage: Object,
});
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Gage: ${gage.name}`" />

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
                                <h1 class="text-2xl font-bold">{{ gage.name }}</h1>
                            </div>
                            <div class="flex space-x-2">
                                <Link :href="route('gages.edit', gage.id)">
                                    <PrimaryButton>Edit Gage</PrimaryButton>
                                </Link>
                                <Link :href="route('calibration-records.create', { gage_id: gage.id })">
                                    <PrimaryButton>Add Calibration</PrimaryButton>
                                </Link>
                            </div>
                        </div>

                        <!-- Gage Details -->
                        <div class="grid md:grid-cols-2 gap-6 mb-8">
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                                <h2 class="text-lg font-semibold mb-4">Gage Information</h2>
                                <div class="space-y-3">
                                    <div>
                                        <span class="font-medium">Serial Number:</span>
                                        <span class="ml-2">{{ gage.serial_number }}</span>
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
                                    <div>
                                        <span class="font-medium">Total Calibrations:</span>
                                        <span class="ml-2">{{ gage.calibration_records.length }}</span>
                                    </div>
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
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>