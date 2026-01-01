<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import {
    FileText,
    Download,
    BookOpen,
    Trophy,
    ArrowRight,
    GraduationCap,
    LayoutDashboard,
    HelpCircle,
    TrendingUp
} from 'lucide-vue-next';
import { route } from 'ziggy-js';

// --- TYPE DEFINITIONS ---
interface User {
    name: string;
    roles: any[];
}

// --- PROPS DEFINITION ---
defineProps<{
    stats: {
        users: number;
        materials: number;
        my_materials: number;
        available_quizzes?: number;
    };
    recentMaterials: Array<{
        id: number;
        title: string;
        description: string;
        file_path: string;
        created_at: string;
        user: { name: string };
    }>;
}>();

const page = usePage();
const user = page.props.auth.user as unknown as User;

// Helper to check for teacher role safely
const isTeacher = user.roles.some((r: any) =>
    r === 'teacher' || r?.name === 'teacher'
);

const breadcrumbs = [
    { title: 'Overview', href: '/dashboard' },
];

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric'
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-7xl mx-auto space-y-8 pb-10 px-4 sm:px-0 font-sans font-normal antialiased">

            <div class="border-b border-slate-200 pb-6">
                <h1 class="text-2xl font-semibold tracking-tight text-slate-900 flex items-center gap-3 uppercase">
                    <LayoutDashboard class="w-7 h-7 text-indigo-600" />
                    Student Overview
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    Welcome back, <span class="text-indigo-600 font-semibold">{{ user.name }}</span>. Here is your current progress.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="relative overflow-hidden bg-indigo-600 p-6 text-white rounded-none shadow-sm group transition-all">
                    <div class="relative z-10 flex justify-between items-start">
                        <div>
                            <p class="text-[10px] font-medium opacity-80 uppercase tracking-widest mb-1">Learning Resources</p>
                            <h3 class="text-4xl font-semibold tabular-nums tracking-tight">{{ stats.materials }}</h3>
                            <div class="mt-4 bg-white/20 text-[9px] font-bold px-2 py-1 uppercase inline-block">Available Modules</div>
                        </div>
                        <BookOpen class="w-10 h-10 opacity-20 group-hover:opacity-40 transition-opacity" />
                    </div>
                </div>

                <div class="relative overflow-hidden bg-emerald-500 p-6 text-white rounded-none shadow-sm group transition-all">
                    <div class="relative z-10 flex justify-between items-start">
                        <div>
                            <p class="text-[10px] font-medium opacity-80 uppercase tracking-widest mb-1">
                                {{ isTeacher ? 'Content Shared' : 'Activities Done' }}
                            </p>
                            <h3 class="text-4xl font-semibold tabular-nums tracking-tight">{{ stats.my_materials }}</h3>
                            <div class="mt-4 bg-white/20 text-[9px] font-bold px-2 py-1 uppercase inline-block">Tasks Completed</div>
                        </div>
                        <Trophy class="w-10 h-10 opacity-20 group-hover:opacity-40 transition-opacity" />
                    </div>
                </div>

                <div class="relative overflow-hidden bg-orange-500 p-6 text-white rounded-none shadow-sm group transition-all">
                    <div class="relative z-10 flex justify-between items-start">
                        <div>
                            <p class="text-[10px] font-medium opacity-80 uppercase tracking-widest mb-1">Available Quizzes</p>
                            <h3 class="text-4xl font-semibold tabular-nums tracking-tight">{{ stats.available_quizzes || 0 }}</h3>
                            <div class="mt-4 bg-white/20 text-[9px] font-bold px-2 py-1 uppercase inline-block">Tests to complete</div>
                        </div>
                        <HelpCircle class="w-10 h-10 opacity-20 group-hover:opacity-40 transition-opacity" />
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-4">
                    <div class="px-2 py-1 flex justify-between items-center border-b border-slate-100 pb-4">
                        <h3 class="font-semibold text-slate-900 flex items-center gap-2 text-sm uppercase tracking-widest">
                            <GraduationCap class="w-4 h-4 text-indigo-600" /> Recent Study Materials
                        </h3>
                        <Link :href="route('materials.index')" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 transition-colors uppercase tracking-widest">
                            Library &rarr;
                        </Link>
                    </div>

                    <div class="space-y-3">
                        <div v-if="recentMaterials.length === 0" class="p-12 text-center text-slate-400 italic text-sm">
                            No study materials found.
                        </div>

                        <div
                            v-for="material in recentMaterials"
                            :key="material.id"
                            class="bg-white border border-slate-200 p-4 flex items-center justify-between group hover:border-indigo-200 transition-all shadow-sm"
                        >
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="w-10 h-10 bg-slate-50 flex items-center justify-center text-slate-400 group-hover:text-indigo-600 group-hover:bg-indigo-50 transition-all border border-slate-100">
                                    <FileText class="w-5 h-5" />
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-sm font-medium text-slate-900 truncate group-hover:text-indigo-600 transition-colors">
                                        {{ material.title }}
                                    </h4>
                                    <div class="flex items-center gap-2 mt-0.5 text-[10px] font-medium text-slate-400 uppercase tracking-wider">
                                        <span>{{ material.user.name }}</span>
                                        <span class="opacity-30">•</span>
                                        <span>{{ formatDate(material.created_at) }}</span>
                                    </div>
                                </div>
                            </div>

                            <a
                                :href="route('materials.download', material.id)"
                                class="p-2 text-slate-400 hover:text-indigo-600 transition-all"
                            >
                                <Download class="w-4 h-4" />
                            </a>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white border border-slate-200 p-6 shadow-sm">
                        <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-6 border-b border-slate-50 pb-2">Academic Hub</h3>
                        <div class="space-y-3">
                            <Link :href="route('forum.index')" class="flex items-center justify-between p-4 bg-slate-50 hover:bg-indigo-600 hover:text-white transition-all group border border-slate-100">
                                <span class="text-xs font-semibold uppercase tracking-widest">Discussion Forum</span>
                                <ArrowRight class="w-4 h-4 opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all" />
                            </Link>
                            <Link :href="route('activities.index')" class="flex items-center justify-between p-4 bg-slate-50 hover:bg-teal-600 hover:text-white transition-all group border border-slate-100">
                                <span class="text-xs font-semibold uppercase tracking-widest">Activities</span>
                                <ArrowRight class="w-4 h-4 opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all" />
                            </Link>
                        </div>
                    </div>

                    <div v-if="!isTeacher" class="bg-slate-900 p-6 text-white shadow-xl border-l-4 border-indigo-500 relative overflow-hidden">
                        <div class="relative z-10">
                            <h3 class="font-semibold text-lg mb-2 tracking-tight">Level Up Your Learning</h3>
                            <p class="text-[11px] text-slate-400 mb-5 leading-relaxed uppercase tracking-wider">
                                You have completed {{ stats.my_materials }} activities. Consistency leads to excellence.
                            </p>
                            <Link :href="route('activities.index')" class="inline-block bg-white text-slate-900 px-4 py-2 font-bold text-[10px] uppercase tracking-widest hover:bg-indigo-50 transition-colors">
                                Explore Activities &rarr;
                            </Link>
                        </div>
                        <TrendingUp class="absolute -right-4 -bottom-4 w-24 h-24 opacity-5" />
                    </div>

                    <div v-if="isTeacher" class="bg-slate-900 text-white p-6 shadow-md border border-slate-800">
                        <h3 class="font-semibold text-lg mb-2 text-teal-400 uppercase tracking-tight">Class Analytics</h3>
                        <p class="text-xs text-slate-400 mb-4 leading-relaxed font-medium">
                            Review and grade student submissions in the central reports hub.
                        </p>
                        <Link :href="route('reports.index')" class="text-[10px] font-bold text-white transition-all uppercase tracking-widest hover:text-teal-400 border-b border-teal-400/30 pb-1">
                            Go to Reports &rarr;
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
