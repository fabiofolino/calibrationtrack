<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    calibrationRecord: Object,
});

const form = useForm({
    calibration_record_id: props.calibrationRecord.id,
    risk_assessment: '',
    corrective_actions: '',
});

const submit = () => {
    form.post(route('gage-tolerance-records.store'));
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Create Out-of-Tolerance Record" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex items-center mb-6">
                            <Link 
                                :href="route('gage-tolerance-records.index')"
                                class="text-blue-600 hover:text-blue-800 mr-4"
                            >
                                ← Back to Tolerance Records
                            </Link>
                            <h1 class="text-2xl font-bold">Create Out-of-Tolerance Record</h1>
                        </div>

                        <!-- Calibration Record Info -->
                        <div class="bg-red-50 dark:bg-red-900 rounded-lg p-6 mb-6">
                            <h2 class="text-lg font-semibold mb-4 text-red-800 dark:text-red-200">Calibration Record</h2>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <span class="font-medium">Gage ID:</span>
                                    <span class="ml-2">{{ calibrationRecord.gage.gage_id }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">Description:</span>
                                    <span class="ml-2">{{ calibrationRecord.gage.description || 'N/A' }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">Calibration Date:</span>
                                    <span class="ml-2">{{ new Date(calibrationRecord.calibrated_at).toLocaleDateString() }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">Measured Value:</span>
                                    <span class="ml-2">{{ calibrationRecord.measured_value }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">Calibrated Value:</span>
                                    <span class="ml-2">{{ calibrationRecord.calibrated_value }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">Technician:</span>
                                    <span class="ml-2">{{ calibrationRecord.user?.name }}</span>
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent="submit" class="max-w-2xl">
                            <div class="mb-6">
                                <InputLabel for="risk_assessment" value="Risk Assessment *" />
                                <textarea
                                    id="risk_assessment"
                                    v-model="form.risk_assessment"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    rows="4"
                                    required
                                    placeholder="Describe the potential risks associated with this out-of-tolerance condition..."
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.risk_assessment" />
                            </div>

                            <div class="mb-6">
                                <InputLabel for="corrective_actions" value="Corrective Actions *" />
                                <textarea
                                    id="corrective_actions"
                                    v-model="form.corrective_actions"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    rows="4"
                                    required
                                    placeholder="Describe the corrective actions taken or planned to address this issue..."
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.corrective_actions" />
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton 
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    Create Tolerance Record
                                </PrimaryButton>
                                
                                <Link 
                                    :href="route('gage-tolerance-records.index')"
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