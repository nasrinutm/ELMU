<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/auth/AuthCardLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { route } from 'ziggy-js';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthBase
        title="Log in to your account"
        description="Enter your email and password below to log in"
    >
        <Head title="Log in" />

        <div
            v-if="status"
            class="mb-4 text-center text-sm font-medium text-green-400"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email" class="text-white">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        v-model="form.email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="email@example.com"
                        class="bg-white text-black border-transparent focus:ring-2 focus:ring-[#FFD900]"
                    />
                    <InputError :message="form.errors.email" class="text-red-300" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password" class="text-white">Password</Label>
                    </div>
                    <Input
                        id="password"
                        type="password"
                        v-model="form.password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="Password"
                        class="bg-white text-black border-transparent focus:ring-2 focus:ring-[#FFD900]"
                    />
                    <InputError :message="form.errors.password" class="text-red-300" />
                </div>

                <div class="flex items-center justify-between">
                    <Label for="remember" class="flex items-center space-x-3 text-white">
                        <Checkbox
                            id="remember"
                            :checked="form.remember"
                            @update:checked="(val) => form.remember = val"
                            :tabindex="3"
                            class="border-white data-[state=checked]:bg-[#FFD900] data-[state=checked]:text-[#003366]"
                        />
                        <span>Remember me</span>
                    </Label>
                </div>

                <Button
                    type="submit"
                    class="mt-4 w-full bg-[#FFD900] text-[#003366] hover:bg-[#e6c300] font-bold"
                    :tabindex="4"
                    :disabled="form.processing"
                    data-test="login-button"
                >
                    <LoaderCircle
                        v-if="form.processing"
                        class="h-4 w-4 animate-spin mr-2"
                    />
                    Log in
                </Button>
            </div>
        </form>
    </AuthBase>
</template>