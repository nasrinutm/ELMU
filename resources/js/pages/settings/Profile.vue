<script setup lang="ts">
import { TransitionRoot } from '@headlessui/vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue'; // Import ref
import { route } from 'ziggy-js';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/SettingsLayout.vue';

// Define the User interface to fix type errors
interface User {
    name: string;
    email: string;
    email_verified_at: string | null;
}

interface BreadcrumbItem {
    title: string;
    href: string;
}

defineProps<{
    mustVerifyEmail: boolean;
    status: string | null;
    breadcrumbs: BreadcrumbItem[];
}>();

const user = usePage().props.auth.user as User;

// FIX: Standardized variable name to 'form' (was likely 'profileForm' in one version)
const form = useForm({
    name: user.name,
    email: user.email,
});

const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Profile" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    title="Profile Information"
                    description="Update your account's profile information and email address."
                />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            required
                            autocomplete="name"
                            placeholder="Full Name"
                        />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="mt-1 block w-full"
                            required
                            autocomplete="username"
                            placeholder="Email Address"
                        />
                        <InputError :message="form.errors.email" class="mt-2" />
                    </div>

                    <div v-if="mustVerifyEmail && user.email_verified_at === null">
                        <p class="mt-2 text-sm text-neutral-800">
                            Your email address is unverified.
                            <Link
                                :href="route('verification.send')"
                                method="post"
                                as="button"
                                class="rounded-md text-sm text-neutral-600 underline hover:text-neutral-900 focus:ring-2 focus:ring-neutral-500 focus:ring-offset-2 focus:outline-hidden"
                            >
                                Click here to re-send the verification email.
                            </Link>
                        </p>

                        <div
                            v-show="status === 'verification-link-sent'"
                            class="mt-2 text-sm font-medium text-green-600"
                        >
                            A new verification link has been sent to your email address.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">Save</Button>

                        <TransitionRoot
                            :show="form.recentlySuccessful"
                            enter="transition ease-in-out"
                            enter-from="opacity-0"
                            leave="transition ease-in-out"
                            leave-to="opacity-0"
                        >
                            <p class="text-sm text-neutral-600">Saved.</p>
                        </TransitionRoot>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>