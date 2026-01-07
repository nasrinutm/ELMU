<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { route } from 'ziggy-js';
import { AlertCircle, UserPlus, HelpCircle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
// Import Tooltip Components
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
    form.post(route('users.store'), {
        onSuccess: () => form.reset(),
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Add User" />

    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-4xl mx-auto p-6 space-y-6 font-sans antialiased text-slate-900">

            <transition
                enter-active-class="transform transition ease-out duration-300"
                enter-from-class="translate-y-2 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
            >
                <div v-if="successMessage" class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-none shadow-sm font-bold uppercase text-[10px] tracking-widest">
                    {{ successMessage }}
                </div>
            </transition>

            <div class="mb-6 flex items-center gap-4 border-b border-slate-100 pb-6">
                <div class="p-3 bg-slate-100 rounded-lg text-slate-600 border border-slate-200 shadow-sm">
                    <UserPlus class="w-6 h-6" />
                </div>
                <div>
                    <h1 class="text-3xl font-bold tracking-tight uppercase">Add New User</h1>
                    <p class="text-sm text-slate-500 font-medium">Create a new student, teacher, or admin account.</p>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-none shadow-sm p-8 sm:p-12">
                <form @submit.prevent="submit" class="space-y-8">

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                        <label for="name" class="block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <div class="md:col-span-3">
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                class="flex h-12 w-full rounded-none border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 transition-all focus:outline-none focus:ring-1 focus:ring-action focus:border-action placeholder:text-slate-300"
                                :class="{'border-red-500 ring-1 ring-red-500': form.errors.name}"
                                placeholder="e.g. Ahmad Albab"
                            />
                            <p v-if="form.errors.name" class="text-red-600 text-[10px] font-bold uppercase mt-2 italic flex items-center gap-1">
                                <AlertCircle class="w-3 h-3" /> {{ form.errors.name }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                        <label for="username" class="block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">
                            Username <span class="text-red-500">*</span>
                        </label>
                        <div class="md:col-span-3">
                            <input
                                id="username"
                                v-model="form.username"
                                type="text"
                                required
                                class="flex h-12 w-full rounded-none border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 focus:outline-none focus:ring-1 focus:ring-action focus:border-action transition-all placeholder:text-slate-300"
                                :class="{'border-red-500 ring-1 ring-red-500': form.errors.username}"
                                placeholder="e.g. ahmad123"
                            />
                            <p v-if="form.errors.username" class="text-red-600 text-[10px] font-bold uppercase mt-2 italic flex items-center gap-1">
                                <AlertCircle class="w-3 h-3" /> {{ form.errors.username }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
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
                                        <p>Must be a valid email format (e.g., name@gmail.com)</p>
                                    </TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                        </div>
                        <div class="md:col-span-3">
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                class="flex h-12 w-full rounded-none border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 focus:outline-none focus:ring-1 focus:ring-action focus:border-action transition-all placeholder:text-slate-300"
                                :class="{'border-red-500 ring-1 ring-red-500': form.errors.email}"
                                placeholder="e.g. ahmad@example.com"
                            />
                            <p v-if="form.errors.email" class="text-red-600 text-[10px] font-bold uppercase mt-2 italic flex items-center gap-1">
                                <AlertCircle class="w-3 h-3" /> {{ form.errors.email }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                        <label for="role" class="block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">
                            System Role <span class="text-red-500">*</span>
                        </label>
                        <div class="md:col-span-3">
                            <select
                                id="role"
                                v-model="form.role"
                                required
                                class="flex h-12 w-full rounded-none border border-slate-200 bg-white px-4 text-sm uppercase tracking-widest text-slate-700 focus:outline-none focus:ring-1 focus:ring-action focus:border-action transition-all appearance-none cursor-pointer"
                                :class="{'border-red-500 ring-1 ring-red-500': form.errors.role}"
                            >
                                <option value="" disabled>Select a role</option>
                                <option v-for="roleName in roles" :key="roleName" :value="roleName">
                                    {{ roleName.toUpperCase() }}
                                </option>
                            </select>
                            <p v-if="form.errors.role" class="text-red-600 text-[10px] font-bold uppercase mt-2 italic flex items-center gap-1">
                                <AlertCircle class="w-3 h-3" /> {{ form.errors.role }}
                            </p>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-100 border-dashed space-y-8">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
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
                            <div class="md:col-span-3">
                                <input
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    required
                                    class="flex h-12 w-full rounded-none border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 focus:outline-none focus:ring-1 focus:ring-action focus:border-action transition-all placeholder:text-slate-300"
                                    :class="{'border-red-500 ring-1 ring-red-500': form.errors.password}"
                                    placeholder="Enter secure password"
                                />
                                <p v-if="form.errors.password" class="text-red-600 text-[10px] font-bold uppercase mt-2 italic flex items-center gap-1">
                                    <AlertCircle class="w-3 h-3" /> {{ form.errors.password }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                            <label for="password_confirmation" class="block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">
                                Confirm <span class="text-red-500">*</span>
                            </label>
                            <div class="md:col-span-3">
                                <input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    type="password"
                                    required
                                    class="flex h-12 w-full rounded-none border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 focus:outline-none focus:ring-1 focus:ring-action focus:border-action transition-all placeholder:text-slate-300"
                                    placeholder="Re-enter password"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="pt-8 flex justify-end border-t border-slate-50">
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-action hover:opacity-90 text-white font-bold text-[10px] uppercase tracking-[0.3em] px-12 py-5 rounded-none shadow-md transition-all disabled:opacity-50 h-auto"
                        >
                            {{ form.processing ? 'Saving...' : 'Add User' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>
