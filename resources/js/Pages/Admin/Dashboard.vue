<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
});
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Admin Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 mb-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <h3 class="text-lg font-medium text-gray-900">Total Users</h3>
                                    <p class="text-3xl font-bold text-blue-600">{{ stats.total_users }}</p>
                                </div>
                                <div class="text-blue-500 text-4xl">👥</div>
                            </div>
                            <div class="mt-4 text-sm text-gray-600">
                                {{ stats.active_users }} active, {{ stats.suspended_users }} suspended
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <h3 class="text-lg font-medium text-gray-900">Administrators</h3>
                                    <p class="text-3xl font-bold text-purple-600">{{ stats.admin_users }}</p>
                                </div>
                                <div class="text-purple-500 text-4xl">👑</div>
                            </div>
                            <div class="mt-4 text-sm text-gray-600">
                                Company administrators
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <h3 class="text-lg font-medium text-gray-900">Total Gages</h3>
                                    <p class="text-3xl font-bold text-green-600">{{ stats.total_gages }}</p>
                                </div>
                                <div class="text-green-500 text-4xl">📏</div>
                            </div>
                            <div class="mt-4 text-sm text-gray-600">
                                Across {{ stats.total_departments }} departments
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Admin Actions -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- User Management -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">User Management</h3>
                                    <p class="mt-1 text-sm text-gray-600">
                                        Manage company users, roles, and permissions
                                    </p>
                                </div>
                                <div class="text-blue-500 text-6xl">👥</div>
                            </div>
                            <div class="mt-6">
                                <Link 
                                    :href="route('admin.users.index')"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    Manage Users
                                </Link>
                            </div>
                            <div class="mt-4 space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Invite new users</span>
                                    <span class="text-gray-900">✓</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Suspend/reactivate users</span>
                                    <span class="text-gray-900">✓</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Manage user roles</span>
                                    <span class="text-gray-900">✓</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Audit Logs -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">Audit Logs</h3>
                                    <p class="mt-1 text-sm text-gray-600">
                                        View system activity and change history
                                    </p>
                                </div>
                                <div class="text-green-500 text-6xl">📋</div>
                            </div>
                            <div class="mt-6">
                                <Link 
                                    :href="route('admin.audit.index')"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    View Audit Logs
                                </Link>
                            </div>
                            <div class="mt-4 space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Track all changes</span>
                                    <span class="text-gray-900">✓</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Filter by user or model</span>
                                    <span class="text-gray-900">✓</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Detailed change logs</span>
                                    <span class="text-gray-900">✓</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>