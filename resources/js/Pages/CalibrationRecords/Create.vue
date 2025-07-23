<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    gages: Array,
    gage_id: String,
});

const form = useForm({
    gage_id: props.gage_id || '',
    measured_value: '',
    calibrated_value: '',
    calibrated_at: new Date().toISOString().slice(0, 16), // Current datetime in YYYY-MM-DDTHH:MM format
    cert_file: null,
});

const submit = () => {
    form.post(route('calibration-records.store'));
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Create Calibration Record" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex items-center mb-6">
                            <Link 
                                :href="route('calibration-records.index', gage_id ? { gage_id } : {})"
                                class="text-blue-600 hover:text-blue-800 mr-4"
                            >
                                ← Back to Calibration Records
                            </Link>
                            <h1 class="text-2xl font-bold">Create Calibration Record</h1>
                        </div>

                        <form @submit.prevent="submit" class="max-w-md">
                            <div class="mb-4">
                                <InputLabel for="gage_id" value="Gage *" />
                                <select
                                    id="gage_id"
                                    v-model="form.gage_id"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    required
                                    :disabled="!!gage_id"
                                >
                                    <option value="">Select a gage</option>
                                    <option 
                                        v-for="gage in gages" 
                                        :key="gage.id" 
                                        :value="gage.id"
                                    >
                                        {{ gage.name }} ({{ gage.serial_number }}) - {{ gage.department.name }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.gage_id" />
                            </div>

                            <div class="mb-4">
                                <InputLabel for="measured_value" value="Measured Value *" />
                                <TextInput
                                    id="measured_value"
                                    type="number"
                                    step="0.0001"
                                    class="mt-1 block w-full"
                                    v-model="form.measured_value"
                                    required
                                    autofocus
                                />
                                <InputError class="mt-2" :message="form.errors.measured_value" />
                            </div>

                            <div class="mb-4">
                                <InputLabel for="calibrated_value" value="Calibrated Value *" />
                                <TextInput
                                    id="calibrated_value"
                                    type="number"
                                    step="0.0001"
                                    class="mt-1 block w-full"
                                    v-model="form.calibrated_value"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.calibrated_value" />
                            </div>

                            <div class="mb-6">
                                <InputLabel for="calibrated_at" value="Calibration Date & Time *" />
                                <input
                                    id="calibrated_at"
                                    type="datetime-local"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    v-model="form.calibrated_at"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.calibrated_at" />
                            </div>

                            <div class="mb-6">
                                <InputLabel for="cert_file" value="Certificate (PDF)" />
                                <input
                                    id="cert_file"
                                    type="file"
                                    accept=".pdf"
                                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                    @change="form.cert_file = $event.target.files[0]"
                                />
                                <p class="mt-1 text-sm text-gray-500">Optional: Upload a PDF certificate (max 10MB)</p>
                                <InputError class="mt-2" :message="form.errors.cert_file" />
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton 
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    Create Calibration Record
                                </PrimaryButton>
                                
                                <Link 
                                    :href="route('calibration-records.index', gage_id ? { gage_id } : {})"
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