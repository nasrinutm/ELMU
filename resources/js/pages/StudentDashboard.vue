<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import {
    FileText,
    Download,
    BookOpen,
    Trophy,
    HelpCircle,
    TrendingUp,
    CheckCircle,
    Clock
} from 'lucide-vue-next';
import { route } from 'ziggy-js';

// --- PROPS DEFINITION ---
// stats.my_materials will now correctly show your unique completion count
// stats.available_quizzes will show 0 if you have attempted all unique quizzes once
const props = defineProps<{
    stats: {
        materials: number;
        my_materials: number;
        available_quizzes: number;
    };
    recentMaterials?: any[];
}>();

const page = usePage();
const user = page.props.auth.user as any;

const breadcrumbs = [
    { title: 'Overview', href: '/dashboard' },
];

const formatDate = (dateString: string) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-GB', {
        month: 'short', day: 'numeric', year: 'numeric'
    });
};
</script>

<template>
    <Head title="Student Dashboard" />

    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-7xl mx-auto space-y-8 pb-10 px-4 sm:px-6 font-sans font-normal antialiased text-gray-900">

            <div class="border-b border-slate-200 pb-6 font-sans pt-6">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 uppercase font-sans">
                    Student Overview
                </h1>
                <p class="text-sm text-slate-500 mt-1 font-sans">
                    Welcome back, <span class="text-indigo-600 font-semibold">{{ user.name }}</span>.
                    Here is your current progress.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 font-sans">

                <div class="bg-indigo-600 p-8 text-white shadow-lg flex flex-col justify-between rounded-sm relative overflow-hidden h-44 group transition-all hover:shadow-indigo-200">
                    <div>
                        <p class="text-[10px] font-bold opacity-80 uppercase tracking-[0.2em] mb-2 font-sans">Material Resources</p>
                        <h3 class="text-5xl font-black tabular-nums tracking-tighter font-sans">{{ stats.materials }}</h3>
                    </div>
                    <div class="flex items-center gap-2 mt-4 text-[10px] font-black uppercase tracking-widest opacity-90 font-sans">
                        <BookOpen class="w-4 h-4" />
                        <span>System Modules</span>
                    </div>
                    <FileText class="absolute right-6 top-1/2 -translate-y-1/2 w-16 h-16 opacity-10 rotate-12 group-hover:rotate-0 transition-transform duration-500" />
                </div>

                <div class="bg-emerald-500 p-8 text-white shadow-lg flex flex-col justify-between rounded-sm relative overflow-hidden h-44 group transition-all hover:shadow-emerald-200">
                    <div>
                        <p class="text-[10px] font-bold opacity-80 uppercase tracking-[0.2em] mb-2 font-sans">Task Completion</p>
                        <h3 class="text-5xl font-black tabular-nums tracking-tighter font-sans">{{ stats.my_materials }}</h3>
                    </div>
                    <div class="flex items-center gap-2 mt-4 text-[10px] font-black uppercase tracking-widest opacity-90 font-sans">
                        <Trophy class="w-4 h-4" />
                        <span>Activities Done</span>
                    </div>
                    <CheckCircle class="absolute right-6 top-1/2 -translate-y-1/2 w-16 h-16 opacity-10 group-hover:scale-110 transition-transform duration-500" />
                </div>

                <div class="bg-orange-500 p-8 text-white shadow-lg flex flex-col justify-between rounded-sm relative overflow-hidden h-44 group transition-all hover:shadow-orange-200">
                    <div>
                        <p class="text-[10px] font-bold opacity-80 uppercase tracking-[0.2em] mb-2 font-sans">Pending Evaluations</p>
                        <h3 class="text-5xl font-black tabular-nums tracking-tighter font-sans">{{ stats.available_quizzes }}</h3>
                    </div>
                    <div class="flex items-center gap-2 mt-4 text-[10px] font-black uppercase tracking-widest opacity-90 font-sans">
                        <HelpCircle class="w-4 h-4" />
                        <span>Remaining Quizzes</span>
                    </div>
                    <TrendingUp class="absolute right-6 top-1/2 -translate-y-1/2 w-16 h-16 opacity-10 -rotate-12 group-hover:rotate-0 transition-transform duration-500" />
                </div>

            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch font-sans">

                <div class="lg:col-span-2 flex flex-col bg-white border border-slate-200 shadow-sm rounded-sm min-h-[450px]">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/30">
                        <h3 class="font-bold text-gray-900 flex items-center gap-2 uppercase text-xs tracking-[0.2em] font-sans">
                            <FileText class="w-4 h-4 text-indigo-600" />
                            Recent Study Materials
                        </h3>
                        <Link :href="route('materials.index')" class="text-[10px] text-indigo-600 hover:underline font-black uppercase tracking-widest font-sans">View All Materials &rarr;</Link>
                    </div>

                    <div class="flex-grow flex flex-col divide-y divide-slate-50 font-sans">
                        <div v-if="!recentMaterials || recentMaterials.length === 0" class="flex-grow flex flex-col items-center justify-center p-12 text-center opacity-40">
                             <FileText class="w-12 h-12 text-slate-300 mb-2" />
                             <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 font-sans">No study materials found.</p>
                        </div>

                        <div v-else v-for="material in recentMaterials" :key="material.id" class="p-5 flex items-center justify-between group hover:bg-slate-50 transition-all border-l-4 border-transparent hover:border-indigo-500">
                            <div class="flex items-center gap-4 min-w-0 font-sans">
                                <div class="w-12 h-12 bg-slate-50 flex items-center justify-center text-slate-400 border border-slate-200 rounded-none group-hover:bg-white group-hover:text-indigo-600 transition-colors">
                                    <FileText class="w-6 h-6" />
                                </div>
                                <div class="min-w-0 font-sans">
                                    <h4 class="text-base font-bold text-slate-900 truncate group-hover:text-indigo-600 transition-colors uppercase font-sans tracking-tight">
                                        {{ material.title }}
                                    </h4>
                                    <div class="flex items-center gap-3 mt-1.5 text-[9px] font-bold text-slate-400 uppercase tracking-widest font-sans">
                                        <span class="flex items-center gap-1"><TrendingUp class="w-3 h-3" /> {{ material.user?.name }}</span>
                                        <span class="opacity-30">•</span>
                                        <span class="flex items-center gap-1"><Clock class="w-3 h-3" /> {{ formatDate(material.created_at) }}</span>
                                    </div>
                                </div>
                            </div>
                            <a :href="route('materials.download', material.id)" class="p-3 text-slate-300 hover:text-indigo-600 hover:bg-white border border-transparent hover:border-slate-200 rounded-none transition-all shadow-sm">
                                <Download class="w-5 h-5" />
                            </a>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col space-y-6">
                    <div class="bg-slate-900 p-8 text-white shadow-2xl border-l-4 border-indigo-500 relative overflow-hidden rounded-sm font-sans group h-full flex flex-col justify-center">
                        <div class="relative z-10">
                            <p class="text-[10px] font-black text-emerald-400 mb-3 tracking-[0.3em] uppercase font-sans">Pro Tip</p>
                            <h3 class="font-bold text-2xl mb-3 tracking-tight uppercase font-sans leading-tight">Level Up Your Learning</h3>
                            <p class="text-[11px] text-slate-400 mb-8 leading-relaxed uppercase tracking-wider font-medium font-sans">
                                You have completed {{ stats.my_materials }} activities. Consistency leads to excellence.
                            </p>
                            <Link :href="route('activities.index')" class="flex items-center justify-between bg-white text-slate-900 px-6 py-4 font-black text-[10px] uppercase tracking-widest hover:bg-emerald-400 hover:text-white transition-all font-sans shadow-lg">
                                Explore Activities <span>&rarr;</span>
                            </Link>
                        </div>
                        <TrendingUp class="absolute -right-6 -bottom-6 w-32 h-32 opacity-5 rotate-12 group-hover:rotate-0 transition-transform duration-700" />
                    </div>
                </div>

            </div>
        </div>
    </AppSidebarLayout>
</template>

<style scoped>
/* Ensuring background icons have higher visibility on hover */
.group:hover .absolute {
    opacity: 0.15;
}
</style>
