<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import {
    FileText,
    GraduationCap,
    Trophy,
    HelpCircle,
    Download,
    BookOpen,
    Users,
    ClipboardCheck,
    BarChart3,
    PlusCircle,
    UserPlus
} from 'lucide-vue-next';
import { route } from 'ziggy-js';

const props = defineProps<{
    stats: any;
    recentMaterials: any[];
}>();

const breadcrumbs = [{ title: 'Teacher Dashboard', href: '/dashboard' }];

const formatDate = (dateString: string) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric'
    });
};
</script>

<template>
    <Head title="Teacher Dashboard" />

    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-7xl mx-auto space-y-8 pb-10 px-4 sm:px-6 lg:px-8 font-sans antialiased text-slate-900">

            <div class="border-b border-slate-200 pb-6 flex justify-between items-end">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900 uppercase">Teacher Overview</h1>
                    <p class="text-sm text-slate-500 mt-1 font-medium italic">Empowering education through organized resources and student insights.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-indigo-600 p-8 text-white flex justify-between items-start rounded-none shadow-md">
                    <div>
                        <p class="text-[10px] font-bold opacity-80 uppercase tracking-[0.2em] mb-4">Material Resources</p>
                        <h3 class="text-4xl font-bold tabular-nums leading-none">{{ stats.materials || 0 }}</h3>
                        <p class="mt-4 text-[9px] font-bold uppercase opacity-60 tracking-widest flex items-center gap-1">
                            <BookOpen class="w-3 h-3" /> System modules
                        </p>
                    </div>
                    <FileText class="w-10 h-10 opacity-20" />
                </div>

                <div class="bg-emerald-500 p-8 text-white flex justify-between items-start rounded-none shadow-md">
                    <div>
                        <p class="text-[10px] font-bold opacity-80 uppercase tracking-[0.2em] mb-4">Total Students</p>
                        <h3 class="text-4xl font-bold tabular-nums leading-none">{{ stats.students || stats.total_students || 0 }}</h3>
                        <p class="mt-4 text-[9px] font-bold uppercase opacity-60 tracking-widest flex items-center gap-1">
                            <Users class="w-3 h-3" /> Enrolled learners
                        </p>
                    </div>
                    <GraduationCap class="w-10 h-10 opacity-20" />
                </div>

                <div class="bg-orange-500 p-8 text-white flex justify-between items-start rounded-none shadow-md">
                    <div>
                        <p class="text-[10px] font-bold opacity-80 uppercase tracking-[0.2em] mb-4">Total Quizzes</p>
                        <h3 class="text-4xl font-bold tabular-nums leading-none">{{ stats.available_quizzes || 0 }}</h3>
                        <p class="mt-4 text-[9px] font-bold uppercase opacity-60 tracking-widest flex items-center gap-1">
                            <Trophy class="w-3 h-3" /> Assessments
                        </p>
                    </div>
                    <HelpCircle class="w-10 h-10 opacity-20" />
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10 items-stretch font-sans">

                <div class="lg:col-span-2 flex flex-col bg-white border border-slate-200 shadow-sm rounded-none">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/30">
                        <h3 class="font-bold text-slate-900 uppercase text-[10px] tracking-[0.2em]">Recent Study Materials</h3>
                        <Link :href="route('materials.index')" class="text-[10px] text-action hover:underline font-bold uppercase tracking-widest">View All</Link>
                    </div>
                    <div class="flex-grow divide-y divide-slate-50">
                        <div v-for="material in recentMaterials" :key="material.id" class="p-5 flex items-center justify-between group hover:bg-slate-50 transition-all border-l-2 border-transparent hover:border-action">
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="w-10 h-10 bg-slate-50 flex items-center justify-center text-slate-400 border border-slate-100 rounded-none shadow-inner"><FileText class="w-4 h-4" /></div>
                                <div class="min-w-0">
                                    <h4 class="text-sm font-bold text-slate-900 uppercase tracking-tight group-hover:text-action truncate">{{ material.title }}</h4>
                                    <div class="flex items-center gap-2 mt-1 text-[9px] font-bold text-slate-400 uppercase tracking-[0.15em]">
                                        <span class="text-slate-600">{{ material.user?.name }}</span>
                                        <span class="opacity-30">•</span>
                                        <span>{{ formatDate(material.created_at) }}</span>
                                    </div>
                                </div>
                            </div>
                            <a :href="route('materials.download', material.id)" class="p-2 text-slate-300 hover:text-action transition-all"><Download class="w-5 h-5" /></a>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col">
                    <div class="flex-grow bg-white border border-slate-200 shadow-sm p-8 flex flex-col justify-between rounded-none">
                        <div>
                            <h3 class="font-bold text-[10px] uppercase tracking-[0.2em] text-slate-400 mb-8 flex items-center gap-2">
                                <ClipboardCheck class="w-4 h-4 text-action" /> Teaching Toolkit
                            </h3>
                            <div class="space-y-2">
                                <Link :href="route('students.index')" class="flex items-center justify-between p-3 border border-slate-50 hover:bg-slate-50 transition group rounded-none">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-600 group-hover:text-action">Manage Roster</span>
                                    <UserPlus class="w-4 h-4 text-slate-300 group-hover:text-action" />
                                </Link>
                                <Link :href="route('reports.index')" class="flex items-center justify-between p-3 border border-slate-50 hover:bg-slate-50 transition group rounded-none">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-600 group-hover:text-action">Class Performance</span>
                                    <BarChart3 class="w-4 h-4 text-slate-300 group-hover:text-action" />
                                </Link>
                                <Link :href="route('teacher.quiz.index')" class="flex items-center justify-between p-3 border border-slate-50 hover:bg-slate-50 transition group rounded-none">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-600 group-hover:text-action">Assessment List</span>
                                    <Trophy class="w-4 h-4 text-slate-300 group-hover:text-action" />
                                </Link>
                            </div>
                        </div>

                        <div class="mt-12 pt-8 border-t border-slate-50">
                            <p class="text-[9px] font-bold text-slate-400 uppercase mb-4 text-center tracking-widest leading-relaxed italic">Instructional Quick Actions</p>
                            <div class="space-y-3">
                                <Link :href="route('materials.create')" class="block w-full text-center bg-slate-900 text-white py-4 hover:bg-action transition text-[10px] font-bold uppercase tracking-[0.3em] shadow-lg rounded-none">
                                    <PlusCircle class="inline-block w-3 h-3 mr-2 mb-0.5" /> Add Material
                                </Link>
                                <Link :href="route('teacher.quiz.create')" class="block w-full text-center border border-slate-200 text-slate-600 py-3 hover:bg-slate-100 transition text-[9px] font-bold uppercase tracking-[0.2em] rounded-none">
                                    New Assessment
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppSidebarLayout>
</template>
