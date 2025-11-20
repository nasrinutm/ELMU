<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { type BreadcrumbItem } from '@/types';
import { route } from 'ziggy-js';

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


const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Users', href: route('users.index') },
    { title: 'Edit User', href: route('users.edit', props.user.id) },
];


const form = useForm({
    name: props.user.name,
    username: props.user.username,
    email: props.user.email,
    role: props.user.role,
    password: '',
    password_confirmation: '',
});


const submit = () => {

    form.put(route('users.update', props.user.id), {

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
                <div class="bg-gray-200 overflow-hidden shadow-md sm:rounded-lg">
                    <div class="p-6 bg-[#ffd900] border-white border-gray-400">
                        <h3 class="text-lg font-medium text-[#003366] mb-4">
                            Edit User: {{ user.name }}
                        </h3>

                        <form @submit.prevent="submit">
                            <div class="text-[#003366]">
                                <Label for="name">Name</Label>
                                <Input
                                    id="name"
                                    type="text"
                                    class="mt-1 block w-full text-[#003366]"
                                    v-model="form.name"
                                    required
                                    autofocus
                                />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <div class="mt-4 text-[#003366]">
                                <Label for="username">Username</Label>
                                <Input
                                    id="username"
                                    type="text"
                                    class="mt-1 block w-full text-[#003366]"
                                    v-model="form.username"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.username" />
                            </div>

                            <div class="mt-4 text-[#003366]">
                                <Label for="email">Email</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    class="mt-1 block w-full text-[#003366]"
                                    v-model="form.email"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>

                            <div class="mt-4 text-[#003366]">
                                <Label for="role">Role</Label>
                                <select
                                    id="role"
                                    class="mt-1 block w-full border-gray-300  bg-[#003366] focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    v-model="form.role"
                                    required
                                >
                                    <option v-for="role in roles" :key="role" :value="role">
                                        {{ capitalize(role) }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.role" />
                            </div>

                            <div class="mt-4 text-[#003366]">
                                <Label for="password">New Password (Optional)</Label>
                                <Input
                                    id="password"
                                    type="password"
                                    class="mt-1 block w-full text-[#003366]"
                                    v-model="form.password"
                                    autocomplete="new-password"
                                />
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Leave blank to keep the current password.
                                </p>
                                <InputError class="mt-2" :message="form.errors.password" />
                            </div>

                            <div class="mt-4 text-[#003366]">
                                <Label for="password_confirmation">Confirm New Password</Label>
                                <Input
                                    id="password_confirmation"
                                    type="password"
                                    class="mt-1 block w-full text-[#003366]"
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
