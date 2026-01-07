<script setup lang="ts">
import { ref, watch, computed, nextTick } from 'vue';
import { Head, useForm, usePage, Link } from '@inertiajs/vue3'; // FIXED: Added Link
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { type BreadcrumbItem } from '@/types';
import { route } from 'ziggy-js';
// FIXED: Added Tooltip Imports
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import {
    User,
    Save,
    ShieldCheck,
    CheckCircle2,
    X,
    AlertCircle,
    ArrowLeft,
    HelpCircle // FIXED: Added HelpCircle
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
        <div class="max-w-3xl mx-auto p-4 sm:p-6 lg:p-8 space-y-6 antialiased font-sans">

            <transition name="toast">
                <div v-if="showNotification" class="fixed top-10 right-10 z-[100] flex items-center gap-4 bg-slate-900 text-white p-5 shadow-2xl border-l-4 border-indigo-500 min-w-[350px]">
                    <div class="bg-indigo-500/20 p-2"><CheckCircle2 class="w-6 h-6 text-indigo-500" /></div>
                    <div class="flex-grow">
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-indigo-500">System Success</p>
                        <p class="text-sm font-medium">{{ flashSuccess }}</p>
                    </div>
                    <button @click="showNotification = false" class="text-slate-500 hover:text-white transition-colors">
                        <X class="w-4 h-4" />
                    </button>
                </div>
            </transition>

            <div class="flex items-center justify-between border-b border-slate-200 pb-6">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900 uppercase flex items-center gap-3">
                        <User class="w-8 h-8 text-indigo-600" />
                        Edit User Profile
                    </h1>
                    <p class="text-slate-500 mt-1 uppercase text-[11px] font-bold tracking-widest italic">
                        Modifying account details for ID: #{{ user.id }}
                    </p>
                </div>
                <Link :href="route('users.index')" class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-indigo-600 transition-all">
                    <ArrowLeft class="w-4 h-4" /> Back to List
                </Link>
            </div>

            <div class="bg-white border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-8">
                    <form @submit.prevent="showConfirmModal = true" class="space-y-6">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="name" class="text-[10px] font-bold uppercase tracking-widest text-slate-500">
                                    Full Name <span class="text-red-500">*</span>
                                </Label>
                                <Input id="name" type="text" class="rounded-none border-slate-200 focus:ring-indigo-600" v-model="form.name" required />
                                <InputError :message="form.errors.name" />
                            </div>

                            <div class="space-y-2">
                                <Label for="username" class="text-[10px] font-bold uppercase tracking-widest text-slate-500">
                                    Username <span class="text-red-500">*</span>
                                </Label>
                                <Input id="username" type="text" class="rounded-none border-slate-200 focus:ring-indigo-600" v-model="form.username" required />
                                <InputError :message="form.errors.username" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <Label for="email" class="text-[10px] font-bold uppercase tracking-widest text-slate-500">
                                    Email Address <span class="text-red-500">*</span>
                                </Label>
                                <TooltipProvider>
                                    <Tooltip>
                                        <TooltipTrigger as-child>
                                            <HelpCircle class="h-3.5 w-3.5 text-slate-300 cursor-help" />
                                        </TooltipTrigger>
                                        <TooltipContent class="bg-slate-900 text-white border-none text-[10px] uppercase tracking-widest p-2">
                                            <p>Must be a valid email (e.g., user@gmail.com)</p>
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                            </div>
                            <Input id="email" type="email" class="rounded-none border-slate-200 focus:ring-indigo-600" v-model="form.email" required />
                            <InputError :message="form.errors.email" />
                        </div>

                        <div class="space-y-2">
                            <Label for="role" class="text-[10px] font-bold uppercase tracking-widest text-slate-500">
                                Access Level (Role) <span class="text-red-500">*</span>
                            </Label>
                            <select
                                id="role"
                                class="w-full h-10 px-3 rounded-none border border-slate-200 bg-white text-sm focus:outline-none focus:ring-1 focus:ring-indigo-600 appearance-none"
                                v-model="form.role"
                                required
                            >
                                <option v-for="role in roles" :key="role" :value="role">
                                    {{ capitalize(role) }}
                                </option>
                            </select>
                            <InputError :message="form.errors.role" />
                        </div>

                        <div class="bg-slate-50 p-6 space-y-6 border border-slate-100">
                            <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 flex items-center gap-2">
                                <ShieldCheck class="w-4 h-4" /> Security Credentials
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <Label for="password" class="text-[10px] font-bold uppercase tracking-widest text-slate-500">New Password</Label>
                                        <TooltipProvider>
                                            <Tooltip>
                                                <TooltipTrigger as-child>
                                                    <HelpCircle class="h-3.5 w-3.5 text-slate-300 cursor-help" />
                                                </TooltipTrigger>
                                                <TooltipContent class="bg-slate-900 text-white border-none p-3 shadow-xl max-w-[200px]">
                                                    <div class="space-y-1">
                                                        <p class="font-bold text-[10px] uppercase tracking-wider border-b border-slate-700 pb-1 mb-1">Requirements:</p>
                                                        <ul class="text-[9px] uppercase tracking-widest list-disc pl-3 space-y-1">
                                                            <li>At least 8 characters</li>
                                                            <li>Alphanumeric format</li>
                                                        </ul>
                                                    </div>
                                                </TooltipContent>
                                            </Tooltip>
                                        </TooltipProvider>
                                    </div>
                                    <Input id="password" type="password" class="rounded-none border-slate-200" v-model="form.password" placeholder="••••••••" />
                                    <p class="text-[9px] text-slate-400 font-medium italic">Leave blank to keep current password.</p>
                                    <InputError :message="form.errors.password" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="password_confirmation" class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Confirm Password</Label>
                                    <Input id="password_confirmation" type="password" class="rounded-none border-slate-200" v-model="form.password_confirmation" placeholder="••••••••" />
                                    <InputError :message="form.errors.password_confirmation" />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end pt-4">
                            <Button
                                type="submit"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow-md rounded-none transition-all text-[10px] uppercase tracking-widest px-10 py-6"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                <Save class="w-4 h-4 mr-2" /> Update User Account
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <transition name="modal">
            <div v-if="showConfirmModal" class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
                <div class="bg-white max-w-md w-full p-10 border border-slate-200 shadow-2xl rounded-none text-center">
                    <div class="w-16 h-16 bg-indigo-50 rounded-full flex items-center justify-center mb-6 mx-auto">
                        <AlertCircle class="w-8 h-8 text-indigo-600" />
                    </div>
                    <h3 class="font-bold uppercase tracking-[0.2em] text-slate-900 mb-2 text-sm">Save Changes?</h3>
                    <p class="text-sm text-slate-500 mb-8 leading-relaxed">
                        Are you sure you want to update the profile for <strong>{{ user.name }}</strong>? This might affect their system access permissions.
                    </p>
                    <div class="flex gap-4 w-full">
                        <button @click="showConfirmModal = false" class="flex-1 py-4 text-[10px] font-bold uppercase border border-slate-100 hover:bg-slate-50 transition-colors">Cancel</button>
                        <button @click="submit" class="flex-1 py-4 bg-indigo-600 text-white text-[10px] font-bold uppercase hover:bg-indigo-700 shadow-lg transition-all">Confirm Update</button>
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
