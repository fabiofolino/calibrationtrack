<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    calibrationRecords: Array,
    gage_id: String,
    user: Object,
    departments: Array,
    users: Array,
    filters: Object,
});

// Search and filter state
const search = ref(props.filters?.search || '');
const selectedDepartment = ref(props.filters?.department_id || '');
const selectedUser = ref(props.filters?.user_id || '');
const dateFrom = ref(props.filters?.date_from || '');
const dateTo = ref(props.filters?.date_to || '');

// Apply filters function
const applyFilters = () => {
    router.get(route('calibration-records.index', props.gage_id ? { gage_id: props.gage_id } : {}), {
        search: search.value || undefined,
        department_id: selectedDepartment.value || undefined,
        user_id: selectedUser.value || undefined,
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

// Debounced search
let searchTimeout;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 300);
});

// Immediate filter application for dropdowns and dates
watch([selectedDepartment, selectedUser, dateFrom, dateTo], applyFilters);

const clearFilters = () => {
    search.value = '';
    selectedDepartment.value = '';
    selectedUser.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    applyFilters();
};

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

                        <!-- Search and Filter Section -->
                        <div class="mb-6 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                                    <TextInput 
                                        v-model="search"
                                        placeholder="Search by technician, gage..."
                                        class="w-full"
                                    />
                                </div>
                                <div v-if="departments && departments.length > 0">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Department</label>
                                    <select 
                                        v-model="selectedDepartment" 
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:border-blue-500 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800"
                                    >
                                        <option value="">All Departments</option>
                                        <option 
                                            v-for="department in departments" 
                                            :key="department.id" 
                                            :value="department.id"
                                        >
                                            {{ department.name }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From Date</label>
                                    <input 
                                        v-model="dateFrom"
                                        type="date"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:border-blue-500 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">To Date</label>
                                    <input 
                                        v-model="dateTo"
                                        type="date"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:border-blue-500 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800"
                                    />
                                </div>
                                <div class="flex items-end">
                                    <button 
                                        @click="clearFilters"
                                        class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-200 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    >
                                        Clear Filters
                                    </button>
                                </div>
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
                                            <div class="flex justify-end space-x-3">
                                                <!-- View Icon -->
                                                <Link 
                                                    :href="route('calibration-records.show', record.id)"
                                                    class="text-blue-600 hover:text-blue-800 transition-colors"
                                                    title="View calibration record details"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                </Link>

                                                <!-- Edit Icon -->
                                                <Link 
                                                    :href="route('calibration-records.edit', record.id)"
                                                    class="text-green-600 hover:text-green-800 transition-colors"
                                                    title="Edit calibration record"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </Link>

                                                <!-- Delete Icon -->
                                                <button 
                                                    v-if="record.can_delete"
                                                    @click="deleteRecord(record)"
                                                    class="text-red-600 hover:text-red-800 transition-colors"
                                                    :title="getDeletionTooltip(record)"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1-1H8a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>

                                                <!-- Disabled Delete Icon -->
                                                <span 
                                                    v-else
                                                    class="text-gray-400 cursor-not-allowed"
                                                    :title="getDeletionTooltip(record)"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1-1H8a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
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