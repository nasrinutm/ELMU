<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import {
    FileText,
    Download,
    BookOpen,
    Activity,
    Clock,
    ArrowRight,
    Trophy
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
    { title: 'Dashboard', href: '/dashboard' },
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
        <div class="max-w-7xl mx-auto space-y-8 pb-10">

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900">
                        Welcome back, {{ user.name }}!
                    </h1>
                    <p class="text-slate-500 mt-1">
                        {{ isTeacher ? 'Manage your classroom and materials here.' : 'View your progress and learning resources.' }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="relative overflow-hidden shadow-lg bg-gradient-to-br from-teal-500 to-emerald-600 p-6 text-white rounded-none border-0">
                    <div class="relative z-10 flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium opacity-90 uppercase tracking-wider">Total Materials</p>
                            <h3 class="mt-2 text-4xl font-bold">{{ stats.materials }}</h3>
                            <p class="mt-2 text-xs opacity-75">Available Resources</p>
                        </div>
                        <div class="p-2 bg-white/20 rounded-none backdrop-blur-sm">
                            <BookOpen class="w-6 h-6 text-white" />
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden shadow-lg bg-gradient-to-br from-blue-600 to-indigo-700 p-6 text-white rounded-none border-0">
                    <div class="relative z-10 flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium opacity-90 uppercase tracking-wider">
                                {{ isTeacher ? 'My Uploads' : 'Activities Done' }}
                            </p>
                            <h3 class="mt-2 text-4xl font-bold">{{ stats.my_materials }}</h3>
                            <p class="mt-2 text-xs opacity-75">
                                {{ isTeacher ? 'Files Shared' : 'Tasks Completed' }}
                            </p>
                        </div>
                        <div class="p-2 bg-white/20 rounded-none backdrop-blur-sm">
                            <FileText v-if="isTeacher" class="w-6 h-6 text-white" />
                            <Trophy v-else class="w-6 h-6 text-white" />
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden shadow-lg bg-gradient-to-br from-orange-500 to-amber-600 p-6 text-white rounded-none border-0">
                    <div class="relative z-10 flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium opacity-90 uppercase tracking-wider">Active Community</p>
                            <h3 class="mt-2 text-4xl font-bold">{{ stats.users }}</h3>
                            <p class="mt-2 text-xs opacity-75">Students & Teachers</p>
                        </div>
                        <div class="p-2 bg-white/20 rounded-none backdrop-blur-sm">
                            <Activity class="w-6 h-6 text-white" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 bg-white border border-slate-200 shadow-sm rounded-none overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/30">
                        <h3 class="font-bold text-slate-900 flex items-center gap-2">
                            <Clock class="w-5 h-5 text-blue-500" /> Recently Added
                        </h3>
                        <Link :href="route('materials.index')" class="text-sm text-blue-600 hover:text-blue-700 font-bold flex items-center gap-1">
                            View All <ArrowRight class="w-4 h-4" />
                        </Link>
                    </div>

                    <div class="divide-y divide-slate-50">
                        <div v-if="recentMaterials.length === 0" class="p-8 text-center text-slate-500 italic">
                            No materials have been uploaded yet.
                        </div>

                        <div
                            v-for="material in recentMaterials"
                            :key="material.id"
                            class="p-4 flex items-center justify-between hover:bg-slate-50 transition group"
                        >
                            <div class="flex items-center gap-4 overflow-hidden">
                                <div class="w-10 h-10 rounded-none bg-slate-100 text-slate-500 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-100 group-hover:text-blue-600">
                                    <FileText class="w-5 h-5" />
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-sm font-bold text-slate-900 truncate group-hover:text-blue-600 transition-colors">
                                        {{ material.title }}
                                    </h4>
                                    <div class="flex items-center gap-2 mt-1 text-[10px] text-slate-400 uppercase tracking-wide">
                                        <span>By {{ material.user.name }}</span>
                                        <span>•</span>
                                        <span>{{ formatDate(material.created_at) }}</span>
                                    </div>
                                </div>
                            </div>

                            <a
                                :href="route('materials.download', material.id)"
                                class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-none transition"
                                title="Download File"
                            >
                                <Download class="w-5 h-5" />
                            </a>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white border border-slate-200 shadow-sm p-6 rounded-none">
                        <h3 class="font-bold text-xs uppercase tracking-widest text-slate-400 mb-4">Quick Navigation</h3>
                        <div class="space-y-2">
                            <Link :href="route('forum.index')" class="flex items-center justify-between p-3 bg-slate-50 hover:bg-blue-50 hover:text-blue-700 transition group rounded-none">
                                <span class="text-sm font-medium">Discussion Forum</span>
                                <ArrowRight class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-all" />
                            </Link>
                            <Link :href="route('activities.index')" class="flex items-center justify-between p-3 bg-slate-50 hover:bg-orange-50 hover:text-orange-700 transition group rounded-none">
                                <span class="text-sm font-medium">Activities & Games</span>
                                <ArrowRight class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-all" />
                            </Link>
                        </div>
                    </div>

                    <div v-if="isTeacher" class="bg-slate-900 text-white p-6 shadow-md rounded-none border border-slate-800">
                        <h3 class="font-bold text-lg mb-2 text-teal-400">Teacher Insights</h3>
                        <p class="text-sm text-slate-400 mb-4 leading-relaxed">
                            Monitor student engagement and quiz performance in the reports section.
                        </p>
                        <Link :href="route('reports.index')" class="text-xs font-bold text-white transition uppercase tracking-widest hover:text-teal-400">
                            Go to Reports &rarr;
                        </Link>
                    </div>

                    <div v-else class="bg-gradient-to-br from-indigo-600 to-indigo-800 p-6 shadow-lg text-white rounded-none">
                        <h3 class="font-bold text-lg mb-2 flex items-center gap-2">
                             Keep it up!
                        </h3>
                        <p class="text-sm opacity-90 mb-4 leading-relaxed">
                            You've completed {{ stats.my_materials }} learning activities. Ready for your next challenge?
                        </p>
                        <Link :href="route('activities.index')" class="text-xs font-bold text-white transition uppercase tracking-widest hover:underline flex items-center gap-2">
                            Explore Activities <ArrowRight class="w-3 h-3" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
