<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    gage: Object,
    departments: Array,
});

const form = useForm({
    department_id: props.gage.department_id,
    name: props.gage.name,
    serial_number: props.gage.serial_number,
    frequency_days: props.gage.frequency_days,
});

const submit = () => {
    form.put(route('gages.update', props.gage.id));
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Edit Gage" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex items-center mb-6">
                            <Link 
                                :href="route('gages.index')"
                                class="text-blue-600 hover:text-blue-800 mr-4"
                            >
                                ← Back to Gages
                            </Link>
                            <h1 class="text-2xl font-bold">Edit Gage</h1>
                        </div>

                        <form @submit.prevent="submit" class="max-w-md">
                            <div class="mb-4">
                                <InputLabel for="department_id" value="Department *" />
                                <select
                                    id="department_id"
                                    v-model="form.department_id"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    required
                                >
                                    <option value="">Select a department</option>
                                    <option 
                                        v-for="department in departments" 
                                        :key="department.id" 
                                        :value="department.id"
                                    >
                                        {{ department.name }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.department_id" />
                            </div>

                            <div class="mb-4">
                                <InputLabel for="name" value="Gage Name *" />
                                <TextInput
                                    id="name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.name"
                                    required
                                    autofocus
                                />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <div class="mb-4">
                                <InputLabel for="serial_number" value="Serial Number *" />
                                <TextInput
                                    id="serial_number"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.serial_number"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.serial_number" />
                            </div>

                            <div class="mb-6">
                                <InputLabel for="frequency_days" value="Calibration Frequency (Days) *" />
                                <TextInput
                                    id="frequency_days"
                                    type="number"
                                    class="mt-1 block w-full"
                                    v-model="form.frequency_days"
                                    required
                                    min="1"
                                />
                                <InputError class="mt-2" :message="form.errors.frequency_days" />
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton 
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    Update Gage
                                </PrimaryButton>
                                
                                <Link 
                                    :href="route('gages.index')"
                                    class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
                                >
                                    Cancel
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>