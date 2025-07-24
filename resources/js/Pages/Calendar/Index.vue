<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';

const calendarEl = ref(null);
let calendar = null;

onMounted(() => {
    calendar = new Calendar(calendarEl.value, {
        plugins: [dayGridPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,dayGridWeek'
        },
        events: {
            url: route('calendar.events'),
            method: 'GET',
            failure: function() {
                alert('There was an error while fetching events!');
            }
        },
        eventClick: function(info) {
            const gage = info.event.extendedProps.gage;
            window.location.href = route('gages.show', gage.id);
        },
        eventDidMount: function(info) {
            // Add tooltip
            info.el.setAttribute('title', 
                `${info.event.title}\nDepartment: ${info.event.extendedProps.department}\nStatus: ${info.event.extendedProps.status}`
            );
        },
        height: 'auto',
        dayMaxEvents: 3,
        moreLinkClick: 'popover'
    });
    
    calendar.render();
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Calibration Calendar" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex justify-between items-center mb-6">
                            <h1 class="text-2xl font-bold">Calibration Calendar</h1>
                            
                            <!-- Legend -->
                            <div class="flex space-x-4 text-sm">
                                <div class="flex items-center">
                                    <div class="w-4 h-4 bg-green-500 rounded mr-2"></div>
                                    <span>On Schedule</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-4 h-4 bg-amber-500 rounded mr-2"></div>
                                    <span>Due Soon</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-4 h-4 bg-red-500 rounded mr-2"></div>
                                    <span>Overdue</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <div ref="calendarEl"></div>
                        </div>

                        <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                            <p>Click on any event to view gage details. Use the navigation buttons to browse different months.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>