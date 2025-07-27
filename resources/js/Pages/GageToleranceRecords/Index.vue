<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    toleranceRecords: Object,
    departments: Array,
    users: Array,
    filters: Object,
});

// Search and filter state
const search = ref(props.filters?.search || '');
const selectedDepartment = ref(props.filters?.department_id || '');
const selectedStatus = ref(props.filters?.status || '');
const selectedUser = ref(props.filters?.user_id || '');
const dateFrom = ref(props.filters?.date_from || '');
const dateTo = ref(props.filters?.date_to || '');

// Status options
const statusOptions = [
    { value: 'out_of_tolerance', label: 'Out of Tolerance' },
    { value: 'under_review', label: 'Under Review' },
    { value: 'resolved', label: 'Resolved' }
];

// Apply filters function
const applyFilters = () => {
    router.get(route('gage-tolerance-records.index'), {
        search: search.value || undefined,
        department_id: selectedDepartment.value || undefined,
        status: selectedStatus.value || undefined,
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
watch([selectedDepartment, selectedStatus, selectedUser, dateFrom, dateTo], applyFilters);

const clearFilters = () => {
    search.value = '';
    selectedDepartment.value = '';
    selectedStatus.value = '';
    selectedUser.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    applyFilters();
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Out-of-Tolerance Records" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex justify-between items-center mb-6">
                            <h1 class="text-2xl font-bold">Out-of-Tolerance Records</h1>
                        </div>

                        <!-- Search and Filter -->
                        <div class="mb-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                <!-- Search -->
                                <div>
                                    <TextInput 
                                        v-model="search"
                                        placeholder="Search by Gage ID or Description"
                                        class="w-full"
                                    />
                                </div>

                                <!-- Department Filter -->
                                <div>
                                    <select 
                                        v-model="selectedDepartment" 
                                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
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

                                <!-- Status Filter -->
                                <div>
                                    <select 
                                        v-model="selectedStatus" 
                                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    >
                                        <option value="">All Statuses</option>
                                        <option 
                                            v-for="status in statusOptions" 
                                            :key="status.value" 
                                            :value="status.value"
                                        >
                                            {{ status.label }}
                                        </option>
                                    </select>
                                </div>

                                <!-- User Filter -->
                                <div>
                                    <select 
                                        v-model="selectedUser" 
                                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    >
                                        <option value="">All Users</option>
                                        <option 
                                            v-for="user in users" 
                                            :key="user.id" 
                                            :value="user.id"
                                        >
                                            {{ user.name }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Date Range -->
                                <div class="sm:col-span-2 lg:col-span-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Date From
                                            </label>
                                            <input 
                                                type="date" 
                                                v-model="dateFrom"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Date To
                                            </label>
                                            <input 
                                                type="date" 
                                                v-model="dateTo"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end mt-4">
                                <PrimaryButton @click="applyFilters">
                                    Apply Filters
                                </PrimaryButton>
                                <button 
                                    @click="clearFilters" 
                                    class="ml-3 inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                >
                                    Clear Filters
                                </button>
                            </div>
                        </div>

                        <div v-if="toleranceRecords.data.length === 0" class="text-center py-8 text-gray-500">
                            No out-of-tolerance records found.
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Gage
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Risk Assessment
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Identified Date
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Identified By
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr 
                                        v-for="record in toleranceRecords.data" 
                                        :key="record.id"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ record.gage.gage_id }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ record.gage.description || 'No description' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span 
                                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                                :class="{
                                                    'bg-red-100 text-red-800': record.status === 'out_of_tolerance',
                                                    'bg-yellow-100 text-yellow-800': record.status === 'under_review',
                                                    'bg-green-100 text-green-800': record.status === 'resolved'
                                                }"
                                            >
                                                {{ record.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900 dark:text-gray-100 max-w-xs truncate">
                                                {{ record.risk_assessment }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                                {{ new Date(record.identified_at).toLocaleDateString() }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                                {{ record.user.name }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-3">
                                                <!-- View Icon -->
                                                <Link 
                                                    :href="route('gage-tolerance-records.show', record.id)"
                                                    class="text-blue-600 hover:text-blue-800 transition-colors"
                                                    title="View tolerance record details"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                </Link>

                                                <!-- Edit Icon -->
                                                <Link 
                                                    :href="route('gage-tolerance-records.edit', record.id)"
                                                    class="text-green-600 hover:text-green-800 transition-colors"
                                                    title="Edit tolerance record"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Pagination -->
                            <div v-if="toleranceRecords.links" class="mt-6">
                                <nav class="flex items-center justify-between">
                                    <div class="flex-1 flex justify-between sm:hidden">
                                        <Link 
                                            v-if="toleranceRecords.prev_page_url"
                                            :href="toleranceRecords.prev_page_url"
                                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                        >
                                            Previous
                                        </Link>
                                        <Link 
                                            v-if="toleranceRecords.next_page_url"
                                            :href="toleranceRecords.next_page_url"
                                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                        >
                                            Next
                                        </Link>
                                    </div>
                                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                        <div>
                                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                                Showing {{ toleranceRecords.from }} to {{ toleranceRecords.to }} of {{ toleranceRecords.total }} results
                                            </p>
                                        </div>
                                        <div>
                                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                                <Link 
                                                    v-for="link in toleranceRecords.links" 
                                                    :key="link.label"
                                                    :href="link.url"
                                                    v-html="link.label"
                                                    :class="[
                                                        'relative inline-flex items-center px-2 py-2 border text-sm font-medium',
                                                        link.active 
                                                            ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' 
                                                            : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                                                    ]"
                                                />
                                            </nav>
                                        </div>
                                    </div>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>