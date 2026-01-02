<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { route } from 'ziggy-js';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Confirm Password" />

    <AuthLayout 
        title="Confirm Password" 
        description="This is a secure area of the application. Please confirm your password before continuing."
    >
        <form @submit.prevent="submit" class="space-y-4">
            
            <div class="grid gap-2">
                <Label for="password">Password</Label>
                <Input
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                    autocomplete="current-password"
                    autofocus
                />
                <InputError :message="form.errors.password" />
            </div>

            <Button class="w-full mt-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Confirm
            </Button>
        </form>
    </AuthLayout>
</template>