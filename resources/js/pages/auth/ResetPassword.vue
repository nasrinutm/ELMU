<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { route } from 'ziggy-js';

const props = defineProps<{
    email: string;
    token: string;
}>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.update'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <Head title="Reset Password" />

    <AuthLayout 
        title="Reset Password" 
        description="Please enter your new password below."
    >
        <form @submit.prevent="submit" class="space-y-4">
            
            <div class="grid gap-2">
                <Label for="email">Email</Label>
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autofocus
                    autocomplete="username"
                    readonly 
                    class="bg-gray-100 cursor-not-allowed text-gray-500"
                />
                <InputError :message="form.errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="password">New Password</Label>
                <Input
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                />
                <InputError :message="form.errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">Confirm Password</Label>
                <Input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                />
                <InputError :message="form.errors.password_confirmation" />
            </div>

            <Button class="w-full mt-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Reset Password
            </Button>
        </form>
    </AuthLayout>
</template>