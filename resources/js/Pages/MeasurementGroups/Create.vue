<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    calibrationRecord: Object,
});

const form = useForm({
    calibration_record_id: props.calibrationRecord.id,
    group_number: '',
    group_name: '',
    type: 'tolerance_percent',
    plus_value: 0.0005,
    minus_value: 0.0005,
    mask_min: null,
    mask_max: null,
    units: 'F',
    notes: '',
    show_sequence: false,
    show_description: false,
    auto_fill_after: false,
    show_uncertainty: false,
    show_standards: false,
    measurements: [
        { nominal: 0.0050, description: '' },
        { nominal: 0.0100, description: '' },
        { nominal: 0.0150, description: '' },
        { nominal: 0.0200, description: '' },
        { nominal: 0.0250, description: '' }
    ]
});

const addMeasurement = () => {
    form.measurements.push({ nominal: 0.0000, description: '' });
};

const removeMeasurement = (index) => {
    if (form.measurements.length > 1) {
        form.measurements.splice(index, 1);
    }
};

const calculateLimits = (nominal) => {
    switch (form.type) {
        case 'tolerance_percent':
            const tolerance = nominal * (form.plus_value / 100);
            return {
                upper: nominal + tolerance,
                lower: nominal - tolerance
            };
        case 'tolerance_plus_minus':
            return {
                upper: nominal + form.plus_value,
                lower: nominal - form.minus_value
            };
        case 'limits':
            return {
                upper: form.mask_max || nominal,
                lower: form.mask_min || nominal
            };
        default:
            return { upper: nominal, lower: nominal };
    }
};

const submit = () => {
    form.post(route('measurement-groups.store'));
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Add Measurement Group" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex items-center mb-6">
                            <Link 
                                :href="route('calibration-records.show', calibrationRecord.id)"
                                class="text-blue-600 hover:text-blue-800 mr-4"
                            >
                                ← Back to Calibration Record
                            </Link>
                            <h1 class="text-2xl font-bold">Add Measurement Group</h1>
                        </div>

                        <!-- Calibration Record Info -->
                        <div class="bg-blue-50 dark:bg-blue-900 rounded-lg p-6 mb-6">
                            <h2 class="text-lg font-semibold mb-4 text-blue-800 dark:text-blue-200">Calibration Record</h2>
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
                                    <span class="font-medium">Technician:</span>
                                    <span class="ml-2">{{ calibrationRecord.user?.name }}</span>
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Group Configuration -->
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                                <h3 class="text-lg font-semibold mb-4">Group Configuration</h3>
                                
                                <div class="grid md:grid-cols-3 gap-4 mb-4">
                                    <div>
                                        <InputLabel for="group_number" value="Group Number *" />
                                        <TextInput
                                            id="group_number"
                                            v-model="form.group_number"
                                            class="mt-1 block w-full"
                                            required
                                        />
                                        <InputError class="mt-2" :message="form.errors.group_number" />
                                    </div>

                                    <div>
                                        <InputLabel for="group_name" value="Group Name *" />
                                        <TextInput
                                            id="group_name"
                                            v-model="form.group_name"
                                            class="mt-1 block w-full"
                                            required
                                        />
                                        <InputError class="mt-2" :message="form.errors.group_name" />
                                    </div>

                                    <div>
                                        <InputLabel for="units" value="Units" />
                                        <select
                                            id="units"
                                            v-model="form.units"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                        >
                                            <option value="F">F (Fahrenheit)</option>
                                            <option value="C">C (Celsius)</option>
                                            <option value="V">V (Volts)</option>
                                            <option value="A">A (Amperes)</option>
                                            <option value="Ω">Ω (Ohms)</option>
                                            <option value="Hz">Hz (Hertz)</option>
                                            <option value="psi">psi (Pressure)</option>
                                            <option value="in">in (Inches)</option>
                                            <option value="mm">mm (Millimeters)</option>
                                        </select>
                                        <InputError class="mt-2" :message="form.errors.units" />
                                    </div>
                                </div>

                                <!-- Tolerance Type -->
                                <div class="mb-4">
                                    <InputLabel for="type" value="Tolerance Type *" />
                                    <select
                                        id="type"
                                        v-model="form.type"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    >
                                        <option value="tolerance_percent">Tolerance (%)</option>
                                        <option value="tolerance_plus_minus">Tolerance (±)</option>
                                        <option value="limits">Fixed Limits</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.type" />
                                </div>

                                <!-- Tolerance Values -->
                                <div v-if="form.type === 'tolerance_percent'" class="grid md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <InputLabel for="plus_value" value="Tolerance (%) *" />
                                        <TextInput
                                            id="plus_value"
                                            type="number"
                                            step="0.0001"
                                            v-model="form.plus_value"
                                            class="mt-1 block w-full"
                                        />
                                        <InputError class="mt-2" :message="form.errors.plus_value" />
                                    </div>
                                </div>

                                <div v-if="form.type === 'tolerance_plus_minus'" class="grid md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <InputLabel for="plus_value" value="Plus (+) *" />
                                        <TextInput
                                            id="plus_value"
                                            type="number"
                                            step="0.0001"
                                            v-model="form.plus_value"
                                            class="mt-1 block w-full"
                                        />
                                        <InputError class="mt-2" :message="form.errors.plus_value" />
                                    </div>
                                    <div>
                                        <InputLabel for="minus_value" value="Minus (-) *" />
                                        <TextInput
                                            id="minus_value"
                                            type="number"
                                            step="0.0001"
                                            v-model="form.minus_value"
                                            class="mt-1 block w-full"
                                        />
                                        <InputError class="mt-2" :message="form.errors.minus_value" />
                                    </div>
                                </div>

                                <div v-if="form.type === 'limits'" class="grid md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <InputLabel for="mask_min" value="Lower Limit *" />
                                        <TextInput
                                            id="mask_min"
                                            type="number"
                                            step="0.0001"
                                            v-model="form.mask_min"
                                            class="mt-1 block w-full"
                                        />
                                        <InputError class="mt-2" :message="form.errors.mask_min" />
                                    </div>
                                    <div>
                                        <InputLabel for="mask_max" value="Upper Limit *" />
                                        <TextInput
                                            id="mask_max"
                                            type="number"
                                            step="0.0001"
                                            v-model="form.mask_max"
                                            class="mt-1 block w-full"
                                        />
                                        <InputError class="mt-2" :message="form.errors.mask_max" />
                                    </div>
                                </div>

                                <!-- Display Options -->
                                <div class="grid md:grid-cols-3 gap-4 mb-4">
                                    <label class="flex items-center">
                                        <input
                                            type="checkbox"
                                            v-model="form.show_sequence"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                        >
                                        <span class="ml-2 text-sm">Show Sequence</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input
                                            type="checkbox"
                                            v-model="form.show_description"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                        >
                                        <span class="ml-2 text-sm">Show Description</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input
                                            type="checkbox"
                                            v-model="form.auto_fill_after"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                        >
                                        <span class="ml-2 text-sm">Auto Fill After</span>
                                    </label>
                                </div>

                                <div class="grid md:grid-cols-2 gap-4">
                                    <label class="flex items-center">
                                        <input
                                            type="checkbox"
                                            v-model="form.show_uncertainty"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                        >
                                        <span class="ml-2 text-sm">Show Uncertainty</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input
                                            type="checkbox"
                                            v-model="form.show_standards"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                        >
                                        <span class="ml-2 text-sm">Show Standards</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Measurements Table -->
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-semibold">Measurement Points</h3>
                                    <button
                                        type="button"
                                        @click="addMeasurement"
                                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm"
                                    >
                                        + Add Point
                                    </button>
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                        <thead>
                                            <tr class="bg-gray-100 dark:bg-gray-600">
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                                    #
                                                </th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                                    Nominal
                                                </th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                                    Upper Limit
                                                </th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                                    Lower Limit
                                                </th>
                                                <th v-if="form.show_description" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                                    Description
                                                </th>
                                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                                    Actions
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                            <tr 
                                                v-for="(measurement, index) in form.measurements" 
                                                :key="index"
                                                class="hover:bg-gray-50 dark:hover:bg-gray-600"
                                            >
                                                <td class="px-4 py-2 text-sm">
                                                    {{ index + 1 }}
                                                </td>
                                                <td class="px-4 py-2">
                                                    <TextInput
                                                        type="number"
                                                        step="0.0001"
                                                        v-model="measurement.nominal"
                                                        class="w-full"
                                                        required
                                                    />
                                                </td>
                                                <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">
                                                    {{ calculateLimits(parseFloat(measurement.nominal) || 0).upper.toFixed(4) }}
                                                </td>
                                                <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">
                                                    {{ calculateLimits(parseFloat(measurement.nominal) || 0).lower.toFixed(4) }}
                                                </td>
                                                <td v-if="form.show_description" class="px-4 py-2">
                                                    <TextInput
                                                        v-model="measurement.description"
                                                        class="w-full"
                                                        placeholder="Description"
                                                    />
                                                </td>
                                                <td class="px-4 py-2 text-center">
                                                    <button
                                                        v-if="form.measurements.length > 1"
                                                        type="button"
                                                        @click="removeMeasurement(index)"
                                                        class="text-red-600 hover:text-red-800"
                                                    >
                                                        ✕
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div>
                                <InputLabel for="notes" value="Notes" />
                                <textarea
                                    id="notes"
                                    v-model="form.notes"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    rows="3"
                                    placeholder="Measurement Group notes go here"
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.notes" />
                            </div>

                            <!-- Submit -->
                            <div class="flex items-center gap-4">
                                <PrimaryButton 
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    Create Measurement Group
                                </PrimaryButton>
                                
                                <Link 
                                    :href="route('calibration-records.show', calibrationRecord.id)"
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