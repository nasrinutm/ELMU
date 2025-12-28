<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { route } from 'ziggy-js';

const props = defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Forgot Password" />

    <AuthLayout 
        title="Forgot Password" 
        description="Forgot your password? No problem. Just let us know your email address and we will email you a password reset link."
    >
        <div v-if="status" class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
            {{ status }}
        </div>

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
                    placeholder="name@example.com"
                />
                <InputError :message="form.errors.email" />
            </div>

            <Button class="w-full mt-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Email Password Reset Link
            </Button>
        </form>
    </AuthLayout>
</template>