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
import { useForm, usePage } from '@inertiajs/vue3';

// 1. Define interfaces for robust Type Checking
interface User {
    name: string;
    email: string;
    email_verified_at: string | null;
}

interface BreadcrumbItem {
    title: string;
    href: string;
}

// 2. Setup Props (Backend-driven)
defineProps<{
    mustVerifyEmail: boolean;
    status: string | null;
    breadcrumbs: BreadcrumbItem[];
}>();

// 3. Get User Data
const page = usePage();
const user = page.props.auth.user as User;

// 4. Initialize Form (using the standardized 'form' name)
const form = useForm({
    name: user.name,
    email: user.email,
});

const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Optional: Add a custom notification trigger here
        }
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

                <form @submit.prevent="submit" class="space-y-6 max-w-xl">
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
                        <InputError :message="form.errors.name" />
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
                        <InputError :message="form.errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && user.email_verified_at === null">
                        <p class="mt-2 text-sm text-neutral-800">
                            Your email address is unverified.
                            <Link
                                :href="route('verification.send')"
                                method="post"
                                as="button"
                                class="rounded-md text-sm text-neutral-600 underline hover:text-neutral-900 focus:outline-none"
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
                        <Button :disabled="form.processing">Save Changes</Button>

                        <TransitionRoot
                            :show="form.recentlySuccessful"
                            enter="transition ease-in-out"
                            enter-from="opacity-0"
                            leave="transition ease-in-out"
                            leave-to="opacity-0"
                        >
                            <p class="text-sm text-green-600 font-medium">Saved successfully.</p>
                        </TransitionRoot>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>