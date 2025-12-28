<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import InputError from '@/components/InputError.vue';
import { route } from 'ziggy-js';
import { ref } from 'vue';

const page = usePage();
const user = page.props.auth.user;

// --- PROFILE INFORMATION FORM ---
const profileForm = useForm({
    name: user.name,
    email: user.email,
});

const updateProfile = () => {
    profileForm.patch(route('profile.update'), {
        preserveScroll: true,
    });
};

// --- PASSWORD UPDATE FORM ---
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    passwordForm.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
        onError: () => {
            if (passwordForm.errors.password) {
                passwordForm.reset('password', 'password_confirmation');
            }
            if (passwordForm.errors.current_password) {
                passwordForm.reset('current_password');
            }
        },
    });
};

// --- DELETE ACCOUNT FORM ---
const deleteForm = useForm({
    password: '',
});

const confirmDelete = () => {
    if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
        deleteForm.delete(route('profile.destroy'), {
            preserveScroll: true,
            onFinish: () => deleteForm.reset(),
        });
    }
};
</script>

<template>
    <Head title="Profile Settings" />

    <AppLayout>
        <div class="p-6 bg-[#002B5C] min-h-screen text-white">
            <div class="max-w-4xl mx-auto space-y-8">
                
                <div>
                    <h1 class="text-3xl font-bold text-[#FFD700]">Profile Settings</h1>
                    <p class="text-gray-300">Update your account information and security settings.</p>
                </div>

                <Card class="bg-white text-[#002B5C] border-none">
                    <CardHeader>
                        <CardTitle>Profile Information</CardTitle>
                        <CardDescription>Update your account's profile information and email address.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="updateProfile" class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="name" class="text-[#002B5C] font-bold">Name</Label>
                                <Input id="name" v-model="profileForm.name" required class="bg-white text-black border-gray-300" />
                                <InputError :message="profileForm.errors.name" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="email" class="text-[#002B5C] font-bold">Email</Label>
                                <Input id="email" type="email" v-model="profileForm.email" required class="bg-white text-black border-gray-300" />
                                <InputError :message="profileForm.errors.email" />
                            </div>

                            <div v-if="profileForm.recentlySuccessful" class="text-sm font-medium text-green-600">
                                Saved successfully.
                            </div>

                            <Button :disabled="profileForm.processing" class="bg-[#FFD700] text-[#002B5C] hover:bg-[#E6C200] font-bold">
                                Save Changes
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                <Card class="bg-white text-[#002B5C] border-none">
                    <CardHeader>
                        <CardTitle>Update Password</CardTitle>
                        <CardDescription>Ensure your account is using a long, random password to stay secure.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="updatePassword" class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="current_password" class="text-[#002B5C] font-bold">Current Password</Label>
                                <Input id="current_password" type="password" v-model="passwordForm.current_password" class="bg-white text-black border-gray-300" />
                                <InputError :message="passwordForm.errors.current_password" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="new_password" class="text-[#002B5C] font-bold">New Password</Label>
                                <Input id="new_password" type="password" v-model="passwordForm.password" class="bg-white text-black border-gray-300" />
                                <InputError :message="passwordForm.errors.password" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="password_confirmation" class="text-[#002B5C] font-bold">Confirm Password</Label>
                                <Input id="password_confirmation" type="password" v-model="passwordForm.password_confirmation" class="bg-white text-black border-gray-300" />
                                <InputError :message="passwordForm.errors.password_confirmation" />
                            </div>

                            <div v-if="passwordForm.recentlySuccessful" class="text-sm font-medium text-green-600">
                                Password updated.
                            </div>

                            <Button :disabled="passwordForm.processing" class="bg-[#FFD700] text-[#002B5C] hover:bg-[#E6C200] font-bold">
                                Update Password
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                <Card class="bg-red-50 text-red-900 border-red-200">
                    <CardHeader>
                        <CardTitle>Delete Account</CardTitle>
                        <CardDescription class="text-red-700">
                            Once your account is deleted, all of its resources and data will be permanently deleted.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Button @click="confirmDelete" variant="destructive" class="bg-red-600 hover:bg-red-700 font-bold">
                            Delete Account
                        </Button>
                    </CardContent>
                </Card>

            </div>
        </div>
    </AppLayout>
</template>