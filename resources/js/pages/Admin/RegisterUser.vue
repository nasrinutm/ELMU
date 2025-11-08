<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { register } from '@/routes/admin'; // Assuming you have a route helper

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/' },
    { title: 'Register User', href: register().url },
];

// Form fields
const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    course: '',
    role: '',
});

// Form errors
const errors = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    course: '',
    role: '',
});

// Submit handler
const submit = () => {
    console.log('Submitting form...', form.value);
    router.post(register().url, form.value, {
        onError: (err) => {
            // Assign backend validation errors
            Object.assign(errors.value, err);
        },
        onSuccess: () => {
            // Reset form if needed
            form.value = {
                name: '',
                email: '',
                password: '',
                password_confirmation: '',
                course: '',
                role: '',
            };
        },
    });
};
</script>

<template>
    <Head title="Register User" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-xl mx-auto p-4 bg-transparent rounded-xl shadow-md">

            <form @submit.prevent="submit" class="space-y-4">
                <!-- Name -->
                <div>
                    <label class="block mb-1 font-medium">Name</label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="w-full rounded border px-3 py-2"
                        placeholder="Enter full name"
                    />
                    <p v-if="errors.name" class="text-red-500 text-sm">{{ errors.name }}</p>
                </div>

                <!-- Email -->
                <div>
                    <label class="block mb-1 font-medium">Email</label>
                    <input
                        v-model="form.email"
                        type="email"
                        class="w-full rounded border px-3 py-2"
                        placeholder="Enter email"
                    />
                    <p v-if="errors.email" class="text-red-500 text-sm">{{ errors.email }}</p>
                </div>

                <!-- Password -->
                <div>
                    <label class="block mb-1 font-medium">Password</label>
                    <input
                        v-model="form.password"
                        type="password"
                        class="w-full rounded border px-3 py-2"
                        placeholder="Enter password"
                    />
                    <p v-if="errors.password" class="text-red-500 text-sm">{{ errors.password }}</p>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label class="block mb-1 font-medium">Confirm Password</label>
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        class="w-full rounded border px-3 py-2"
                        placeholder="Confirm password"
                    />
                    <p v-if="errors.password_confirmation" class="text-red-500 text-sm">
                        {{ errors.password_confirmation }}
                    </p>
                </div>

                <!-- Course -->
                <div>
                    <label class="block mb-1 font-medium">Course</label>
                    <input
                        v-model="form.course"
                        type="text"
                        class="w-full rounded border px-3 py-2"
                        placeholder="Enter course"
                    />
                    <p v-if="errors.course" class="text-red-500 text-sm">{{ errors.course }}</p>
                </div>

                <!-- Role -->
                <div>
                    <label class="block mb-1 font-medium">Role</label>
                    <select v-model="form.role" class="w-full rounded border px-3 py-2">
                        <option value="" disabled>Select role</option>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                    </select>
                    <p v-if="errors.role" class="text-red-500 text-sm">{{ errors.role }}</p>
                </div>

                <!-- Submit -->
                <div class="pt-2">
                    <button
                        type="submit"
                        class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700"
                    >
                        Register
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
