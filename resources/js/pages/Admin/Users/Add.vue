<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, defineProps } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { route } from 'ziggy-js'; // Assuming you have a route helper

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Users', href: route('users.index') },
    { title: 'Add', href: route('users.create') },
];

const props = defineProps({
    roles: {
        type: Array as () => string[], // This is an array of role names
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
    console.log('Submitting form...', form.value);
    router.post(route('users.store'), form.value, {
        onError: (err) => {
            // Assign backend validation errors
            Object.assign(errors.value, err);
        },
        onSuccess: () => {
            // Reset form if needed
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

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-5xl mx-auto p-4 bg-transparent rounded-xl shadow-md">

            <form @submit.prevent="submit" class="space-y-6 mt-8">

                <div class="grid grid-cols-4 gap-4 items-center">
                    <label for="name" class="block font-medium">Name</label>
                    <div class="col-span-3">
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="w-full rounded border px-3 py-2"
                            placeholder="Enter full name"
                        />
                        <p v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-4 items-center">
                    <label for="username" class="block font-medium">Username</label>
                    <div class="col-span-3">
                        <input
                            id="username"
                            v-model="form.username"
                            type="text"
                            class="w-full rounded border px-3 py-2"
                            placeholder="Enter username"
                        />
                        <p v-if="errors.username" class="text-red-500 text-sm mt-1">{{ errors.username }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-4 items-center">
                    <label for="email" class="block font-medium">Email</label>
                    <div class="col-span-3">
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="w-full rounded border px-3 py-2"
                            placeholder="Enter email"
                        />
                        <p v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-4 items-center">
                    <label for="password" class="block font-medium">Password</label>
                    <div class="col-span-3">
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            class="w-full rounded border px-3 py-2"
                            placeholder="Enter password"
                        />
                        <p v-if="errors.password" class="text-red-500 text-sm mt-1">{{ errors.password }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-4 items-center">
                    <label for="password_confirmation" class="block font-medium">Confirm Password</label>
                    <div class="col-span-3">
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            class="w-full rounded border px-3 py-2"
                            placeholder="Confirm password"
                        />
                        <p v-if="errors.password_confirmation" class="text-red-500 text-sm mt-1">
                            {{ errors.password_confirmation }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-4 items-center">
                    <label for="role" class="block font-medium">Role</label>
                    <div class="col-span-3">
                        <select id="role" v-model="form.role" class="w-full rounded border px-3 py-2">
                            <option value="" disabled>Select role</option>
                            <option v-for="roleName in roles" :key="roleName" :value="roleName">
                                {{ roleName.charAt(0).toUpperCase() + roleName.slice(1) }}
                            </option>
                        </select>
                        <p v-if="errors.role" class="text-red-500 text-sm mt-1">{{ errors.role }}</p>
                    </div>
                </div>

                <div class="pt-2 grid grid-cols-4 gap-4">
                    <div class="col-start-2 col-span-3">
                        <button
                            type="submit"
                            class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700"
                        >
                            Register
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>