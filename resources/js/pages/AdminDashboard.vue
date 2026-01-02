<script setup lang="ts">
import { Head, Link, usePage, useForm } from '@inertiajs/vue3';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import {
    ShieldCheck,
    BookOpen,
    GraduationCap,
    Users,
    X,
    UploadCloud,
    PlusSquare
} from 'lucide-vue-next';
import { route } from 'ziggy-js';
import { ref } from 'vue';

// --- PROPS DEFINITION ---
const props = defineProps<{
    stats: any;
    recentUsers?: any[];
}>();

const page = usePage();
const user = page.props.auth.user as any;

// --- MODAL & UPLOAD LOGIC ---
const showUploadModal = ref(false);

const uploadForm = useForm({
    title: '',
    file: null as File | null,
});

const submitUpload = () => {
    uploadForm.post(route('upload.store'), {
        onSuccess: () => {
            showUploadModal.value = false;
            uploadForm.reset();
        },
        forceFormData: true,
    });
};

const breadcrumbs = [
    { title: 'Overview', href: '/dashboard' },
];

const formatDate = (dateString: string) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric'
    });
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-7xl mx-auto space-y-8 pb-10 px-4 sm:px-0 font-sans font-normal antialiased text-gray-900">

            <div class="border-b border-slate-200 pb-6 font-sans">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 uppercase">
                    System Overview
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    Welcome back, <span class="text-action font-semibold">{{ user.name }}</span>.
                    Monitor school performance and recent activity.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 font-sans">
                <div class="relative overflow-hidden shadow-lg bg-gradient-to-br from-fuchsia-600 to-purple-700 p-6 text-white rounded-sm">
                    <div class="relative z-10 flex justify-between items-start">
                        <div>
                            <p class="text-xs font-medium opacity-90 uppercase tracking-wider">Admins</p>
                            <h3 class="mt-2 text-4xl font-bold tabular-nums">{{ stats.admins }}</h3>
                            <p class="mt-2 text-[10px] opacity-75">System Managers</p>
                        </div>
                        <div class="p-1.5 bg-white/20 rounded-sm"><ShieldCheck class="w-5 h-5 text-white" /></div>
                    </div>
                </div>

                <div class="relative overflow-hidden shadow-lg bg-gradient-to-br from-teal-500 to-emerald-600 p-6 text-white rounded-sm">
                    <div class="relative z-10 flex justify-between items-start">
                        <div>
                            <p class="text-xs font-medium opacity-90 uppercase tracking-wider">Teachers</p>
                            <h3 class="mt-2 text-4xl font-bold tabular-nums">{{ stats.teachers }}</h3>
                            <p class="mt-2 text-[10px] opacity-75">Instructors</p>
                        </div>
                        <div class="p-1.5 bg-white/20 rounded-sm"><BookOpen class="w-5 h-5 text-white" /></div>
                    </div>
                </div>

                <div class="relative overflow-hidden shadow-lg bg-gradient-to-br from-orange-500 to-amber-600 p-6 text-white rounded-sm">
                    <div class="relative z-10 flex justify-between items-start">
                        <div>
                            <p class="text-xs font-medium opacity-90 uppercase tracking-wider">Students</p>
                            <h3 class="mt-2 text-4xl font-bold tabular-nums">{{ stats.students }}</h3>
                            <p class="mt-2 text-[10px] opacity-75">Learners</p>
                        </div>
                        <div class="p-1.5 bg-white/20 rounded-sm"><GraduationCap class="w-5 h-5 text-white" /></div>
                    </div>
                </div>

                <div class="relative overflow-hidden shadow-lg bg-gradient-to-br from-blue-600 to-indigo-700 p-6 text-white rounded-sm">
                    <div class="relative z-10 flex justify-between items-start">
                        <div>
                            <p class="text-xs font-medium opacity-90 uppercase tracking-wider">Total Users</p>
                            <h3 class="mt-2 text-4xl font-bold tabular-nums">{{ stats.total_users }}</h3>
                            <p class="mt-2 text-[10px] opacity-75">All Accounts</p>
                        </div>
                        <div class="p-1.5 bg-white/20 rounded-sm"><Users class="w-5 h-5 text-white" /></div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch font-sans">

                <div class="lg:col-span-2 flex flex-col bg-white border border-slate-200 shadow-sm rounded-sm">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                        <h3 class="font-bold text-gray-900 flex items-center gap-2 uppercase text-sm tracking-widest">
                            Recent Registrations
                        </h3>
                        <Link :href="route('users.index')" class="text-xs text-blue-600 hover:underline font-bold uppercase">View All</Link>
                    </div>

                    <div class="flex-grow divide-y divide-slate-50 font-sans">
                        <div v-for="u in recentUsers" :key="u.id" class="p-4 flex items-center justify-between hover:bg-slate-50 transition border-l-2 border-transparent hover:border-action">
                            <div class="flex items-center gap-4 font-sans">
                                <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 font-bold text-xs uppercase shadow-inner">
                                    {{ u.name.charAt(0) }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900 uppercase tracking-tight">{{ u.name }}</p>
                                    <p class="text-[10px] text-gray-400 font-medium uppercase tracking-wider">{{ u.email }}</p>
                                </div>
                            </div>
                            <span class="text-[10px] font-bold text-slate-400 bg-slate-50 border border-slate-100 px-3 py-1 uppercase tracking-widest">{{ formatDate(u.created_at) }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col">
                    <div class="flex-grow bg-white border border-gray-200 shadow-sm p-8 flex flex-col justify-between rounded-sm font-sans">
                        <div>
                            <h3 class="font-bold text-[10px] uppercase tracking-[0.2em] text-slate-400 mb-8 flex items-center gap-2">
                                <ShieldCheck class="w-4 h-4 text-emerald-500" /> System Status
                            </h3>
                            <div class="space-y-6">
                                <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-widest">
                                    <span class="text-slate-500">Nodes Status</span>
                                    <span class="text-emerald-700 bg-emerald-50 border border-emerald-100 px-2 py-1">Operational</span>
                                </div>
                                <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-widest">
                                    <span class="text-slate-500 font-sans">AI Chatbot</span>
                                    <span class="text-blue-700 bg-blue-50 border border-blue-100 px-2 py-1">Active</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-12 pt-8 border-t border-slate-100">
                            <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold mb-4 text-center italic leading-relaxed">System-wide Data Management</p>
                            <button
                                @click="showUploadModal = true"
                                class="block w-full text-center bg-slate-900 text-white py-4 hover:bg-action transition text-[10px] font-bold uppercase tracking-[0.3em] shadow-lg rounded-none"
                            >
                                Update Knowledge Base
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showUploadModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 font-sans">
            <div class="bg-white w-full max-w-2xl p-10 rounded-none shadow-2xl border border-slate-200">
                <div class="flex justify-between items-center mb-10">
                    <h3 class="text-sm font-bold uppercase tracking-[0.2em] text-slate-900 flex items-center gap-3">
                        <UploadCloud class="w-5 h-5 text-action" /> Upload Material
                    </h3>
                    <button @click="showUploadModal = false" class="text-slate-400 hover:text-action transition"><X class="w-6 h-6" /></button>
                </div>

                <form @submit.prevent="submitUpload" class="space-y-10">
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-400 mb-3 tracking-widest">Document Title</label>
                        <input
                            v-model="uploadForm.title"
                            type="text"
                            placeholder="e.g., Physics Chapter 1"
                            class="w-full bg-white border border-slate-200 px-4 py-4 text-sm focus:ring-1 focus:ring-action focus:border-action outline-none transition placeholder:text-slate-300 font-medium rounded-none"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-400 mb-3 tracking-widest">File Attachment (PDF, TXT, CSV)</label>
                        <div class="relative border-2 border-dashed border-slate-200 p-12 text-center hover:border-action transition-colors bg-slate-50/30 group">
                            <input
                                type="file"
                                @input="uploadForm.file = ($event.target as HTMLInputElement).files?.[0] || null"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                required
                            />
                            <div class="flex flex-col items-center gap-2">
                                <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">
                                    {{ uploadForm.file ? uploadForm.file.name : 'Choose File No file chosen' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div v-if="uploadForm.progress" class="w-full bg-slate-100 h-1 overflow-hidden">
                        <div class="bg-action h-full transition-all duration-300" :style="{ width: uploadForm.progress.percentage + '%' }"></div>
                    </div>

                    <div class="flex justify-end items-center gap-10 pt-4">
                        <button
                            type="button"
                            @click="showUploadModal = false"
                            class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500 hover:text-slate-900 transition"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="uploadForm.processing"
                            class="bg-slate-900 text-white px-8 py-4 text-[10px] font-bold uppercase tracking-[0.2em] shadow-xl hover:bg-action transition flex items-center gap-3 disabled:opacity-50"
                        >
                            <PlusSquare v-if="!uploadForm.processing" class="w-4 h-4" />
                            {{ uploadForm.processing ? 'Uploading...' : 'Upload to AI' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>
