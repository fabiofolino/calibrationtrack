<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    calibrationRecords: Array,
    gage_id: String,
    user: Object,
});

const deleteRecord = (record) => {
    // Additional client-side check with helpful messages
    if (!record.can_delete) {
        if (!props.user.is_admin && record.user.id !== props.user.id) {
            alert('You can only delete your own calibration records.');
        } else if (isOlderThan30Days(record.created_at)) {
            alert('Cannot delete calibration records older than 30 days.');
        } else if (hasDetailedMeasurements(record)) {
            alert('Cannot delete calibration records with detailed measurement data.');
        } else {
            alert('You do not have permission to delete this calibration record.');
        }
        return;
    }

    if (confirm('Are you sure you want to delete this calibration record? This action cannot be undone.')) {
        router.delete(route('calibration-records.destroy', record.id));
    }
};

const isOlderThan30Days = (createdAt) => {
    const thirtyDaysAgo = new Date();
    thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
    return new Date(createdAt) < thirtyDaysAgo;
};

const hasDetailedMeasurements = (record) => {
    // This is a simplified check - the real check is done server-side
    return record.measurement_groups && record.measurement_groups.length > 0;
};

const getDeletionTooltip = (record) => {
    if (record.can_delete) return 'Delete this calibration record';
    
    if (!props.user.is_admin && record.user.id !== props.user.id) {
        return 'You can only delete your own records';
    }
    if (isOlderThan30Days(record.created_at)) {
        return 'Cannot delete records older than 30 days';
    }
    if (hasDetailedMeasurements(record)) {
        return 'Cannot delete records with measurement data';
    }
    return 'You do not have permission to delete this record';
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Calibration Records" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex justify-between items-center mb-6">
                            <div class="flex items-center">
                                <Link 
                                    :href="route('gages.index')"
                                    class="text-blue-600 hover:text-blue-800 mr-4"
                                >
                                    ← Back to Gages
                                </Link>
                                <h1 class="text-2xl font-bold">
                                    Calibration Records
                                    <span v-if="gage_id && calibrationRecords.length > 0" class="text-lg font-normal text-gray-600 dark:text-gray-400">
                                        for {{ calibrationRecords[0].gage.name }}
                                    </span>
                                </h1>
                            </div>
                            <div class="flex space-x-3">
                                <!-- Export Buttons -->
                                <div class="flex space-x-2">
                                    <a 
                                        :href="route('export.calibration-records.csv')"
                                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    >
                                        📊 Export CSV
                                    </a>
                                    <a 
                                        :href="route('export.calibration-records.pdf')"
                                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    >
                                        📄 Export PDF
                                    </a>
                                </div>
                                <Link :href="route('calibration-records.create', gage_id ? { gage_id } : {})">
                                    <PrimaryButton>Add Calibration Record</PrimaryButton>
                                </Link>
                            </div>
                        </div>

                        <div v-if="calibrationRecords.length === 0" class="text-center py-8 text-gray-500">
                            No calibration records found. Create your first calibration record to get started.
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th v-if="!gage_id" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Gage
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
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Department
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Certificate
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr 
                                        v-for="record in calibrationRecords" 
                                        :key="record.id"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ new Date(record.calibrated_at).toLocaleDateString() }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ new Date(record.calibrated_at).toLocaleTimeString() }}
                                            </div>
                                        </td>
                                        <td v-if="!gage_id" class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                                {{ record.gage.name }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ record.gage.serial_number }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                                {{ record.measured_value }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                                {{ record.calibrated_value }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                                {{ record.user.name }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                                {{ record.gage.department.name }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div v-if="record.cert_file" class="text-sm">
                                                <a 
                                                    :href="route('calibration-records.download-certificate', record.id)"
                                                    class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full hover:bg-blue-200"
                                                    target="_blank"
                                                >
                                                    📄 Download PDF
                                                </a>
                                            </div>
                                            <div v-else class="text-sm text-gray-500 italic">
                                                No certificate
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                <Link 
                                                    :href="route('calibration-records.show', record.id)"
                                                    class="text-blue-600 hover:text-blue-800"
                                                >
                                                    View
                                                </Link>
                                                <Link 
                                                    :href="route('calibration-records.edit', record.id)"
                                                    class="text-blue-600 hover:text-blue-800"
                                                >
                                                    Edit
                                                </Link>
                                                <button 
                                                    v-if="record.can_delete"
                                                    @click="deleteRecord(record)"
                                                    class="text-red-600 hover:text-red-800 transition-colors"
                                                    :title="getDeletionTooltip(record)"
                                                >
                                                    Delete
                                                </button>
                                                <span 
                                                    v-else
                                                    class="text-gray-400 cursor-not-allowed"
                                                    :title="getDeletionTooltip(record)"
                                                >
                                                    Delete
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>