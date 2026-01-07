<script setup lang="ts">
import { ref, watch, computed, nextTick } from 'vue';
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { type BreadcrumbItem } from '@/types';
import { route } from 'ziggy-js';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import {
    User,
    Save,
    ShieldCheck,
    CheckCircle2,
    X,
    AlertCircle,
    ArrowLeft,
    HelpCircle,
    ChevronDown
} from 'lucide-vue-next';

const props = defineProps<{
    user: {
        id: number;
        name: string;
        username: string;
        email: string;
        role: string;
    };
    roles: string[];
}>();

const page = usePage();

// --- NOTIFICATION & MODAL STATE ---
const showNotification = ref(false);
const showConfirmModal = ref(false);
const flashSuccess = computed(() => (page.props as any).flash?.success);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'User Management', href: route('users.index') },
    { title: `Edit ${props.user.name}`, href: route('users.edit', props.user.id) },
];

const form = useForm({
    name: props.user.name,
    username: props.user.username,
    email: props.user.email,
    role: props.user.role,
    password: '',
    password_confirmation: '',
});

// Watch for flash messages
watch(flashSuccess, async (newVal) => {
    if (newVal) {
        showNotification.value = false;
        await nextTick();
        showNotification.value = true;
        setTimeout(() => { showNotification.value = false; }, 5000);
    }
}, { immediate: true });

// --- MANUAL VALIDATION & SUBMIT ---
const requestSubmit = () => {
    form.clearErrors();

    if (!form.name) form.setError('name', 'Please input full name');
    if (!form.username) form.setError('username', 'Please input username');
    if (!form.email) form.setError('email', 'Please input email address');
    if (!form.role) form.setError('role', 'Please select a system role');

    if (!form.hasErrors) {
        showConfirmModal.value = true;
    }
};

const submit = () => {
    form.put(route('users.update', props.user.id), {
        onSuccess: () => {
            form.reset('password', 'password_confirmation');
            showConfirmModal.value = false;
        },
        onFinish: () => showConfirmModal.value = false,
    });
};

const capitalize = (s: string) => s.charAt(0).toUpperCase() + s.slice(1);
</script>

<template>
    <Head title="Edit User" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-4xl mx-auto p-4 space-y-4 antialiased font-sans">

            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-slate-100 rounded text-slate-600 border border-slate-200 shadow-sm">
                        <User class="w-5 h-5" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-slate-900 uppercase">Edit User</h1>
                        <p class="text-[10px] text-slate-500 font-medium italic">Update details for {{ user.name }}</p>
                    </div>
                </div>
                <Link :href="route('users.index')" class="flex items-center gap-2 text-[9px] font-bold uppercase tracking-widest text-slate-400 hover:text-[var(--action-color)] transition-all group">
                    <ArrowLeft class="w-3.5 h-3.5 transition-transform group-hover:-translate-x-1" /> Back to List
                </Link>
            </div>

            <div class="bg-white border border-slate-200 rounded-none shadow-sm">
                <div class="p-6">
                    <form @submit.prevent="requestSubmit" class="space-y-6">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">

                            <div class="space-y-1">
                                <Label for="name" class="block text-[9px] font-bold uppercase tracking-[0.2em] text-slate-400">
                                    Full Name <span class="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="name"
                                    type="text"
                                    class="h-10 rounded-none border-slate-200 focus:ring-1 focus:ring-[var(--action-color)]"
                                    v-model="form.name"
                                    @input="form.clearErrors('name')"
                                    :class="{'border-red-500 ring-1 ring-red-500': form.errors.name}"
                                />
                                <p v-if="form.errors.name" class="text-red-600 text-[9px] font-bold uppercase italic flex items-center gap-1">
                                    <AlertCircle class="w-3 h-3" /> {{ form.errors.name }}
                                </p>
                            </div>

                            <div class="space-y-1">
                                <Label for="username" class="block text-[9px] font-bold uppercase tracking-[0.2em] text-slate-400">
                                    Username <span class="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="username"
                                    type="text"
                                    class="h-10 rounded-none border-slate-200 focus:ring-1 focus:ring-[var(--action-color)]"
                                    v-model="form.username"
                                    @input="form.clearErrors('username')"
                                    :class="{'border-red-500 ring-1 ring-red-500': form.errors.username}"
                                />
                                <p v-if="form.errors.username" class="text-red-600 text-[9px] font-bold uppercase italic flex items-center gap-1">
                                    <AlertCircle class="w-3 h-3" /> {{ form.errors.username }}
                                </p>
                            </div>

                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <Label for="email" class="text-[9px] font-bold uppercase tracking-[0.2em] text-slate-400">
                                        Email Address <span class="text-red-500">*</span>
                                    </Label>
                                    <TooltipProvider>
                                        <Tooltip>
                                            <TooltipTrigger as-child>
                                                <HelpCircle class="h-3 w-3 text-slate-300 cursor-help" />
                                            </TooltipTrigger>
                                            <TooltipContent class="bg-slate-900 text-white border-none text-[9px] uppercase tracking-widest p-2">
                                                <p>Must be a valid format (e.g., name@gmail.com)</p>
                                            </TooltipContent>
                                        </Tooltip>
                                    </TooltipProvider>
                                </div>
                                <Input
                                    id="email"
                                    type="email"
                                    class="h-10 rounded-none border-slate-200 focus:ring-1 focus:ring-[var(--action-color)]"
                                    v-model="form.email"
                                    @input="form.clearErrors('email')"
                                    :class="{'border-red-500 ring-1 ring-red-500': form.errors.email}"
                                />
                                <p v-if="form.errors.email" class="text-red-600 text-[9px] font-bold uppercase italic flex items-center gap-1">
                                    <AlertCircle class="w-3 h-3" /> {{ form.errors.email }}
                                </p>
                            </div>

                            <div class="space-y-1">
                                <Label for="role" class="block text-[9px] font-bold uppercase tracking-[0.2em] text-slate-400">
                                    Access Level (Role) <span class="text-red-500">*</span>
                                </Label>
                                <div class="relative">
                                    <select
                                        id="role"
                                        class="w-full h-10 px-3 rounded-none border border-slate-200 bg-white text-[11px] font-bold uppercase tracking-widest focus:outline-none focus:ring-1 focus:ring-[var(--action-color)] appearance-none cursor-pointer pr-10"
                                        v-model="form.role"
                                        @change="form.clearErrors('role')"
                                        :class="{'border-red-500 ring-1 ring-red-500': form.errors.role}"
                                    >
                                        <option v-for="role in roles" :key="role" :value="role">
                                            {{ capitalize(role) }}
                                        </option>
                                    </select>
                                    <ChevronDown class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                                </div>
                                <p v-if="form.errors.role" class="text-red-600 text-[9px] font-bold uppercase italic flex items-center gap-1">
                                    <AlertCircle class="w-3 h-3" /> {{ form.errors.role }}
                                </p>
                            </div>
                        </div>

                        <div class="bg-slate-50 p-4 space-y-4 border border-slate-100">
                            <h4 class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 flex items-center gap-2">
                                <ShieldCheck class="w-3.5 h-3.5" /> Security Credentials
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <Label for="password" class="text-[9px] font-bold uppercase tracking-widest text-slate-500">New Password</Label>
                                        <TooltipProvider>
                                            <Tooltip>
                                                <TooltipTrigger as-child>
                                                    <HelpCircle class="h-3 w-3 text-slate-300 cursor-help" />
                                                </TooltipTrigger>
                                                <TooltipContent class="bg-slate-900 text-white border-none p-2 shadow-xl max-w-[180px]">
                                                    <div class="space-y-1">
                                                        <p class="font-bold text-[8px] uppercase tracking-wider border-b border-slate-700 pb-1 mb-1">Requirements:</p>
                                                        <ul class="text-[8px] uppercase tracking-widest list-disc pl-3 space-y-1">
                                                            <li>At least 8 characters</li>
                                                            <li>Alphanumeric format</li>
                                                        </ul>
                                                    </div>
                                                </TooltipContent>
                                            </Tooltip>
                                        </TooltipProvider>
                                    </div>
                                    <Input id="password" type="password" class="h-10 rounded-none border-slate-200 focus:ring-1 focus:ring-[var(--action-color)]" v-model="form.password" placeholder="••••••••" />
                                    <p class="text-[8px] text-slate-400 font-medium italic">Leave blank to keep current.</p>
                                    <InputError :message="form.errors.password" />
                                </div>

                                <div class="space-y-1">
                                    <Label for="password_confirmation" class="text-[9px] font-bold uppercase tracking-widest text-slate-500">Confirm Password</Label>
                                    <Input id="password_confirmation" type="password" class="h-10 rounded-none border-slate-200 focus:ring-1 focus:ring-[var(--action-color)]" v-model="form.password_confirmation" placeholder="••••••••" />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end pt-4 border-t border-slate-50">
                            <Button
                                type="submit"
                                :disabled="form.processing"
                                class="bg-[var(--action-color)] hover:bg-[var(--action-color)] hover:opacity-90 text-white font-bold shadow-md rounded-none transition-all text-[9px] uppercase tracking-widest px-8 py-3 h-auto"
                            >
                                <Save class="w-3.5 h-3.5 mr-2" />
                                {{ form.processing ? 'Saving...' : 'Update Account' }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <transition name="modal">
            <div v-if="showConfirmModal" class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
                <div class="bg-white max-w-sm w-full p-8 border border-slate-200 shadow-2xl rounded-none text-center font-sans antialiased">
                    <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mb-4 mx-auto border border-slate-100">
                        <AlertCircle class="w-6 h-6 text-[var(--action-color)]" />
                    </div>
                    <h3 class="font-bold uppercase tracking-[0.2em] text-slate-900 mb-2 text-xs">Save Changes?</h3>
                    <p class="text-xs text-slate-500 mb-6 leading-relaxed px-4">
                        Are you sure you want to update the profile for <strong>{{ user.name }}</strong>?
                    </p>
                    <div class="flex gap-3 w-full">
                        <button @click="showConfirmModal = false" class="flex-1 py-3 text-[9px] font-bold uppercase border border-slate-100 hover:bg-slate-50 transition-colors tracking-widest">Cancel</button>
                        <button @click="submit" class="flex-1 py-3 bg-[var(--action-color)] text-white text-[9px] font-bold uppercase shadow-lg hover:opacity-90 transition-all tracking-widest border-none">Confirm</button>
                    </div>
                </div>
            </div>
        </transition>

    </AppLayout>
</template>

<style scoped>
/* Toast Animation */
.toast-enter-active, .toast-leave-active { transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.toast-enter-from { transform: translateX(100%); opacity: 0; }
.toast-leave-to { transform: translateY(-20px); opacity: 0; }

/* Modal Animation */
.modal-enter-active, .modal-leave-active { transition: opacity 0.3s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>
