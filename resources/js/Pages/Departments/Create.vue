<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    manager_name: '',
    manager_email: '',
});

const submit = () => {
    form.post(route('departments.store'));
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Create Department" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex items-center mb-6">
                            <Link 
                                :href="route('departments.index')"
                                class="text-blue-600 hover:text-blue-800 mr-4"
                            >
                                ← Back to Departments
                            </Link>
                            <h1 class="text-2xl font-bold">Create Department</h1>
                        </div>

                        <form @submit.prevent="submit" class="max-w-md">
                            <div class="mb-4">
                                <InputLabel for="name" value="Department Name *" />
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
                                <InputLabel for="manager_name" value="Manager Name" />
                                <TextInput
                                    id="manager_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.manager_name"
                                />
                                <InputError class="mt-2" :message="form.errors.manager_name" />
                            </div>

                            <div class="mb-6">
                                <InputLabel for="manager_email" value="Manager Email" />
                                <TextInput
                                    id="manager_email"
                                    type="email"
                                    class="mt-1 block w-full"
                                    v-model="form.manager_email"
                                />
                                <InputError class="mt-2" :message="form.errors.manager_email" />
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton 
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    Create Department
                                </PrimaryButton>
                                
                                <Link 
                                    :href="route('departments.index')"
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