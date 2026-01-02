<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { route } from 'ziggy-js';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
    canRegister?: boolean;
}>();

/**
 * Initialize the login form with Inertia's useForm helper.
 */
const form = useForm({
    email: '',
    password: '',
    remember: false,
});

/**
 * Handle form submission.
 * This posts to the 'login' route defined in your Laravel backend.
 */
const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <Head title="Log in" />

    <div
        class="relative min-h-screen flex flex-col justify-center items-center p-4 bg-cover bg-center bg-no-repeat"
        style="background-image: url('/bg-raw.jpg');"
    >
        <div class="absolute inset-0 bg-slate-900/20 z-0"></div>

        <div class="relative z-10 w-full flex flex-col items-center">

            <div class="mb-8">
                <Link href="/">
                    <img src="/logo.png" alt="ELMU Logo" class="h-36 w-auto object-contain" />
                </Link>
            </div>

            <div class="w-full sm:max-w-md px-8 py-10 bg-white shadow-2xl rounded-2xl border border-gray-100">

                <div class="mb-6 text-center">
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900">Welcome back</h2>
                    <p class="text-sm text-gray-500 mt-2">Please enter your details to sign in.</p>
                </div>

                <div v-if="status" class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-3 rounded-lg text-center">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label for="email" class="block font-medium text-sm text-gray-700">Email Address</label>
                        <input
                            id="email"
                            type="email"
                            class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-blue-600 focus:bg-white focus:ring-blue-600 transition shadow-sm outline-none"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="email@example.com"
                        />
                        <div v-if="form.errors.email" class="text-red-600 text-xs mt-1 italic">{{ form.errors.email }}</div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <label for="password" class="block font-medium text-sm text-gray-700">Password</label>
                        </div>
                        <input
                            id="password"
                            type="password"
                            class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-blue-600 focus:bg-white focus:ring-blue-600 transition shadow-sm outline-none"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                        />
                        <div v-if="form.errors.password" class="text-red-600 text-xs mt-1 italic">{{ form.errors.password }}</div>
                    </div>

                    <div class="flex items-center">
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all disabled:opacity-70 disabled:cursor-not-allowed"
                    >
                        <LoaderCircle v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4" />
                        {{ form.processing ? 'Signing in...' : 'Sign in' }}
                    </button>
                </form>

            </div>

            <div class="mt-8 text-center text-sm text-white">
                &copy; {{ new Date().getFullYear() }} ELMU. All rights reserved.
            </div>
        </div>
    </div>
</template>
