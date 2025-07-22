<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    activity: Object,
});

const formatDate = (date) => {
    return new Date(date).toLocaleString();
};

const getModelName = (subjectType) => {
    if (!subjectType) return 'Unknown';
    const parts = subjectType.split('\\');
    return parts[parts.length - 1];
};

const getEventColor = (event) => {
    const colors = {
        'created': 'bg-green-100 text-green-800',
        'updated': 'bg-blue-100 text-blue-800',
        'deleted': 'bg-red-100 text-red-800',
    };
    return colors[event] || 'bg-gray-100 text-gray-800';
};

const getEventIcon = (event) => {
    const icons = {
        'created': '➕',
        'updated': '✏️',
        'deleted': '🗑️',
    };
    return icons[event] || '📝';
};

const formatValue = (value) => {
    if (value === null || value === undefined) {
        return 'null';
    }
    if (typeof value === 'object') {
        return JSON.stringify(value, null, 2);
    }
    return String(value);
};
</script>

<template>
    <Head title="Audit Log Detail" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <Link 
                    :href="route('admin.audit.index')"
                    class="text-blue-600 hover:text-blue-800 mr-4"
                >
                    ← Back to Audit Log
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Audit Log Detail
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Overview Section -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Overview</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Event</label>
                                    <span :class="[
                                        'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium mt-1',
                                        getEventColor(activity.event)
                                    ]">
                                        {{ getEventIcon(activity.event) }} {{ activity.event }}
                                    </span>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Model</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ getModelName(activity.subject_type) }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Subject</label>
                                    <p class="mt-1 text-sm text-gray-900">
                                        <span v-if="activity.subject">
                                            {{ activity.subject.name || `#${activity.subject.id}` }}
                                        </span>
                                        <span v-else class="text-gray-500 italic">Deleted</span>
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">User</label>
                                    <p class="mt-1 text-sm text-gray-900">
                                        <span v-if="activity.causer">
                                            {{ activity.causer.name }} ({{ activity.causer.email }})
                                        </span>
                                        <span v-else class="text-gray-500 italic">System</span>
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Date & Time</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ formatDate(activity.created_at) }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Activity ID</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ activity.id }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Changes Section -->
                        <div v-if="activity.properties && (activity.properties.attributes || activity.properties.old)" class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Changes</h3>
                            
                            <!-- For created events, show new attributes -->
                            <div v-if="activity.event === 'created' && activity.properties.attributes" class="mb-6">
                                <h4 class="text-md font-medium text-green-800 mb-3">New Values</h4>
                                <div class="bg-green-50 rounded-lg p-4">
                                    <div class="grid grid-cols-1 gap-3">
                                        <div v-for="(value, key) in activity.properties.attributes" :key="key" class="flex items-start">
                                            <span class="inline-block w-32 text-sm font-medium text-gray-700 mr-4">{{ key }}:</span>
                                            <span class="text-sm text-gray-900 font-mono">{{ formatValue(value) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- For updated events, show old vs new -->
                            <div v-if="activity.event === 'updated'" class="space-y-6">
                                <div v-if="activity.properties.old" class="mb-6">
                                    <h4 class="text-md font-medium text-red-800 mb-3">Old Values</h4>
                                    <div class="bg-red-50 rounded-lg p-4">
                                        <div class="grid grid-cols-1 gap-3">
                                            <div v-for="(value, key) in activity.properties.old" :key="key" class="flex items-start">
                                                <span class="inline-block w-32 text-sm font-medium text-gray-700 mr-4">{{ key }}:</span>
                                                <span class="text-sm text-gray-900 font-mono">{{ formatValue(value) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="activity.properties.attributes" class="mb-6">
                                    <h4 class="text-md font-medium text-green-800 mb-3">New Values</h4>
                                    <div class="bg-green-50 rounded-lg p-4">
                                        <div class="grid grid-cols-1 gap-3">
                                            <div v-for="(value, key) in activity.properties.attributes" :key="key" class="flex items-start">
                                                <span class="inline-block w-32 text-sm font-medium text-gray-700 mr-4">{{ key }}:</span>
                                                <span class="text-sm text-gray-900 font-mono">{{ formatValue(value) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- For deleted events, show old attributes -->
                            <div v-if="activity.event === 'deleted' && activity.properties.attributes" class="mb-6">
                                <h4 class="text-md font-medium text-red-800 mb-3">Deleted Values</h4>
                                <div class="bg-red-50 rounded-lg p-4">
                                    <div class="grid grid-cols-1 gap-3">
                                        <div v-for="(value, key) in activity.properties.attributes" :key="key" class="flex items-start">
                                            <span class="inline-block w-32 text-sm font-medium text-gray-700 mr-4">{{ key }}:</span>
                                            <span class="text-sm text-gray-900 font-mono">{{ formatValue(value) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Raw Data Section -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Raw Activity Data</h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <pre class="text-sm text-gray-800 overflow-x-auto">{{ JSON.stringify(activity, null, 2) }}</pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>