<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import {
    FileText,
    Download,
    BookOpen,
    Trophy,
    Clock,
    ArrowRight,
    GraduationCap,
    TrendingUp,
    LayoutDashboard
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
        quiz_avg?: number; // Optional prop for professional version
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
        <div class="max-w-7xl mx-auto space-y-8 pb-10 px-4 sm:px-0">

            <div class="border-b border-slate-200 pb-6">
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 flex items-center gap-3">
                    <LayoutDashboard class="w-8 h-8 text-indigo-600" />
                    Student Overview
                </h1>
                <p class="text-slate-500 mt-2 font-medium">
                    Welcome back, <span class="text-indigo-600 font-bold">{{ user.name }}</span>. Here is your current learning progress.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="relative overflow-hidden shadow-md bg-gradient-to-br from-indigo-600 to-blue-700 p-6 text-white rounded-none border-0 group transition-all">
                    <div class="relative z-10 flex justify-between items-center">
                        <div>
                            <p class="text-xs font-bold opacity-80 uppercase tracking-widest mb-1">Learning Resources</p>
                            <h3 class="text-4xl font-black">{{ stats.materials }}</h3>
                            <p class="mt-2 text-[10px] font-medium bg-white/20 inline-block px-2 py-0.5 rounded-none uppercase tracking-tighter">Available Modules</p>
                        </div>
                        <BookOpen class="w-10 h-10 opacity-30 group-hover:scale-110 transition-transform" />
                    </div>
                </div>

                <div class="relative overflow-hidden shadow-md bg-gradient-to-br from-emerald-500 to-teal-600 p-6 text-white rounded-none border-0 group transition-all">
                    <div class="relative z-10 flex justify-between items-center">
                        <div>
                            <p class="text-xs font-bold opacity-80 uppercase tracking-widest mb-1">
                                {{ isTeacher ? 'Content Shared' : 'Activities Done' }}
                            </p>
                            <h3 class="text-4xl font-black">{{ stats.my_materials }}</h3>
                            <p class="mt-2 text-[10px] font-medium bg-white/20 inline-block px-2 py-0.5 rounded-none uppercase tracking-tighter">Tasks Completed</p>
                        </div>
                        <Trophy class="w-10 h-10 opacity-30 group-hover:scale-110 transition-transform" />
                    </div>
                </div>

                <div class="relative overflow-hidden shadow-md bg-gradient-to-br from-orange-500 to-amber-600 p-6 text-white rounded-none border-0 group transition-all">
                    <div class="relative z-10 flex justify-between items-center">
                        <div>
                            <p class="text-xs font-bold opacity-80 uppercase tracking-widest mb-1">Average Grade</p>
                            <h3 class="text-4xl font-black">{{ stats.quiz_avg || 0 }}%</h3>
                            <p class="mt-2 text-[10px] font-medium bg-white/20 inline-block px-2 py-0.5 rounded-none uppercase tracking-tighter">Current Standing</p>
                        </div>
                        <TrendingUp class="w-10 h-10 opacity-30 group-hover:scale-110 transition-transform" />
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 bg-white border border-slate-200 rounded-none shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                        <h3 class="font-black text-slate-900 flex items-center gap-2 text-sm uppercase tracking-wider">
                            <GraduationCap class="w-4 h-4 text-indigo-600" /> Recent Study Materials
                        </h3>
                        <Link :href="route('materials.index')" class="text-xs font-black text-indigo-600 hover:text-indigo-800 transition-colors uppercase">
                            Library &rarr;
                        </Link>
                    </div>

                    <div class="divide-y divide-slate-50">
                        <div v-if="recentMaterials.length === 0" class="p-12 text-center text-slate-400 italic">
                            No study materials found.
                        </div>

                        <div
                            v-for="material in recentMaterials"
                            :key="material.id"
                            class="p-5 flex items-center justify-between hover:bg-slate-50 transition group"
                        >
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="w-10 h-10 bg-slate-100 flex items-center justify-center border border-slate-200 text-slate-400 group-hover:text-indigo-600 group-hover:bg-indigo-50 transition-all">
                                    <FileText class="w-5 h-5" />
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-sm font-bold text-slate-900 truncate group-hover:text-indigo-600 transition-colors uppercase tracking-tight">
                                        {{ material.title }}
                                    </h4>
                                    <div class="flex items-center gap-2 mt-0.5 text-[10px] font-semibold text-slate-400 uppercase tracking-tighter">
                                        <span>{{ material.user.name }}</span>
                                        <span class="opacity-30">•</span>
                                        <span>{{ formatDate(material.created_at) }}</span>
                                    </div>
                                </div>
                            </div>

                            <a
                                :href="route('materials.download', material.id)"
                                class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all border border-transparent hover:border-indigo-100"
                                title="Download Module"
                            >
                                <Download class="w-4 h-4" />
                            </a>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white border border-slate-200 p-6 shadow-sm rounded-none">
                        <h3 class="font-black text-[10px] uppercase tracking-[0.2em] text-slate-400 mb-6 border-b border-slate-100 pb-2">Academic Hub</h3>
                        <div class="space-y-3">
                            <Link :href="route('forum.index')" class="flex items-center justify-between p-3 bg-slate-50 hover:bg-indigo-600 hover:text-white transition-all group font-bold text-xs uppercase tracking-widest border border-slate-100">
                                <span>Discussion Forum</span>
                                <ArrowRight class="w-4 h-4 opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all" />
                            </Link>
                            <Link :href="route('activities.index')" class="flex items-center justify-between p-3 bg-slate-50 hover:bg-emerald-600 hover:text-white transition-all group font-bold text-xs uppercase tracking-widest border border-slate-100">
                                <span>Tasks & Games</span>
                                <ArrowRight class="w-4 h-4 opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all" />
                            </Link>
                        </div>
                    </div>

                    <div v-if="!isTeacher" class="bg-indigo-800 p-8 text-white rounded-none shadow-xl border-l-4 border-indigo-400 relative overflow-hidden">
                        <div class="relative z-10">
                            <h3 class="font-black text-xl mb-3 tracking-tight italic">Level Up Your Learning</h3>
                            <p class="text-xs font-medium text-indigo-100 mb-6 leading-relaxed uppercase tracking-tighter">
                                You have successfully submitted {{ stats.my_materials }} activities. Consistency leads to excellence.
                            </p>
                            <Link :href="route('activities.index')" class="inline-block bg-white text-indigo-800 px-4 py-2 font-black text-[10px] uppercase tracking-widest hover:bg-slate-50 transition-colors shadow-lg">
                                Next Activity &rarr;
                            </Link>
                        </div>
                        <div class="absolute -right-4 -bottom-4 w-32 h-32 opacity-10 rotate-12">
                            <TrendingUp class="w-full h-full" />
                        </div>
                    </div>

                    <div v-if="isTeacher" class="bg-slate-900 text-white p-6 shadow-md rounded-none border border-slate-800">
                        <h3 class="font-black text-lg mb-2 text-teal-400 uppercase tracking-tight">Class Analytics</h3>
                        <p class="text-sm text-slate-400 mb-4 leading-relaxed font-medium">
                            Review and grade student submissions in the central reports hub.
                        </p>
                        <Link :href="route('reports.index')" class="text-[10px] font-black text-white transition-all uppercase tracking-widest hover:text-teal-400 border-b border-teal-400/30 pb-1">
                            Go to Reports &rarr;
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
