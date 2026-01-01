<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import {
    FileText,
    Download,
    BookOpen,
    Trophy,
    HelpCircle,
    TrendingUp
} from 'lucide-vue-next';
import { route } from 'ziggy-js';

// --- PROPS DEFINITION ---
// Receives the student-specific data from the web.php route
const props = defineProps<{
    stats: any;
    recentMaterials?: any[];
}>();

const page = usePage();
const user = page.props.auth.user as any;

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
    <Head title="Student Dashboard" />

    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-7xl mx-auto space-y-8 pb-10 px-4 sm:px-0 font-sans font-normal antialiased text-gray-900">

            <div class="border-b border-slate-200 pb-6 font-sans">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 uppercase">
                    Student Overview
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    Welcome back, <span class="text-action font-semibold">{{ user.name }}</span>.
                    Here is your current progress.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 font-sans">
                <div class="bg-indigo-600 p-6 text-white shadow-sm flex justify-between items-start rounded-sm">
                    <div>
                        <p class="text-[10px] font-medium opacity-80 uppercase tracking-widest mb-1">Learning Resources</p>
                        <h3 class="text-4xl font-semibold tabular-nums tracking-tight">{{ stats.materials }}</h3>
                    </div>
                    <BookOpen class="w-10 h-10 opacity-20" />
                </div>

                <div class="bg-emerald-500 p-6 text-white shadow-sm flex justify-between items-start rounded-sm">
                    <div>
                        <p class="text-[10px] font-medium opacity-80 uppercase tracking-widest mb-1">Activities Done</p>
                        <h3 class="text-4xl font-semibold tabular-nums tracking-tight">{{ stats.my_materials }}</h3>
                    </div>
                    <Trophy class="w-10 h-10 opacity-20" />
                </div>

                <div class="bg-orange-500 p-6 text-white shadow-sm flex justify-between items-start rounded-sm">
                    <div>
                        <p class="text-[10px] font-medium opacity-80 uppercase tracking-widest mb-1">Remaining Quizzes</p>
                        <h3 class="text-4xl font-semibold tabular-nums tracking-tight">{{ stats.available_quizzes }}</h3>
                    </div>
                    <HelpCircle class="w-10 h-10 opacity-20" />
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start font-sans">

                <div class="lg:col-span-2 flex flex-col bg-white border border-slate-200 shadow-sm rounded-sm">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                        <h3 class="font-bold text-gray-900 flex items-center gap-2 uppercase text-sm tracking-widest">
                            Recent Study Materials
                        </h3>
                        <Link :href="route('materials.index')" class="text-xs text-blue-600 hover:underline font-bold uppercase">View All</Link>
                    </div>

                    <div class="flex-grow divide-y divide-slate-50">
                        <div v-if="recentMaterials?.length === 0" class="p-12 text-center text-slate-400 italic text-sm">No study materials found.</div>
                        <div v-for="material in recentMaterials" :key="material.id" class="p-4 flex items-center justify-between group hover:bg-slate-50 transition-all">
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="w-10 h-10 bg-slate-50 flex items-center justify-center text-slate-400 border border-slate-100"><FileText class="w-5 h-5" /></div>
                                <div class="min-w-0">
                                    <h4 class="text-sm font-medium text-slate-900 truncate group-hover:text-indigo-600 transition-colors font-sans">{{ material.title }}</h4>
                                    <div class="flex items-center gap-2 mt-0.5 text-[10px] font-medium text-slate-400 uppercase tracking-wider">
                                        <span>{{ material.user?.name }}</span>
                                        <span class="opacity-30">•</span>
                                        <span>{{ formatDate(material.created_at) }}</span>
                                    </div>
                                </div>
                            </div>
                            <a :href="route('materials.download', material.id)" class="p-2 text-slate-400 hover:text-indigo-600 transition-all"><Download class="w-4 h-4" /></a>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col space-y-6">
                    <div class="bg-slate-900 p-6 text-white shadow-xl border-l-4 border-indigo-500 relative overflow-hidden rounded-sm font-sans">
                        <div class="relative z-10 font-normal">
                            <h3 class="font-semibold text-lg mb-2 tracking-tight">Level Up Your Learning</h3>
                            <p class="text-[11px] text-slate-400 mb-5 leading-relaxed uppercase tracking-wider">You have completed {{ stats.my_materials }} activities. Consistency leads to excellence.</p>
                            <Link :href="route('activities.index')" class="inline-block bg-white text-slate-900 px-4 py-2 font-bold text-[10px] uppercase tracking-widest hover:bg-indigo-50 transition-colors">Explore Activities &rarr;</Link>
                        </div>
                        <TrendingUp class="absolute -right-4 -bottom-4 w-24 h-24 opacity-5" />
                    </div>
                </div>

            </div>
        </div>
    </AppSidebarLayout>
</template>
