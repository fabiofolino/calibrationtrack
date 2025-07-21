<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    gage: Object,
    checkouts: Array,
});
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Checkout History: ${gage.name}`" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex items-center mb-6">
                            <Link 
                                :href="route('gages.show', gage.id)"
                                class="text-blue-600 hover:text-blue-800 mr-4"
                            >
                                ← Back to Gage Details
                            </Link>
                            <h1 class="text-2xl font-bold">Checkout History: {{ gage.name }}</h1>
                        </div>

                        <!-- Gage Summary -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-6">
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="font-medium">Serial Number:</span>
                                    <span class="ml-2">{{ gage.serial_number }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">Department:</span>
                                    <span class="ml-2">{{ gage.department.name }}</span>
                                </div>
                            </div>
                        </div>

                        <div v-if="checkouts.length === 0" class="text-center py-8 text-gray-500">
                            No checkout history found for this gage.
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            User
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Checked Out
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Checked In
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Duration
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Notes
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr 
                                        v-for="checkout in checkouts" 
                                        :key="checkout.id"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ checkout.user.name }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                                {{ new Date(checkout.checked_out_at).toLocaleDateString() }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ new Date(checkout.checked_out_at).toLocaleTimeString() }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div v-if="checkout.checked_in_at" class="text-sm text-gray-900 dark:text-gray-100">
                                                {{ new Date(checkout.checked_in_at).toLocaleDateString() }}
                                            </div>
                                            <div v-if="checkout.checked_in_at" class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ new Date(checkout.checked_in_at).toLocaleTimeString() }}
                                            </div>
                                            <span v-else class="text-sm text-yellow-600 dark:text-yellow-400">
                                                Still checked out
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            <span v-if="checkout.checked_in_at">
                                                {{ 
                                                    Math.round(
                                                        (new Date(checkout.checked_in_at) - new Date(checkout.checked_out_at)) / (1000 * 60 * 60 * 24)
                                                    )
                                                }} days
                                            </span>
                                            <span v-else>
                                                {{ 
                                                    Math.round(
                                                        (new Date() - new Date(checkout.checked_out_at)) / (1000 * 60 * 60 * 24)
                                                    )
                                                }} days (ongoing)
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div v-if="checkout.notes" class="text-sm text-gray-900 dark:text-gray-100 max-w-xs truncate">
                                                {{ checkout.notes }}
                                            </div>
                                            <span v-else class="text-sm text-gray-500 italic">
                                                No notes
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span 
                                                v-if="checkout.checked_in_at"
                                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800"
                                            >
                                                Returned
                                            </span>
                                            <span 
                                                v-else
                                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800"
                                            >
                                                Active
                                            </span>
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