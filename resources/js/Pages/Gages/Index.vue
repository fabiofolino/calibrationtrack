<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    gages: Array,
    departments: Array,
    locations: Array,
    filters: Object,
    subscriptionInfo: Object,
    hasDepartments: Boolean,
});

// Search and filter reactive state
const search = ref(props.filters.search || '');
const selectedDepartment = ref(props.filters.department_id || '');
const selectedStatus = ref(props.filters.status || '');
const selectedLocation = ref(props.filters.location || '');
const sortBy = ref(props.filters.sort_by || 'gage_id');
const sortOrder = ref(props.filters.sort_order || 'asc');

// Watch for changes and apply filters
const applyFilters = () => {
    router.get(route('gages.index'), {
        search: search.value || undefined,
        department_id: selectedDepartment.value || undefined,
        status: selectedStatus.value || undefined,
        location: selectedLocation.value || undefined,
        sort_by: sortBy.value,
        sort_order: sortOrder.value,
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

// Immediate filter application for dropdowns
watch([selectedDepartment, selectedStatus, selectedLocation], applyFilters);

const clearFilters = () => {
    search.value = '';
    selectedDepartment.value = '';
    selectedStatus.value = '';
    selectedLocation.value = '';
    sortBy.value = 'gage_id';
    sortOrder.value = 'asc';
    applyFilters();
};

const toggleSort = (field) => {
    if (sortBy.value === field) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = field;
        sortOrder.value = 'asc';
    }
    applyFilters();
};

const getSortIcon = (field) => {
    if (sortBy.value !== field) return '↕️';
    return sortOrder.value === 'asc' ? '↑' : '↓';
};

const deleteGage = (id) => {
    if (confirm('Are you sure you want to delete this gage?')) {
        router.delete(route('gages.destroy', id));
    }
};

const calibrateGage = (gageId) => {
    router.get(route('calibration-records.create', { gage_id: gageId }));
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Gages" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex justify-between items-center mb-6">
                            <h1 class="text-2xl font-bold">Gages</h1>
                            <div class="flex space-x-3">
                                <!-- Subscription Status Card -->
                                <div v-if="!subscriptionInfo.hasActiveSubscription && !subscriptionInfo.onTrial" 
                                     class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg px-4 py-2 text-sm">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-blue-800 dark:text-blue-200">
                                            {{ subscriptionInfo.gageCount }}/{{ subscriptionInfo.gageLimit }} gages used
                                        </span>
                                        <Link v-if="subscriptionInfo.hasReachedLimit" 
                                              :href="route('subscription.subscribe')"
                                              class="text-blue-600 hover:text-blue-800 underline">
                                            Subscribe for unlimited
                                        </Link>
                                    </div>
                                </div>
                                
                                <!-- Subscription Success Banner -->
                                <div v-if="subscriptionInfo.hasActiveSubscription" 
                                     class="bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-lg px-4 py-2 text-sm">
                                    <span class="text-green-800 dark:text-green-200">
                                        ✓ Subscribed - Unlimited gages
                                    </span>
                                </div>

                                <!-- Trial Banner -->
                                <div v-if="subscriptionInfo.onTrial" 
                                     class="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-lg px-4 py-2 text-sm">
                                    <span class="text-yellow-800 dark:text-yellow-200">
                                        Trial active - Unlimited gages
                                    </span>
                                </div>

                                <!-- Export Buttons -->
                                <div class="flex space-x-2">
                                    <a 
                                        :href="route('export.gages.csv')"
                                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    >
                                        📊 Export CSV
                                    </a>
                                    <a 
                                        :href="route('export.gages.pdf')"
                                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    >
                                        📄 Export PDF
                                    </a>
                                </div>

                                <!-- Add Gage Button Logic -->
                                <div v-if="hasDepartments && subscriptionInfo.canAddGages">
                                    <Link :href="route('gages.create')">
                                        <PrimaryButton>Add Gage</PrimaryButton>
                                    </Link>
                                </div>
                                
                                <!-- No Departments Warning -->
                                <div v-else-if="!hasDepartments" class="relative">
                                    <button 
                                        disabled
                                        class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-500 dark:text-gray-400 uppercase tracking-widest cursor-not-allowed"
                                        title="Create a department first"
                                    >
                                        Add Gage (No Departments)
                                    </button>
                                    <Link :href="route('departments.create')" 
                                          class="ml-2 text-sm text-blue-600 hover:text-blue-800 underline">
                                        Create Department First
                                    </Link>
                                </div>
                                
                                <!-- Subscription Limit Reached -->
                                <div v-else class="relative">
                                    <button 
                                        disabled
                                        class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-500 dark:text-gray-400 uppercase tracking-widest cursor-not-allowed"
                                        title="Subscription required to add more gages"
                                    >
                                        Add Gage (Limit Reached)
                                    </button>
                                    <Link :href="route('subscription.subscribe')" 
                                          class="ml-2 text-sm text-blue-600 hover:text-blue-800 underline">
                                        Subscribe to add more
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Limit Reached Warning -->
                        <div v-if="subscriptionInfo.hasReachedLimit && !subscriptionInfo.hasActiveSubscription && !subscriptionInfo.onTrial" 
                             class="mb-6 bg-amber-50 dark:bg-amber-900 border border-amber-200 dark:border-amber-700 rounded-lg p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-amber-800 dark:text-amber-200">
                                        Gage Limit Reached
                                    </h3>
                                    <div class="mt-2 text-sm text-amber-700 dark:text-amber-300">
                                        <p>You've reached the limit of {{ subscriptionInfo.gageLimit }} gages for free accounts. 
                                           <Link :href="route('subscription.subscribe')" 
                                                 class="font-medium underline hover:text-amber-600">
                                               Subscribe to our monthly plan
                                           </Link> 
                                           to add unlimited gages and unlock all features.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="gages.length === 0" class="text-center py-8 text-gray-500">
                            No gages found. Create your first gage to get started.
                        </div>

                        <div v-else class="overflow-x-auto">
                            <!-- Filters -->
                            <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                                <!-- Search Input -->
                                <div class="min-w-[200px] mb-2 sm:mb-0">
                                    <TextInput 
                                        v-model="search" 
                                        placeholder="Search gages..."
                                        class="w-full"
                                    />
                                </div>

                                <!-- Department Filter -->
                                <div class="min-w-[150px] mb-2 sm:mb-0">
                                    <select 
                                        v-model="selectedDepartment" 
                                        @change="applyFilters"
                                        class="block w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring focus:ring-opacity-50"
                                    >
                                        <option value="">All Departments</option>
                                        <option v-for="department in props.departments" 
                                                :key="department.id" 
                                                :value="department.id">
                                            {{ department.name }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Status Filter -->
                                <div class="min-w-[150px] mb-2 sm:mb-0">
                                    <select 
                                        v-model="selectedStatus" 
                                        @change="applyFilters"
                                        class="block w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring focus:ring-opacity-50"
                                    >
                                        <option value="">All Statuses</option>
                                        <option value="overdue">Overdue</option>
                                        <option value="due_soon">Due Soon</option>
                                        <option value="on_schedule">On Schedule</option>
                                        <option value="unknown">Unknown</option>
                                    </select>
                                </div>

                                <!-- Location Filter -->
                                <div class="min-w-[150px] mb-2 sm:mb-0">
                                    <select 
                                        v-model="selectedLocation" 
                                        @change="applyFilters"
                                        class="block w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring focus:ring-opacity-50"
                                    >
                                        <option value="">All Locations</option>
                                        <option v-for="location in locations" 
                                                :key="location" 
                                                :value="location">
                                            {{ location }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Clear Filters Button -->
                                <div>
                                    <button 
                                        @click="clearFilters"
                                        class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none transition ease-in-out duration-150"
                                    >
                                        Clear Filters
                                    </button>
                                </div>
                            </div>

                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th @click="toggleSort('gage_id')" 
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer">
                                            Gage ID 
                                            <span class="text-gray-400 text-xs" v-if="sortBy === 'gage_id'">{{ getSortIcon('gage_id') }}</span>
                                        </th>
                                        <th @click="toggleSort('description')" 
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer">
                                            Description 
                                            <span class="text-gray-400 text-xs" v-if="sortBy === 'description'">{{ getSortIcon('description') }}</span>
                                        </th>
                                        <th @click="toggleSort('location')" 
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer">
                                            Location 
                                            <span class="text-gray-400 text-xs" v-if="sortBy === 'location'">{{ getSortIcon('location') }}</span>
                                        </th>
                                        <th @click="toggleSort('custodian')" 
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer">
                                            Custodian 
                                            <span class="text-gray-400 text-xs" v-if="sortBy === 'custodian'">{{ getSortIcon('custodian') }}</span>
                                        </th>
                                        <th @click="toggleSort('last_calibrated')" 
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer">
                                            Last Calibration 
                                            <span class="text-gray-400 text-xs" v-if="sortBy === 'last_calibrated'">{{ getSortIcon('last_calibrated') }}</span>
                                        </th>
                                        <th @click="toggleSort('next_due_date')" 
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer">
                                            Next Due Date 
                                            <span class="text-gray-400 text-xs" v-if="sortBy === 'next_due_date'">{{ getSortIcon('next_due_date') }}</span>
                                        </th>
                                        <th @click="toggleSort('status')" 
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer">
                                            Status 
                                            <span class="text-gray-400 text-xs" v-if="sortBy === 'status'">{{ getSortIcon('status') }}</span>
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr 
                                        v-for="gage in gages" 
                                        :key="gage.id"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ gage.gage_id }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                                {{ gage.description || 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                                {{ gage.location }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                                {{ gage.custodian }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                                <span v-if="gage.calibration_records && gage.calibration_records.length > 0">
                                                    {{ new Date(gage.calibration_records[0].calibrated_at).toLocaleDateString() }}
                                                </span>
                                                <span v-else class="text-gray-500 italic">
                                                    Never calibrated
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                                <span v-if="gage.next_due_date">
                                                    {{ new Date(gage.next_due_date).toLocaleDateString() }}
                                                </span>
                                                <span v-else class="text-gray-500 italic">
                                                    Not set
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span 
                                                v-if="gage.status" 
                                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
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
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-3">
                                                <!-- View Icon -->
                                                <Link 
                                                    :href="route('gages.show', gage.id)"
                                                    class="text-blue-600 hover:text-blue-800 transition-colors"
                                                    title="View gage details"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                </Link>

                                                <!-- Edit Icon -->
                                                <Link 
                                                    :href="route('gages.edit', gage.id)"
                                                    class="text-gray-600 hover:text-gray-800 transition-colors"
                                                    title="Edit gage"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </Link>

                                                <!-- Calibrate Icon -->
                                                <button 
                                                    @click="calibrateGage(gage.id)"
                                                    class="text-green-600 hover:text-green-800 transition-colors"
                                                    :title="gage.calibration_records && gage.calibration_records.length > 0 ? 'Recalibrate gage' : 'Calibrate gage'"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                                    </svg>
                                                </button>

                                                <!-- Delete Icon -->
                                                <button 
                                                    @click="deleteGage(gage.id)"
                                                    class="text-red-600 hover:text-red-800 transition-colors"
                                                    title="Delete gage"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1-1H8a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- No Departments Warning Banner -->
                        <div v-if="!hasDepartments" 
                             class="mb-6 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                                        Create a Department First
                                    </h3>
                                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                                        <p>Before you can add gages, you need to create at least one department. Departments help organize your gages by area, function, or responsibility.
                                           <Link :href="route('departments.create')" 
                                                 class="font-medium underline hover:text-blue-600">
                                               Create your first department now
                                           </Link>
                                        </p>
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