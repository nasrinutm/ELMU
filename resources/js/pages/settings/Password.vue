<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { route } from 'ziggy-js';

// Layouts and Components
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { type BreadcrumbItem } from '@/types';

// Breadcrumbs for navigation
const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Password settings',
        href: route('password.edit'),
    },
];

const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);

// Inertia useForm hook
const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <Head title="Update Password" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="p-6 bg-[#002B5C] min-h-screen text-white">
            <div class="max-w-4xl mx-auto space-y-8">
                
                <div>
                    <h1 class="text-3xl font-bold text-[#FFD700]">Security Settings</h1>
                    <p class="text-gray-300">Ensure your account is using a long, random password to stay secure.</p>
                </div>

                <Card class="bg-white text-[#002B5C] border-none">
                    <CardHeader>
                        <CardTitle>Update Password</CardTitle>
                        <CardDescription>
                            Enter your current password to confirm your identity, then choose a new password.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="updatePassword" class="space-y-4">
                            
                            <div class="grid gap-2">
                                <Label for="current_password" class="text-[#002B5C] font-bold">Current Password</Label>
                                <Input 
                                    id="current_password" 
                                    type="password" 
                                    v-model="form.current_password" 
                                    ref="currentPasswordInput"
                                    class="bg-white text-black border-gray-300" 
                                    autocomplete="current-password"
                                    placeholder="Current password"
                                />
                                <InputError :message="form.errors.current_password" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="password" class="text-[#002B5C] font-bold">New Password</Label>
                                <Input 
                                    id="password" 
                                    type="password" 
                                    v-model="form.password" 
                                    ref="passwordInput"
                                    class="bg-white text-black border-gray-300" 
                                    autocomplete="new-password"
                                    placeholder="New password"
                                />
                                <InputError :message="form.errors.password" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="password_confirmation" class="text-[#002B5C] font-bold">Confirm Password</Label>
                                <Input 
                                    id="password_confirmation" 
                                    type="password" 
                                    v-model="form.password_confirmation" 
                                    class="bg-white text-black border-gray-300" 
                                    autocomplete="new-password"
                                    placeholder="Confirm password"
                                />
                                <InputError :message="form.errors.password_confirmation" />
                            </div>

                            <div class="flex items-center gap-4">
                                <Button 
                                    :disabled="form.processing" 
                                    class="bg-[#FFD700] text-[#002B5C] hover:bg-[#E6C200] font-bold"
                                >
                                    Update Password
                                </Button>

                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <p v-show="form.recentlySuccessful" class="text-sm font-medium text-green-600">
                                        Password updated successfully.
                                    </p>
                                </Transition>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>