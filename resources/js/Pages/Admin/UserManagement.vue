<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    users: Array,
});

const showInviteModal = ref(false);
const showEditModal = ref(false);
const editingUser = ref(null);

const inviteForm = useForm({
    name: '',
    email: '',
    role: 'member',
});

const editForm = useForm({
    name: '',
    role: 'member',
    is_suspended: false,
});

const inviteUser = () => {
    inviteForm.post(route('admin.users.store'), {
        onSuccess: () => {
            showInviteModal.value = false;
            inviteForm.reset();
        },
    });
};

const openEditModal = (user) => {
    editingUser.value = user;
    editForm.name = user.name;
    editForm.role = user.role;
    editForm.is_suspended = user.is_suspended || false;
    showEditModal.value = true;
};

const updateUser = () => {
    editForm.put(route('admin.users.update', editingUser.value.id), {
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
            editingUser.value = null;
        },
    });
};

const suspendUser = (user) => {
    if (confirm(`Are you sure you want to suspend ${user.name}?`)) {
        router.post(route('admin.users.suspend', user.id));
    }
};

const reactivateUser = (user) => {
    if (confirm(`Are you sure you want to reactivate ${user.name}?`)) {
        router.post(route('admin.users.reactivate', user.id));
    }
};

const removeUser = (user) => {
    if (confirm(`Are you sure you want to remove ${user.name} from the company? This action cannot be undone.`)) {
        router.delete(route('admin.users.destroy', user.id));
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

const getRoleBadgeClass = (role) => {
    return role === 'admin' 
        ? 'bg-purple-100 text-purple-800' 
        : 'bg-blue-100 text-blue-800';
};

const getStatusBadgeClass = (user) => {
    if (user.is_suspended) {
        return 'bg-red-100 text-red-800';
    }
    return user.email_verified_at 
        ? 'bg-green-100 text-green-800' 
        : 'bg-yellow-100 text-yellow-800';
};

const getStatusText = (user) => {
    if (user.is_suspended) {
        return 'Suspended';
    }
    return user.email_verified_at ? 'Active' : 'Pending';
};
</script>

<template>
    <Head title="User Management" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                User Management
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium text-gray-900">Company Users</h3>
                            <PrimaryButton @click="showInviteModal = true">
                                Invite User
                            </PrimaryButton>
                        </div>

                        <div v-if="users.length === 0" class="text-center py-8 text-gray-500">
                            No users found. Invite your first user to get started.
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Name
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Email
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Role
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Joined
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ user.name }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ user.email }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="[
                                                'inline-flex px-2 py-1 text-xs font-medium rounded-full',
                                                getRoleBadgeClass(user.role)
                                            ]">
                                                {{ user.role === 'admin' ? 'Administrator' : 'Member' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="[
                                                'inline-flex px-2 py-1 text-xs font-medium rounded-full',
                                                getStatusBadgeClass(user)
                                            ]">
                                                {{ getStatusText(user) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatDate(user.created_at) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                <button 
                                                    @click="openEditModal(user)"
                                                    class="text-blue-600 hover:text-blue-800"
                                                >
                                                    Edit
                                                </button>
                                                <button 
                                                    v-if="!user.is_suspended"
                                                    @click="suspendUser(user)"
                                                    class="text-yellow-600 hover:text-yellow-800"
                                                >
                                                    Suspend
                                                </button>
                                                <button 
                                                    v-else
                                                    @click="reactivateUser(user)"
                                                    class="text-green-600 hover:text-green-800"
                                                >
                                                    Reactivate
                                                </button>
                                                <button 
                                                    @click="removeUser(user)"
                                                    class="text-red-600 hover:text-red-800"
                                                >
                                                    Remove
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invite User Modal -->
        <Modal :show="showInviteModal" @close="showInviteModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Invite New User
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    Invite a new user to join your company. They will receive an email with login instructions.
                </p>

                <form @submit.prevent="inviteUser" class="mt-6 space-y-6">
                    <div>
                        <InputLabel for="name" value="Name" />
                        <TextInput
                            id="name"
                            v-model="inviteForm.name"
                            type="text"
                            class="mt-1 block w-full"
                            required
                        />
                        <div v-if="inviteForm.errors.name" class="text-red-600 text-sm mt-1">
                            {{ inviteForm.errors.name }}
                        </div>
                    </div>

                    <div>
                        <InputLabel for="email" value="Email" />
                        <TextInput
                            id="email"
                            v-model="inviteForm.email"
                            type="email"
                            class="mt-1 block w-full"
                            required
                        />
                        <div v-if="inviteForm.errors.email" class="text-red-600 text-sm mt-1">
                            {{ inviteForm.errors.email }}
                        </div>
                    </div>

                    <div>
                        <InputLabel for="role" value="Role" />
                        <select
                            id="role"
                            v-model="inviteForm.role"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            required
                        >
                            <option value="member">Member</option>
                            <option value="admin">Administrator</option>
                        </select>
                        <div v-if="inviteForm.errors.role" class="text-red-600 text-sm mt-1">
                            {{ inviteForm.errors.role }}
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="showInviteModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            Cancel
                        </button>
                        <PrimaryButton :disabled="inviteForm.processing">
                            Invite User
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit User Modal -->
        <Modal :show="showEditModal" @close="showEditModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Edit User
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    Update user information and role.
                </p>

                <form @submit.prevent="updateUser" class="mt-6 space-y-6">
                    <div>
                        <InputLabel for="edit_name" value="Name" />
                        <TextInput
                            id="edit_name"
                            v-model="editForm.name"
                            type="text"
                            class="mt-1 block w-full"
                            required
                        />
                        <div v-if="editForm.errors.name" class="text-red-600 text-sm mt-1">
                            {{ editForm.errors.name }}
                        </div>
                    </div>

                    <div>
                        <InputLabel for="edit_role" value="Role" />
                        <select
                            id="edit_role"
                            v-model="editForm.role"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            required
                        >
                            <option value="member">Member</option>
                            <option value="admin">Administrator</option>
                        </select>
                        <div v-if="editForm.errors.role" class="text-red-600 text-sm mt-1">
                            {{ editForm.errors.role }}
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input
                            id="edit_suspended"
                            v-model="editForm.is_suspended"
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        />
                        <label for="edit_suspended" class="ml-2 block text-sm text-gray-900">
                            Suspended
                        </label>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="showEditModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            Cancel
                        </button>
                        <PrimaryButton :disabled="editForm.processing">
                            Update User
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>