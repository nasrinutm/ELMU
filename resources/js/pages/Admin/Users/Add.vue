<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { route } from 'ziggy-js';
import {
    AlertCircle,
    UserPlus,
    HelpCircle,
    ArrowLeft,
    ChevronDown
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';

const page = usePage();
const successMessage = computed(() => page.props.flash?.success);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Users', href: route('users.index') },
    { title: 'Add User', href: route('users.create') },
];

const props = defineProps({
    roles: {
        type: Array as () => string[],
        required: true,
    },
});

const form = useForm({
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: '',
});

const submit = () => {
    // 1. Clear previous errors
    form.clearErrors();

    // 2. Client-side manual validation for notification below boxes
    if (!form.name) form.setError('name', 'Please input full name');
    if (!form.username) form.setError('username', 'Please input username');
    if (!form.email) form.setError('email', 'Please input email address');
    if (!form.role) form.setError('role', 'Please select a system role');
    if (!form.password) form.setError('password', 'Please input password');

    // 3. Only submit if no errors exist
    if (!form.hasErrors) {
        form.post(route('users.store'), {
            onSuccess: () => form.reset(),
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
    }
};
</script>

<template>
    <Head title="Add User" />

    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-5xl mx-auto p-6 space-y-6 font-sans antialiased text-slate-900">

            <div class="flex items-center justify-between border-b border-slate-100 pb-6">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-slate-100 rounded-lg text-slate-600 border border-slate-200 shadow-sm">
                        <UserPlus class="w-6 h-6" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight uppercase">Add New User</h1>
                        <p class="text-sm text-slate-500 font-medium">Create a new student, teacher, or admin account.</p>
                    </div>
                </div>
                <Link :href="route('users.index')" class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-action transition-all group">
                    <ArrowLeft class="w-4 h-4 transition-transform group-hover:-translate-x-1" /> Back to List
                </Link>
            </div>

            <transition
                enter-active-class="transform transition ease-out duration-300"
                enter-from-class="translate-y-2 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
            >
                <div v-if="successMessage" class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-none shadow-sm font-bold uppercase text-[10px] tracking-widest">
                    {{ successMessage }}
                </div>
            </transition>

            <div class="bg-white border border-slate-200 rounded-none shadow-sm p-8 sm:p-10">
                <form @submit.prevent="submit" class="space-y-8">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">

                        <div class="space-y-2">
                            <label for="name" class="block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                @input="form.clearErrors('name')"
                                class="flex h-12 w-full rounded-none border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 transition-all focus:outline-none focus:ring-1 focus:ring-action focus:border-action placeholder:text-slate-300"
                                :class="{'border-red-500 ring-1 ring-red-500': form.errors.name}"
                                placeholder="e.g. Ahmad Albab"
                            />
                            <p v-if="form.errors.name" class="text-red-600 text-[10px] font-bold uppercase mt-2 italic flex items-center gap-1">
                                <AlertCircle class="w-3 h-3" /> {{ form.errors.name }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label for="username" class="block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">
                                Username <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="username"
                                v-model="form.username"
                                type="text"
                                @input="form.clearErrors('username')"
                                class="flex h-12 w-full rounded-none border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 focus:outline-none focus:ring-1 focus:ring-action focus:border-action transition-all placeholder:text-slate-300"
                                :class="{'border-red-500 ring-1 ring-red-500': form.errors.username}"
                                placeholder="e.g. ahmad123"
                            />
                            <p v-if="form.errors.username" class="text-red-600 text-[10px] font-bold uppercase mt-2 italic flex items-center gap-1">
                                <AlertCircle class="w-3 h-3" /> {{ form.errors.username }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <label for="email" class="block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <TooltipProvider>
                                    <Tooltip>
                                        <TooltipTrigger as-child>
                                            <HelpCircle class="h-3.5 w-3.5 text-slate-300 cursor-help" />
                                        </TooltipTrigger>
                                        <TooltipContent class="bg-slate-900 text-white border-none text-[10px] uppercase tracking-widest p-2">
                                            <p>Must be a valid email (e.g., name@gmail.com)</p>
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                            </div>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                @input="form.clearErrors('email')"
                                class="flex h-12 w-full rounded-none border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 focus:outline-none focus:ring-1 focus:ring-action focus:border-action transition-all placeholder:text-slate-300"
                                :class="{'border-red-500 ring-1 ring-red-500': form.errors.email}"
                                placeholder="e.g. ahmad@example.com"
                            />
                            <p v-if="form.errors.email" class="text-red-600 text-[10px] font-bold uppercase mt-2 italic flex items-center gap-1">
                                <AlertCircle class="w-3 h-3" /> {{ form.errors.email }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label for="role" class="block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">
                                System Role <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select
                                    id="role"
                                    v-model="form.role"
                                    @change="form.clearErrors('role')"
                                    class="flex h-12 w-full rounded-none border border-slate-200 bg-white px-4 text-sm uppercase tracking-widest text-slate-700 focus:outline-none focus:ring-1 focus:ring-action focus:border-action transition-all appearance-none cursor-pointer pr-10"
                                    :class="{'border-red-500 ring-1 ring-red-500': form.errors.role}"
                                >
                                    <option value="" disabled>Select a role</option>
                                    <option v-for="roleName in roles" :key="roleName" :value="roleName">
                                        {{ roleName.toUpperCase() }}
                                    </option>
                                </select>
                                <ChevronDown class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                            </div>
                            <p v-if="form.errors.role" class="text-red-600 text-[10px] font-bold uppercase mt-2 italic flex items-center gap-1">
                                <AlertCircle class="w-3 h-3" /> {{ form.errors.role }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <label for="password" class="block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">
                                    Password <span class="text-red-500">*</span>
                                </label>
                                <TooltipProvider>
                                    <Tooltip>
                                        <TooltipTrigger as-child>
                                            <HelpCircle class="h-3.5 w-3.5 text-slate-300 cursor-help" />
                                        </TooltipTrigger>
                                        <TooltipContent class="bg-slate-900 text-white border-none p-3 shadow-xl">
                                            <div class="space-y-1">
                                                <p class="font-bold text-[10px] uppercase tracking-wider border-b border-slate-700 pb-1 mb-1">Requirements:</p>
                                                <ul class="text-[9px] uppercase tracking-widest list-disc pl-3 space-y-1">
                                                    <li>At least 8 characters long</li>
                                                    <li>Must be alphanumeric</li>
                                                </ul>
                                            </div>
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                            </div>
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                @input="form.clearErrors('password')"
                                class="flex h-12 w-full rounded-none border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 focus:outline-none focus:ring-1 focus:ring-action focus:border-action transition-all placeholder:text-slate-300"
                                :class="{'border-red-500 ring-1 ring-red-500': form.errors.password}"
                                placeholder="Enter secure password"
                            />
                            <p v-if="form.errors.password" class="text-red-600 text-[10px] font-bold uppercase mt-2 italic flex items-center gap-1">
                                <AlertCircle class="w-3 h-3" /> {{ form.errors.password }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label for="password_confirmation" class="block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">
                                Confirm <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                class="flex h-12 w-full rounded-none border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 focus:outline-none focus:ring-1 focus:ring-action focus:border-action transition-all placeholder:text-slate-300"
                                placeholder="Re-enter password"
                            />
                        </div>

                    </div>

                    <div class="pt-8 flex justify-end border-t border-slate-50">
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-action hover:bg-action hover:opacity-90 text-white font-bold text-[10px] uppercase tracking-[0.3em] px-12 py-5 rounded-none shadow-md transition-all disabled:opacity-50 h-auto"
                        >
                            {{ form.processing ? 'Saving...' : 'Add User' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>
