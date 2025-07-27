<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    calibrationRecord: Object,
});

// Wizard step management
const currentStep = ref(1);
const totalSteps = 4;

const form = useForm({
    calibration_record_id: props.calibrationRecord.id,
    group_number: '1',
    group_name: 'Main Measurements',
    type: 'tolerance_percent',
    plus_value: 0.05, // 0.05% default
    minus_value: 0.05,
    mask_min: null,
    mask_max: null,
    units: 'mm',
    notes: '',
    show_sequence: true,
    show_description: false,
    auto_fill_after: true,
    show_uncertainty: false,
    show_standards: false,
    measurements: [
        { nominal: 0.000, description: '' }
    ]
});

// Step navigation
const nextStep = () => {
    if (currentStep.value < totalSteps) {
        currentStep.value++;
    }
};

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

const goToStep = (step) => {
    currentStep.value = step;
};

// Measurement management
const addMeasurement = () => {
    form.measurements.push({ nominal: 0.000, description: '' });
};

const removeMeasurement = (index) => {
    if (form.measurements.length > 1) {
        form.measurements.splice(index, 1);
    }
};

// Quick preset measurement sets
const applyPreset = (preset) => {
    switch (preset) {
        case 'temperature':
            form.measurements = [
                { nominal: 32, description: 'Freezing point' },
                { nominal: 72, description: 'Room temperature' },
                { nominal: 212, description: 'Boiling point' }
            ];
            form.units = 'F';
            form.type = 'tolerance_plus_minus';
            form.plus_value = 2;
            form.minus_value = 2;
            break;
        case 'dimensional':
            form.measurements = [
                { nominal: 0.0000, description: '' },
                { nominal: 0.1000, description: '' },
                { nominal: 0.5000, description: '' },
                { nominal: 1.0000, description: '' }
            ];
            form.units = 'in';
            form.type = 'tolerance_percent';
            form.plus_value = 0.1;
            break;
        case 'electrical':
            form.measurements = [
                { nominal: 1.0, description: '' },
                { nominal: 5.0, description: '' },
                { nominal: 10.0, description: '' }
            ];
            form.units = 'V';
            form.type = 'tolerance_percent';
            form.plus_value = 0.5;
            break;
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

// Computed properties for validation
const isStep1Valid = computed(() => {
    return form.group_number && form.group_name && form.units;
});

const isStep2Valid = computed(() => {
    return form.type && 
           (form.type !== 'tolerance_percent' || form.plus_value) &&
           (form.type !== 'tolerance_plus_minus' || (form.plus_value && form.minus_value)) &&
           (form.type !== 'limits' || (form.mask_min && form.mask_max));
});

const isStep3Valid = computed(() => {
    return form.measurements.length > 0 && 
           form.measurements.every(m => m.nominal !== null && m.nominal !== '');
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Add Measurement Group" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <!-- Header -->
                        <div class="flex items-center mb-6">
                            <Link 
                                :href="route('calibration-records.show', calibrationRecord.id)"
                                class="text-blue-600 hover:text-blue-800 mr-4"
                            >
                                ← Back to Calibration Record
                            </Link>
                            <div class="flex-1">
                                <h1 class="text-2xl font-bold">Add Measurement Group</h1>
                                <p class="text-gray-600 dark:text-gray-400 mt-1">
                                    Create detailed measurement points with tolerances for {{ calibrationRecord.gage.gage_id }}
                                </p>
                            </div>
                        </div>

                        <!-- Progress Steps -->
                        <div class="mb-8">
                            <div class="flex items-center justify-between mb-4">
                                <div 
                                    v-for="step in totalSteps" 
                                    :key="step"
                                    class="flex items-center cursor-pointer"
                                    @click="goToStep(step)"
                                >
                                    <div :class="[
                                        'w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium',
                                        step <= currentStep 
                                            ? 'bg-blue-600 text-white' 
                                            : 'bg-gray-300 text-gray-600'
                                    ]">
                                        {{ step }}
                                    </div>
                                    <div v-if="step < totalSteps" :class="[
                                        'h-0.5 w-16 ml-2',
                                        step < currentStep ? 'bg-blue-600' : 'bg-gray-300'
                                    ]"></div>
                                </div>
                            </div>
                            <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400">
                                <span>Group Info</span>
                                <span>Tolerance Setup</span>
                                <span>Measurement Points</span>
                                <span>Review & Create</span>
                            </div>
                        </div>

                        <!-- Calibration Record Context -->
                        <div class="bg-blue-50 dark:bg-blue-900 rounded-lg p-4 mb-6">
                            <div class="grid md:grid-cols-4 gap-4 text-sm">
                                <div>
                                    <span class="font-medium text-blue-800 dark:text-blue-200">Gage:</span>
                                    <span class="ml-2">{{ calibrationRecord.gage.gage_id }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-blue-800 dark:text-blue-200">Date:</span>
                                    <span class="ml-2">{{ new Date(calibrationRecord.calibrated_at).toLocaleDateString() }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-blue-800 dark:text-blue-200">Technician:</span>
                                    <span class="ml-2">{{ calibrationRecord.user?.name }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-blue-800 dark:text-blue-200">Step:</span>
                                    <span class="ml-2">{{ currentStep }} of {{ totalSteps }}</span>
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent="submit">
                            <!-- Step 1: Basic Group Information -->
                            <div v-show="currentStep === 1" class="space-y-6">
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                                    <h3 class="text-lg font-semibold mb-4">Step 1: Group Information</h3>
                                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                                        Set up basic information for this measurement group. This helps organize your measurements.
                                    </p>

                                    <div class="grid md:grid-cols-3 gap-6">
                                        <div>
                                            <InputLabel for="group_number" value="Group Number *" />
                                            <TextInput
                                                id="group_number"
                                                v-model="form.group_number"
                                                class="mt-1 block w-full"
                                                placeholder="1"
                                                required
                                            />
                                            <p class="text-xs text-gray-500 mt-1">Sequential number for this group</p>
                                            <InputError class="mt-2" :message="form.errors.group_number" />
                                        </div>

                                        <div>
                                            <InputLabel for="group_name" value="Group Name *" />
                                            <TextInput
                                                id="group_name"
                                                v-model="form.group_name"
                                                class="mt-1 block w-full"
                                                placeholder="Main Measurements"
                                                required
                                            />
                                            <p class="text-xs text-gray-500 mt-1">Descriptive name for this group</p>
                                            <InputError class="mt-2" :message="form.errors.group_name" />
                                        </div>

                                        <div>
                                            <InputLabel for="units" value="Units of Measurement *" />
                                            <select
                                                id="units"
                                                v-model="form.units"
                                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                            >
                                                <optgroup label="Dimensional">
                                                    <option value="mm">mm (Millimeters)</option>
                                                    <option value="in">in (Inches)</option>
                                                    <option value="cm">cm (Centimeters)</option>
                                                </optgroup>
                                                <optgroup label="Temperature">
                                                    <option value="°C">°C (Celsius)</option>
                                                    <option value="°F">°F (Fahrenheit)</option>
                                                    <option value="K">K (Kelvin)</option>
                                                </optgroup>
                                                <optgroup label="Electrical">
                                                    <option value="V">V (Volts)</option>
                                                    <option value="A">A (Amperes)</option>
                                                    <option value="Ω">Ω (Ohms)</option>
                                                    <option value="Hz">Hz (Hertz)</option>
                                                </optgroup>
                                                <optgroup label="Pressure">
                                                    <option value="psi">psi</option>
                                                    <option value="Pa">Pa (Pascal)</option>
                                                    <option value="bar">bar</option>
                                                </optgroup>
                                            </select>
                                            <p class="text-xs text-gray-500 mt-1">Unit for all measurements in this group</p>
                                            <InputError class="mt-2" :message="form.errors.units" />
                                        </div>
                                    </div>

                                    <!-- Quick Start Presets -->
                                    <div class="mt-8">
                                        <h4 class="font-medium mb-4">Quick Start Templates (Optional)</h4>
                                        <div class="grid md:grid-cols-3 gap-4">
                                            <button
                                                type="button"
                                                @click="applyPreset('temperature')"
                                                class="p-4 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 text-left"
                                            >
                                                <div class="font-medium">Temperature Calibration</div>
                                                <div class="text-sm text-gray-500">32°F, 72°F, 212°F with ±2°F tolerance</div>
                                            </button>
                                            <button
                                                type="button"
                                                @click="applyPreset('dimensional')"
                                                class="p-4 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 text-left"
                                            >
                                                <div class="font-medium">Dimensional Measurement</div>
                                                <div class="text-sm text-gray-500">0-1 inch range with 0.1% tolerance</div>
                                            </button>
                                            <button
                                                type="button"
                                                @click="applyPreset('electrical')"
                                                class="p-4 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 text-left"
                                            >
                                                <div class="font-medium">Electrical Measurement</div>
                                                <div class="text-sm text-gray-500">1V, 5V, 10V with 0.5% tolerance</div>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 2: Tolerance Configuration -->
                            <div v-show="currentStep === 2" class="space-y-6">
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                                    <h3 class="text-lg font-semibold mb-4">Step 2: Tolerance Setup</h3>
                                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                                        Configure how pass/fail limits are calculated for your measurements.
                                    </p>

                                    <!-- Tolerance Type Selection -->
                                    <div class="grid md:grid-cols-3 gap-4 mb-6">
                                        <label class="cursor-pointer">
                                            <input type="radio" v-model="form.type" value="tolerance_percent" class="sr-only">
                                            <div :class="[
                                                'p-4 border-2 rounded-lg transition-colors',
                                                form.type === 'tolerance_percent' 
                                                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900' 
                                                    : 'border-gray-300 dark:border-gray-600'
                                            ]">
                                                <div class="flex items-center justify-between mb-2">
                                                    <h4 class="font-medium">Percentage (%)</h4>
                                                    <span v-if="form.type === 'tolerance_percent'" class="text-blue-600">✓</span>
                                                </div>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                    Tolerance as percentage of nominal value
                                                </p>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    Example: ±0.1% of 100 = ±0.1
                                                </p>
                                            </div>
                                        </label>

                                        <label class="cursor-pointer">
                                            <input type="radio" v-model="form.type" value="tolerance_plus_minus" class="sr-only">
                                            <div :class="[
                                                'p-4 border-2 rounded-lg transition-colors',
                                                form.type === 'tolerance_plus_minus' 
                                                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900' 
                                                    : 'border-gray-300 dark:border-gray-600'
                                            ]">
                                                <div class="flex items-center justify-between mb-2">
                                                    <h4 class="font-medium">Plus/Minus (±)</h4>
                                                    <span v-if="form.type === 'tolerance_plus_minus'" class="text-blue-600">✓</span>
                                                </div>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                    Fixed tolerance values
                                                </p>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    Example: +2/-1 or ±0.5
                                                </p>
                                            </div>
                                        </label>

                                        <label class="cursor-pointer">
                                            <input type="radio" v-model="form.type" value="limits" class="sr-only">
                                            <div :class="[
                                                'p-4 border-2 rounded-lg transition-colors',
                                                form.type === 'limits' 
                                                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900' 
                                                    : 'border-gray-300 dark:border-gray-600'
                                            ]">
                                                <div class="flex items-center justify-between mb-2">
                                                    <h4 class="font-medium">Fixed Limits</h4>
                                                    <span v-if="form.type === 'limits'" class="text-blue-600">✓</span>
                                                </div>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                    Absolute upper and lower limits
                                                </p>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    Example: 95-105 range
                                                </p>
                                            </div>
                                        </label>
                                    </div>

                                    <!-- Tolerance Value Inputs -->
                                    <div v-if="form.type === 'tolerance_percent'" class="bg-white dark:bg-gray-800 rounded-lg p-4">
                                        <h4 class="font-medium mb-3">Percentage Tolerance</h4>
                                        <div class="grid md:grid-cols-2 gap-4">
                                            <div>
                                                <InputLabel for="plus_value" value="Tolerance (%) *" />
                                                <TextInput
                                                    id="plus_value"
                                                    type="number"
                                                    step="0.001"
                                                    v-model="form.plus_value"
                                                    class="mt-1 block w-full"
                                                    placeholder="0.05"
                                                />
                                                <p class="text-xs text-gray-500 mt-1">Will be applied as ± to nominal values</p>
                                                <InputError class="mt-2" :message="form.errors.plus_value" />
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="form.type === 'tolerance_plus_minus'" class="bg-white dark:bg-gray-800 rounded-lg p-4">
                                        <h4 class="font-medium mb-3">Plus/Minus Tolerance</h4>
                                        <div class="grid md:grid-cols-2 gap-4">
                                            <div>
                                                <InputLabel for="plus_value" value="Plus (+) Tolerance *" />
                                                <TextInput
                                                    id="plus_value"
                                                    type="number"
                                                    step="0.0001"
                                                    v-model="form.plus_value"
                                                    class="mt-1 block w-full"
                                                    placeholder="0.5"
                                                />
                                                <InputError class="mt-2" :message="form.errors.plus_value" />
                                            </div>
                                            <div>
                                                <InputLabel for="minus_value" value="Minus (-) Tolerance *" />
                                                <TextInput
                                                    id="minus_value"
                                                    type="number"
                                                    step="0.0001"
                                                    v-model="form.minus_value"
                                                    class="mt-1 block w-full"
                                                    placeholder="0.5"
                                                />
                                                <InputError class="mt-2" :message="form.errors.minus_value" />
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="form.type === 'limits'" class="bg-white dark:bg-gray-800 rounded-lg p-4">
                                        <h4 class="font-medium mb-3">Fixed Limits</h4>
                                        <div class="grid md:grid-cols-2 gap-4">
                                            <div>
                                                <InputLabel for="mask_min" value="Lower Limit *" />
                                                <TextInput
                                                    id="mask_min"
                                                    type="number"
                                                    step="0.0001"
                                                    v-model="form.mask_min"
                                                    class="mt-1 block w-full"
                                                    placeholder="95.0"
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
                                                    placeholder="105.0"
                                                />
                                                <InputError class="mt-2" :message="form.errors.mask_max" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 3: Measurement Points -->
                            <div v-show="currentStep === 3" class="space-y-6">
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                                    <div class="flex justify-between items-center mb-4">
                                        <div>
                                            <h3 class="text-lg font-semibold">Step 3: Measurement Points</h3>
                                            <p class="text-gray-600 dark:text-gray-400">
                                                Define the nominal values you'll be measuring
                                            </p>
                                        </div>
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
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                                        Point #
                                                    </th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                                        Nominal Value ({{ form.units }})
                                                    </th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                                        Upper Limit
                                                    </th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                                        Lower Limit
                                                    </th>
                                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                                        Remove
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                                <tr 
                                                    v-for="(measurement, index) in form.measurements" 
                                                    :key="index"
                                                    class="hover:bg-gray-50 dark:hover:bg-gray-600"
                                                >
                                                    <td class="px-4 py-3 text-sm font-medium">
                                                        {{ index + 1 }}
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <TextInput
                                                            type="number"
                                                            step="0.0001"
                                                            v-model="measurement.nominal"
                                                            class="w-full"
                                                            placeholder="0.000"
                                                            required
                                                        />
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-green-600 dark:text-green-400 font-mono">
                                                        {{ calculateLimits(parseFloat(measurement.nominal) || 0).upper.toFixed(4) }}
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-red-600 dark:text-red-400 font-mono">
                                                        {{ calculateLimits(parseFloat(measurement.nominal) || 0).lower.toFixed(4) }}
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        <button
                                                            v-if="form.measurements.length > 1"
                                                            type="button"
                                                            @click="removeMeasurement(index)"
                                                            class="text-red-600 hover:text-red-800 text-lg"
                                                        >
                                                            ✕
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 4: Review -->
                            <div v-show="currentStep === 4" class="space-y-6">
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                                    <h3 class="text-lg font-semibold mb-4">Step 4: Review & Create</h3>
                                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                                        Review your measurement group configuration before creating
                                    </p>

                                    <!-- Summary -->
                                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4">
                                            <h4 class="font-medium mb-3">Group Information</h4>
                                            <div class="space-y-2 text-sm">
                                                <div><span class="font-medium">Number:</span> {{ form.group_number }}</div>
                                                <div><span class="font-medium">Name:</span> {{ form.group_name }}</div>
                                                <div><span class="font-medium">Units:</span> {{ form.units }}</div>
                                                <div><span class="font-medium">Points:</span> {{ form.measurements.length }}</div>
                                            </div>
                                        </div>

                                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4">
                                            <h4 class="font-medium mb-3">Tolerance Configuration</h4>
                                            <div class="space-y-2 text-sm">
                                                <div><span class="font-medium">Type:</span> 
                                                    {{ form.type === 'tolerance_percent' ? 'Percentage' : 
                                                       form.type === 'tolerance_plus_minus' ? 'Plus/Minus' : 'Fixed Limits' }}
                                                </div>
                                                <div v-if="form.type === 'tolerance_percent'">
                                                    <span class="font-medium">Tolerance:</span> ±{{ form.plus_value }}%
                                                </div>
                                                <div v-if="form.type === 'tolerance_plus_minus'">
                                                    <span class="font-medium">Tolerance:</span> +{{ form.plus_value }}/-{{ form.minus_value }}
                                                </div>
                                                <div v-if="form.type === 'limits'">
                                                    <span class="font-medium">Range:</span> {{ form.mask_min }} to {{ form.mask_max }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Notes -->
                                    <div>
                                        <InputLabel for="notes" value="Notes (Optional)" />
                                        <textarea
                                            id="notes"
                                            v-model="form.notes"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                            rows="3"
                                            placeholder="Any additional notes about this measurement group..."
                                        ></textarea>
                                        <InputError class="mt-2" :message="form.errors.notes" />
                                    </div>
                                </div>
                            </div>

                            <!-- Navigation Buttons -->
                            <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-200 dark:border-gray-600">
                                <div>
                                    <SecondaryButton 
                                        v-if="currentStep > 1"
                                        @click="prevStep"
                                        type="button"
                                    >
                                        ← Previous
                                    </SecondaryButton>
                                </div>

                                <div class="text-sm text-gray-500">
                                    Step {{ currentStep }} of {{ totalSteps }}
                                </div>

                                <div class="flex space-x-3">
                                    <Link 
                                        :href="route('calibration-records.show', calibrationRecord.id)"
                                        class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 px-4 py-2"
                                    >
                                        Cancel
                                    </Link>

                                    <PrimaryButton 
                                        v-if="currentStep < totalSteps"
                                        @click="nextStep"
                                        type="button"
                                        :disabled="
                                            (currentStep === 1 && !isStep1Valid) ||
                                            (currentStep === 2 && !isStep2Valid) ||
                                            (currentStep === 3 && !isStep3Valid)
                                        "
                                    >
                                        Next →
                                    </PrimaryButton>

                                    <PrimaryButton 
                                        v-if="currentStep === totalSteps"
                                        :class="{ 'opacity-25': form.processing }"
                                        :disabled="form.processing"
                                        type="submit"
                                    >
                                        Create Measurement Group
                                    </PrimaryButton>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>