<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Button } from '@/components/ui/button';
import { route } from 'ziggy-js';
import { computed } from 'vue';

const props = defineProps<{
    status?: string;
}>();

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <Head title="Email Verification" />

    <AuthLayout 
        title="Verify Your Email" 
        description="Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another."
    >
        <div v-if="verificationLinkSent" class="mb-4 font-medium text-sm text-green-600 dark:text-green-400 text-center">
            A new verification link has been sent to the email address you provided during registration.
        </div>

        <form @submit.prevent="submit" class="mt-4 space-y-4">
            <Button class="w-full" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Resend Verification Email
            </Button>

            <div class="flex items-center justify-between mt-4 text-sm">
                <Link
                    :href="route('profile.edit')"
                    class="underline text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                >
                    Edit Profile
                </Link>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="underline text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                >
                    Log Out
                </Link>
            </div>
        </form>
    </AuthLayout>
</template>