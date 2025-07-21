<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    departments: Array,
});

const deleteDepartment = (id) => {
    if (confirm('Are you sure you want to delete this department?')) {
        router.delete(route('departments.destroy', id));
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Departments" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex justify-between items-center mb-6">
                            <h1 class="text-2xl font-bold">Departments</h1>
                            <Link :href="route('departments.create')">
                                <PrimaryButton>Add Department</PrimaryButton>
                            </Link>
                        </div>

                        <div v-if="departments.length === 0" class="text-center py-8 text-gray-500">
                            No departments found. Create your first department to get started.
                        </div>

                        <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            <div 
                                v-for="department in departments" 
                                :key="department.id"
                                class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6"
                            >
                                <div class="flex justify-between items-start mb-4">
                                    <h3 class="text-lg font-semibold">{{ department.name }}</h3>
                                    <div class="flex space-x-2">
                                        <Link 
                                            :href="route('departments.edit', department.id)"
                                            class="text-blue-600 hover:text-blue-800 text-sm"
                                        >
                                            Edit
                                        </Link>
                                        <button 
                                            @click="deleteDepartment(department.id)"
                                            class="text-red-600 hover:text-red-800 text-sm"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>
                                
                                <div v-if="department.manager_name" class="mb-2">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Manager: {{ department.manager_name }}
                                    </p>
                                </div>
                                
                                <div v-if="department.manager_email" class="mb-4">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Email: {{ department.manager_email }}
                                    </p>
                                </div>

                                <div class="flex justify-between items-center text-sm text-gray-600 dark:text-gray-400">
                                    <span>{{ department.gages.length }} gage(s)</span>
                                    <Link 
                                        :href="route('departments.show', department.id)"
                                        class="text-blue-600 hover:text-blue-800"
                                    >
                                        View Details →
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>