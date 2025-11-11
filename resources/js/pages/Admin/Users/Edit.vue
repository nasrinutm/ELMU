<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';       // <-- 1. Corrected
import { Input } from '@/components/ui/input';         // <-- 2. Corrected
import { Label } from '@/components/ui/label';         // <-- 3. Corrected
import InputError from '@/components/InputError.vue';    // <-- 4. This path should be correct
import { type BreadcrumbItem } from '@/types';
import { route } from 'ziggy-js';

// Define the props we're receiving from the Controller
const props = defineProps<{
    user: {
        id: number;
        name: string;
        username: string;
        email: string;
        role: string;
    };
    roles: string[];
}>();

// Define breadcrumbs for AppLayout
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Users', href: route('users.index') },
    { title: 'Edit User', href: route('users.edit', props.user.id) },
];

// Initialize the form with the user's data
const form = useForm({
    name: props.user.name,
    username: props.user.username,
    email: props.user.email,
    role: props.user.role, // This is the user's current role name
    password: '',
    password_confirmation: '',
});

// Define the submit function
const submit = () => {
    // We use 'put' for updates
    form.put(route('users.update', props.user.id), {
        // Clear password fields on success
        onSuccess: () => form.reset('password', 'password_confirmation'),
    });
};

const capitalize = (s: string) => s.charAt(0).toUpperCase() + s.slice(1);
</script>

<template>
    <Head title="Edit User" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-md sm:rounded-lg">
                    <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">

                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                            Edit User: {{ user.name }}
                        </h3>

                        <form @submit.prevent="submit">
                            <div>
                                <Label for="name">Name</Label>
                                <Input
                                    id="name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.name"
                                    required
                                    autofocus
                                />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <div class="mt-4">
                                <Label for="username">Username</Label>
                                <Input
                                    id="username"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.username"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.username" />
                            </div>

                            <div class="mt-4">
                                <Label for="email">Email</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    class="mt-1 block w-full"
                                    v-model="form.email"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>

                            <div class="mt-4">
                                <Label for="role">Role</Label>
                                <select
                                    id="role"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    v-model="form.role"
                                    required
                                >
                                    <option v-for="role in roles" :key="role" :value="role">
                                        {{ capitalize(role) }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.role" />
                            </div>

                            <div class="mt-4">
                                <Label for="password">New Password (Optional)</Label>
                                <Input
                                    id="password"
                                    type="password"
                                    class="mt-1 block w-full"
                                    v-model="form.password"
                                    autocomplete="new-password"
                                />
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Leave blank to keep the current password.
                                </p>
                                <InputError class="mt-2" :message="form.errors.password" />
                            </div>

                            <div class="mt-4">
                                <Label for="password_confirmation">Confirm New Password</Label>
                                <Input
                                    id="password_confirmation"
                                    type="password"
                                    class="mt-1 block w-full"
                                    v-model="form.password_confirmation"
                                    autocomplete="new-password"
                                />
                                <InputError class="mt-2" :message="form.errors.password_confirmation" />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <Button type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Update User
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
