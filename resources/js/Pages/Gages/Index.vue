<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    gages: Array,
});

const deleteGage = (id) => {
    if (confirm('Are you sure you want to delete this gage?')) {
        router.delete(route('gages.destroy', id));
    }
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
                                <Link :href="route('gages.create')">
                                    <PrimaryButton>Add Gage</PrimaryButton>
                                </Link>
                            </div>
                        </div>

                        <div v-if="gages.length === 0" class="text-center py-8 text-gray-500">
                            No gages found. Create your first gage to get started.
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Gage ID
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Description
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Location
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Custodian
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Last Calibration
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Next Due Date
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Status
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
                                            <div class="flex justify-end space-x-2">
                                                <Link 
                                                    :href="route('gages.show', gage.id)"
                                                    class="text-blue-600 hover:text-blue-800"
                                                >
                                                    View
                                                </Link>
                                                <Link 
                                                    :href="route('gages.edit', gage.id)"
                                                    class="text-blue-600 hover:text-blue-800"
                                                >
                                                    Edit
                                                </Link>
                                                <button 
                                                    @click="deleteGage(gage.id)"
                                                    class="text-red-600 hover:text-red-800"
                                                >
                                                    Delete
                                                </button>
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