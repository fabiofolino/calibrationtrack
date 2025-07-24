<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    twoFactorEnabled: Boolean,
    recoveryCodes: Array,
    qrCodeSvg: String,
});

const showingRecoveryCodes = ref(false);
const confirmingTwoFactor = ref(false);

const enableForm = useForm({
    password: '',
});

const confirmForm = useForm({
    code: '',
});

const disableForm = useForm({
    password: '',
});

const recoveryForm = useForm({
    password: '',
});

const enableTwoFactor = () => {
    enableForm.post(route('profile.two-factor-auth.enable'), {
        preserveScroll: true,
        onSuccess: () => {
            enableForm.reset();
            confirmingTwoFactor.value = true;
        },
    });
};

const confirmTwoFactor = () => {
    confirmForm.post(route('profile.two-factor-auth.confirm'), {
        preserveScroll: true,
        onSuccess: () => {
            confirmForm.reset();
            confirmingTwoFactor.value = false;
        },
    });
};

const disableTwoFactor = () => {
    disableForm.delete(route('profile.two-factor-auth.disable'), {
        preserveScroll: true,
        onSuccess: () => {
            disableForm.reset();
            showingRecoveryCodes.value = false;
        },
    });
};

const generateRecoveryCodes = () => {
    recoveryForm.post(route('profile.two-factor-auth.recovery-codes'), {
        preserveScroll: true,
        onSuccess: () => {
            recoveryForm.reset();
            showingRecoveryCodes.value = true;
        },
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Two-Factor Authentication" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <div class="flex items-center mb-6">
                        <Link 
                            :href="route('profile.edit')"
                            class="text-blue-600 hover:text-blue-800 mr-4"
                        >
                            ← Back to Profile
                        </Link>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Two-Factor Authentication</h1>
                    </div>

                    <div class="max-w-xl">
                        <div v-if="!twoFactorEnabled">
                            <!-- Enable 2FA Section -->
                            <div class="mb-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    Enable Two-Factor Authentication
                                </h3>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    Add additional security to your account using two-factor authentication. When enabled, you'll be prompted for a secure, random token during authentication.
                                </p>
                            </div>

                            <form @submit.prevent="enableTwoFactor" class="space-y-4">
                                <div>
                                    <InputLabel for="enable_password" value="Password" />
                                    <TextInput
                                        id="enable_password"
                                        v-model="enableForm.password"
                                        type="password"
                                        class="mt-1 block w-full"
                                        placeholder="Confirm your password to enable 2FA"
                                        required
                                    />
                                    <InputError class="mt-2" :message="enableForm.errors.password" />
                                </div>

                                <PrimaryButton 
                                    :class="{ 'opacity-25': enableForm.processing }"
                                    :disabled="enableForm.processing"
                                >
                                    Enable Two-Factor Authentication
                                </PrimaryButton>
                            </form>
                        </div>

                        <div v-else>
                            <!-- 2FA Enabled Section -->
                            <div class="mb-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    Two-Factor Authentication is Enabled
                                </h3>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    Two-factor authentication adds an additional layer of security to your account.
                                </p>
                            </div>

                            <!-- Confirmation Form (for newly enabled 2FA) -->
                            <div v-if="confirmingTwoFactor" class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/50 rounded-lg">
                                <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-3">
                                    Confirm Two-Factor Authentication
                                </h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    To finish enabling two-factor authentication, scan the following QR code using your phone's authenticator application or enter the setup key and provide the generated OTP code.
                                </p>

                                <!-- QR Code -->
                                <div v-if="qrCodeSvg" class="mb-4">
                                    <div v-html="qrCodeSvg" class="inline-block p-2 bg-white rounded"></div>
                                </div>

                                <form @submit.prevent="confirmTwoFactor" class="space-y-4">
                                    <div>
                                        <InputLabel for="confirm_code" value="Authentication Code" />
                                        <TextInput
                                            id="confirm_code"
                                            v-model="confirmForm.code"
                                            type="text"
                                            class="mt-1 block w-full"
                                            placeholder="Enter the 6-digit code from your authenticator app"
                                            maxlength="6"
                                            required
                                        />
                                        <InputError class="mt-2" :message="confirmForm.errors.code" />
                                    </div>

                                    <PrimaryButton 
                                        :class="{ 'opacity-25': confirmForm.processing }"
                                        :disabled="confirmForm.processing"
                                    >
                                        Confirm Two-Factor Authentication
                                    </PrimaryButton>
                                </form>
                            </div>

                            <!-- Recovery Codes Section -->
                            <div class="mb-6">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-md font-medium text-gray-900 dark:text-gray-100">
                                        Recovery Codes
                                    </h4>
                                    <button
                                        @click="showingRecoveryCodes = !showingRecoveryCodes"
                                        type="button"
                                        class="text-sm text-blue-600 hover:text-blue-800"
                                    >
                                        {{ showingRecoveryCodes ? 'Hide' : 'Show' }} Recovery Codes
                                    </button>
                                </div>

                                <div v-if="showingRecoveryCodes" class="mt-4">
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                        Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two-factor authentication device is lost.
                                    </p>
                                    
                                    <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-4">
                                        <div class="grid grid-cols-2 gap-2 text-sm font-mono">
                                            <div v-for="code in recoveryCodes" :key="code" class="text-gray-900 dark:text-gray-100">
                                                {{ code }}
                                            </div>
                                        </div>
                                    </div>

                                    <form @submit.prevent="generateRecoveryCodes" class="mt-4">
                                        <div class="mb-4">
                                            <InputLabel for="recovery_password" value="Password" />
                                            <TextInput
                                                id="recovery_password"
                                                v-model="recoveryForm.password"
                                                type="password"
                                                class="mt-1 block w-full"
                                                placeholder="Confirm your password to regenerate codes"
                                            />
                                            <InputError class="mt-2" :message="recoveryForm.errors.password" />
                                        </div>

                                        <PrimaryButton 
                                            :class="{ 'opacity-25': recoveryForm.processing }"
                                            :disabled="recoveryForm.processing"
                                        >
                                            Regenerate Recovery Codes
                                        </PrimaryButton>
                                    </form>
                                </div>
                            </div>

                            <!-- Disable 2FA Section -->
                            <div class="pt-6 border-t border-gray-200 dark:border-gray-600">
                                <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-3">
                                    Disable Two-Factor Authentication
                                </h4>
                                
                                <form @submit.prevent="disableTwoFactor" class="space-y-4">
                                    <div>
                                        <InputLabel for="disable_password" value="Password" />
                                        <TextInput
                                            id="disable_password"
                                            v-model="disableForm.password"
                                            type="password"
                                            class="mt-1 block w-full"
                                            placeholder="Confirm your password to disable 2FA"
                                        />
                                        <InputError class="mt-2" :message="disableForm.errors.password" />
                                    </div>

                                    <DangerButton 
                                        :class="{ 'opacity-25': disableForm.processing }"
                                        :disabled="disableForm.processing"
                                    >
                                        Disable Two-Factor Authentication
                                    </DangerButton>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>