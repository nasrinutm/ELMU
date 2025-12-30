<script setup lang="ts">
// 1. Change Layout to the new Sidebar version for consistent Admin UI
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, router } from '@inertiajs/vue3';
// 2. REMOVED 'defineProps' from this import line to fix the error
import { ref } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { route } from 'ziggy-js';

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Users', href: route('users.index') },
    { title: 'Add User', href: route('users.create') },
];

// defineProps is automatically available, no import needed
const props = defineProps({
    roles: {
        type: Array as () => string[],
        required: true,
    },
});

// Form fields
const form = ref({
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: '',
});

// Form errors
const errors = ref({
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: '',
});

// Submit handler
const submit = () => {
    router.post(route('users.store'), form.value, {
        onError: (err: any) => {
            Object.assign(errors.value, err);
        },
        onSuccess: () => {
            form.value = {
                name: '',
                email: '',
                username: '',
                password: '',
                password_confirmation: '',
                role: '',
            };
        },
    });
};
</script>

<template>
    <Head title="Add User" />

    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-4xl mx-auto p-6">

            <div class="mb-6">
                <h1 class="text-2xl font-bold tracking-tight text-foreground">Add New User</h1>
                <p class="text-muted-foreground">Create a new student, teacher, or admin account.</p>
            </div>

            <div class="bg-card text-card-foreground border rounded-xl shadow-sm p-6">
                <form @submit.prevent="submit" class="space-y-6">

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                        <label for="name" class="block font-medium text-sm">Full Name</label>
                        <div class="md:col-span-3">
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                placeholder="e.g. Ahmad Albab"
                            />
                            <p v-if="errors.name" class="text-destructive text-sm mt-1">{{ errors.name }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                        <label for="username" class="block font-medium text-sm">Username</label>
                        <div class="md:col-span-3">
                            <input
                                id="username"
                                v-model="form.username"
                                type="text"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:ring-2 focus-visible:ring-ring"
                                placeholder="e.g. ahmad123"
                            />
                            <p v-if="errors.username" class="text-destructive text-sm mt-1">{{ errors.username }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                        <label for="email" class="block font-medium text-sm">Email Address</label>
                        <div class="md:col-span-3">
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:ring-2 focus-visible:ring-ring"
                                placeholder="e.g. ahmad@example.com"
                            />
                            <p v-if="errors.email" class="text-destructive text-sm mt-1">{{ errors.email }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                        <label for="password" class="block font-medium text-sm">Password</label>
                        <div class="md:col-span-3">
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:ring-2 focus-visible:ring-ring"
                                placeholder="Min 8 characters"
                            />
                            <p v-if="errors.password" class="text-destructive text-sm mt-1">{{ errors.password }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                        <label for="password_confirmation" class="block font-medium text-sm">Confirm Password</label>
                        <div class="md:col-span-3">
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:ring-2 focus-visible:ring-ring"
                                placeholder="Re-enter password"
                            />
                            <p v-if="errors.password_confirmation" class="text-destructive text-sm mt-1">
                                {{ errors.password_confirmation }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                        <label for="role" class="block font-medium text-sm">Role</label>
                        <div class="md:col-span-3">
                            <select
                                id="role"
                                v-model="form.role"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:ring-2 focus-visible:ring-ring"
                            >
                                <option value="" disabled>Select a role</option>
                                <option v-for="roleName in roles" :key="roleName" :value="roleName">
                                    {{ roleName.charAt(0).toUpperCase() + roleName.slice(1) }}
                                </option>
                            </select>
                            <p v-if="errors.role" class="text-destructive text-sm mt-1">{{ errors.role }}</p>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2"
                        >
                            Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>
