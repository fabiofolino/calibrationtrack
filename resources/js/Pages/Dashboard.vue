<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import GageStatusChart from '@/Components/GageStatusChart.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    analytics: Object
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

const getStatusBadgeClass = (status) => {
    const classes = {
        'on_schedule': 'bg-green-100 text-green-800',
        'due_soon': 'bg-yellow-100 text-yellow-800',
        'overdue': 'bg-red-100 text-red-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getStatusLabel = (status) => {
    const labels = {
        'on_schedule': '✅ On Schedule',
        'due_soon': '⚠️ Due Soon',
        'overdue': '🔴 Overdue'
    };
    return labels[status] || 'Unknown';
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- KPI Cards -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                    <!-- Total Gages -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <h3 class="text-lg font-medium text-gray-900">Total Gages</h3>
                                    <p class="text-3xl font-bold text-blue-600">{{ analytics.total_gages }}</p>
                                </div>
                                <div class="text-blue-500 text-4xl">📏</div>
                            </div>
                        </div>
                    </div>

                    <!-- Calibrations This Month -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <h3 class="text-lg font-medium text-gray-900">Calibrations This Month</h3>
                                    <p class="text-3xl font-bold text-green-600">{{ analytics.calibration_records_this_month }}</p>
                                </div>
                                <div class="text-green-500 text-4xl">📅</div>
                            </div>
                        </div>
                    </div>

                    <!-- Overdue Gages -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <h3 class="text-lg font-medium text-gray-900">Overdue</h3>
                                    <p class="text-3xl font-bold text-red-600">{{ analytics.gages_by_status.overdue }}</p>
                                </div>
                                <div class="text-red-500 text-4xl">🔴</div>
                            </div>
                        </div>
                    </div>

                    <!-- Due Soon -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <h3 class="text-lg font-medium text-gray-900">Due Soon</h3>
                                    <p class="text-3xl font-bold text-yellow-600">{{ analytics.gages_by_status.due_soon }}</p>
                                </div>
                                <div class="text-yellow-500 text-4xl">⚠️</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts and Tables Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Gage Status Chart -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Gages by Status</h3>
                            <GageStatusChart :data="analytics.gages_by_status" />
                        </div>
                    </div>

                    <!-- Department Statistics -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Department with Most Open Items</h3>
                            <div v-if="analytics.department_with_most_open" class="text-center py-8">
                                <div class="text-4xl mb-2">🏢</div>
                                <h4 class="text-xl font-semibold text-gray-900">{{ analytics.department_with_most_open.name }}</h4>
                                <p class="text-sm text-gray-600 mt-2">
                                    {{ analytics.department_with_most_open.open_gages }} open items
                                    out of {{ analytics.department_with_most_open.total_gages }} total gages
                                </p>
                            </div>
                            <div v-else class="text-center py-8 text-gray-500">
                                No departments with open items
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity and Upcoming Calibrations -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Recently Calibrated -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Recently Calibrated</h3>
                            <div class="space-y-3">
                                <div v-for="record in analytics.recently_calibrated" :key="record.id" 
                                     class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ record.gage.name }}</p>
                                        <p class="text-sm text-gray-600">{{ record.gage.department.name }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-gray-900">{{ formatDate(record.calibrated_at) }}</p>
                                        <p class="text-xs text-gray-500">by {{ record.user.name }}</p>
                                    </div>
                                </div>
                                <div v-if="analytics.recently_calibrated.length === 0" class="text-center py-4 text-gray-500">
                                    No recent calibrations
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Calibrations -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Upcoming Calibrations (30 days)</h3>
                            <div class="space-y-3">
                                <div v-for="gage in analytics.upcoming_calibrations" :key="gage.id"
                                     class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
                                    <div>
                                        <Link :href="`/gages/${gage.id}`" class="font-medium text-blue-600 hover:text-blue-800">
                                            {{ gage.name }}
                                        </Link>
                                        <p class="text-sm text-gray-600">{{ gage.department_name }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-gray-900">{{ formatDate(gage.next_due_date) }}</p>
                                        <span :class="[
                                            'inline-flex px-2 py-1 text-xs font-medium rounded-full',
                                            getStatusBadgeClass(gage.status)
                                        ]">
                                            {{ gage.days_until_due }} days
                                        </span>
                                    </div>
                                </div>
                                <div v-if="analytics.upcoming_calibrations.length === 0" class="text-center py-4 text-gray-500">
                                    No upcoming calibrations
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
