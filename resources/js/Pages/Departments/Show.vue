<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    department: Object,
});

const deleteDepartment = () => {
    if (confirm('Are you sure you want to delete this department?')) {
        router.delete(route('departments.destroy', department.id));
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Department: ${department.name}`" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex justify-between items-center mb-6">
                            <h1 class="text-3xl font-bold">{{ department.name }}</h1>
                            <div class="flex space-x-3">
                                <Link :href="route('departments.edit', department.id)">
                                    <PrimaryButton>Edit Department</PrimaryButton>
                                </Link>
                                <DangerButton @click="deleteDepartment">Delete Department</DangerButton>
                            </div>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <!-- Department Details -->
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                                <h2 class="text-xl font-semibold mb-4">Department Information</h2>
                                
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Name</label>
                                        <p class="text-lg">{{ department.name }}</p>
                                    </div>
                                    
                                    <div v-if="department.manager_name">
                                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Manager</label>
                                        <p class="text-lg">{{ department.manager_name }}</p>
                                    </div>
                                    
                                    <div v-if="department.manager_email">
                                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Manager Email</label>
                                        <p class="text-lg">
                                            <a :href="`mailto:${department.manager_email}`" class="text-blue-600 hover:text-blue-800">
                                                {{ department.manager_email }}
                                            </a>
                                        </p>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Created</label>
                                        <p class="text-lg">{{ new Date(department.created_at).toLocaleDateString() }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Gages Section -->
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h2 class="text-xl font-semibold">Gages ({{ department.gages?.length || 0 }})</h2>
                                    <Link :href="route('gages.create', { department: department.id })">
                                        <PrimaryButton class="text-sm">Add Gage</PrimaryButton>
                                    </Link>
                                </div>
                                
                                <div v-if="!department.gages || department.gages.length === 0" class="text-center py-8 text-gray-500">
                                    No gages assigned to this department.
                                </div>
                                
                                <div v-else class="space-y-3">
                                    <div 
                                        v-for="gage in department.gages" 
                                        :key="gage.id"
                                        class="bg-white dark:bg-gray-600 rounded p-4 border border-gray-200 dark:border-gray-600"
                                    >
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h3 class="font-medium">{{ gage.name }}</h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">ID: {{ gage.gage_id }}</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Type: {{ gage.type }}</p>
                                            </div>
                                            <div class="text-right">
                                                <span 
                                                    :class="{
                                                        'bg-green-100 text-green-800': gage.status === 'active',
                                                        'bg-yellow-100 text-yellow-800': gage.status === 'due',
                                                        'bg-red-100 text-red-800': gage.status === 'overdue'
                                                    }"
                                                    class="px-2 py-1 rounded-full text-xs font-medium"
                                                >
                                                    {{ gage.status }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-2 flex justify-between items-center">
                                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                                Next Cal: {{ gage.next_calibration_date ? new Date(gage.next_calibration_date).toLocaleDateString() : 'N/A' }}
                                            </span>
                                            <Link 
                                                :href="route('gages.show', gage.id)"
                                                class="text-blue-600 hover:text-blue-800 text-sm"
                                            >
                                                View →
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="mt-6 flex justify-start">
                            <Link :href="route('departments.index')">
                                <PrimaryButton variant="secondary">← Back to Departments</PrimaryButton>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>