<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    toleranceRecords: Object,
});
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
                                            <div class="flex justify-end space-x-2">
                                                <Link 
                                                    :href="route('gage-tolerance-records.show', record.id)"
                                                    class="text-blue-600 hover:text-blue-800"
                                                >
                                                    View
                                                </Link>
                                                <Link 
                                                    :href="route('gage-tolerance-records.edit', record.id)"
                                                    class="text-blue-600 hover:text-blue-800"
                                                >
                                                    Edit
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