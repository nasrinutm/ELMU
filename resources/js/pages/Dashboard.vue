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
    TrendingUp,
    Users,
    ShieldCheck,
    UserCircle
} from 'lucide-vue-next';
import { route } from 'ziggy-js';
import { computed } from 'vue';

// --- TYPE DEFINITIONS ---
interface User {
    id: number;
    name: string;
    email: string;
    roles: any[];
}

// --- PROPS DEFINITION ---
const props = defineProps<{
    stats: any;
    recentMaterials?: any[];
    recentUsers?: any[];
}>();

const page = usePage();
const user = page.props.auth.user as unknown as User;

// --- ROBUST ROLE HELPERS ---
const roles = computed(() => (page.props as any).auth?.roles || []);

const isAdmin = computed(() => {
    return roles.value.some((r: any) =>
        typeof r === 'string' ? r === 'admin' : r.name === 'admin'
    );
});

const isTeacher = computed(() => {
    return roles.value.some((r: any) =>
        typeof r === 'string' ? r === 'teacher' : r.name === 'teacher'
    );
});

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
    <Head title="Dashboard" />

    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-7xl mx-auto space-y-8 pb-10 px-4 sm:px-0 font-sans font-normal antialiased">

            <div class="border-b border-slate-200 pb-6">
                <h1 class="text-2xl font-semibold tracking-tight text-slate-900 flex items-center gap-3 uppercase">
                    <LayoutDashboard class="w-7 h-7 text-action" />
                    {{ isAdmin ? 'System Overview' : 'Student Overview' }}
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    Welcome back, <span class="text-action font-semibold">{{ user.name }}</span>.
                    {{ isAdmin ? 'Monitor school performance and recent activity.' : 'Here is your current progress.' }}
                </p>
            </div>

            <div v-if="isAdmin" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="relative overflow-hidden shadow-lg bg-gradient-to-br from-fuchsia-600 to-purple-700 p-6 text-white rounded-sm">
                    <div class="relative z-10 flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium opacity-90 uppercase tracking-wider">Admins</p>
                            <h3 class="mt-2 text-4xl font-bold tabular-nums">{{ stats.admins }}</h3>
                            <p class="mt-2 text-xs opacity-75">System Managers</p>
                        </div>
                        <div class="p-2 bg-white/20 rounded-sm backdrop-blur-sm">
                            <ShieldCheck class="w-6 h-6 text-white" />
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden shadow-lg bg-gradient-to-br from-teal-500 to-emerald-600 p-6 text-white rounded-sm">
                    <div class="relative z-10 flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium opacity-90 uppercase tracking-wider">Teachers</p>
                            <h3 class="mt-2 text-4xl font-bold tabular-nums">{{ stats.teachers }}</h3>
                            <p class="mt-2 text-xs opacity-75">Instructors</p>
                        </div>
                        <div class="p-2 bg-white/20 rounded-sm backdrop-blur-sm">
                            <BookOpen class="w-6 h-6 text-white" />
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden shadow-lg bg-gradient-to-br from-orange-500 to-amber-600 p-6 text-white rounded-sm">
                    <div class="relative z-10 flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium opacity-90 uppercase tracking-wider">Students</p>
                            <h3 class="mt-2 text-4xl font-bold tabular-nums">{{ stats.students }}</h3>
                            <p class="mt-2 text-xs opacity-75">Learners</p>
                        </div>
                        <div class="p-2 bg-white/20 rounded-sm backdrop-blur-sm">
                            <GraduationCap class="w-6 h-6 text-white" />
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden shadow-lg bg-gradient-to-br from-blue-600 to-indigo-700 p-6 text-white rounded-sm">
                    <div class="relative z-10 flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium opacity-90 uppercase tracking-wider">Total Users</p>
                            <h3 class="mt-2 text-4xl font-bold tabular-nums">{{ stats.total_users }}</h3>
                            <p class="mt-2 text-xs opacity-75">All Accounts</p>
                        </div>
                        <div class="p-2 bg-white/20 rounded-sm backdrop-blur-sm">
                            <Users class="w-6 h-6 text-white" />
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="relative overflow-hidden bg-indigo-600 p-6 text-white shadow-sm flex justify-between items-start group">
                    <div>
                        <p class="text-[10px] font-medium opacity-80 uppercase tracking-widest mb-1">Learning Resources</p>
                        <h3 class="text-4xl font-semibold tabular-nums tracking-tight">{{ stats.materials }}</h3>
                        <div class="mt-4 bg-white/20 text-[9px] font-bold px-2 py-1 uppercase inline-block">Available Modules</div>
                    </div>
                    <BookOpen class="w-10 h-10 opacity-20" />
                </div>

                <div class="relative overflow-hidden bg-emerald-500 p-6 text-white shadow-sm flex justify-between items-start group">
                    <div>
                        <p class="text-[10px] font-medium opacity-80 uppercase tracking-widest mb-1">
                            {{ isTeacher ? 'Content Shared' : 'Activities Done' }}
                        </p>
                        <h3 class="text-4xl font-semibold tabular-nums tracking-tight">{{ stats.my_materials }}</h3>
                        <div class="mt-4 bg-white/20 text-[9px] font-bold px-2 py-1 uppercase inline-block">Tasks Completed</div>
                    </div>
                    <Trophy class="w-10 h-10 opacity-20" />
                </div>

                <div class="relative overflow-hidden bg-orange-500 p-6 text-white shadow-sm flex justify-between items-start group">
                    <div>
                        <p class="text-[10px] font-medium opacity-80 uppercase tracking-widest mb-1">Remaining Quizzes</p>
                        <h3 class="text-4xl font-semibold tabular-nums tracking-tight">{{ stats.available_quizzes }}</h3>
                        <div class="mt-4 bg-white/20 text-[9px] font-bold px-2 py-1 uppercase inline-block">Tests to complete</div>
                    </div>
                    <HelpCircle class="w-10 h-10 opacity-20" />
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-4">
                    <div class="px-2 py-1 flex justify-between items-center border-b border-slate-100 pb-4">
                        <h3 class="font-semibold text-slate-900 flex items-center gap-2 text-sm uppercase tracking-widest">
                            <template v-if="isAdmin">
                                <Users class="w-4 h-4 text-action" /> Recent Registrations
                            </template>
                            <template v-else>
                                <GraduationCap class="w-4 h-4 text-indigo-600" /> Recent Study Materials
                            </template>
                        </h3>
                        <Link :href="isAdmin ? route('users.index') : route('materials.index')" class="text-[10px] font-bold text-action hover:underline uppercase tracking-widest">
                            View All &rarr;
                        </Link>
                    </div>

                    <div class="space-y-3">
                        <template v-if="isAdmin">
                            <div v-for="u in recentUsers" :key="u.id" class="bg-white border border-slate-200 p-4 flex items-center justify-between shadow-sm hover:border-slate-300 transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 font-bold text-xs">
                                        {{ u.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-slate-900">{{ u.name }}</h4>
                                        <p class="text-[10px] text-slate-400 uppercase tracking-wider">{{ u.email }}</p>
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold text-slate-400 bg-slate-50 px-2 py-1 uppercase">{{ formatDate(u.created_at) }}</span>
                            </div>
                        </template>

                        <template v-else>
                            <div v-if="recentMaterials?.length === 0" class="p-12 text-center text-slate-400 italic text-sm">No study materials found.</div>
                            <div v-for="material in recentMaterials" :key="material.id" class="bg-white border border-slate-200 p-4 flex items-center justify-between group hover:border-indigo-200 transition-all shadow-sm">
                                <div class="flex items-center gap-4 min-w-0">
                                    <div class="w-10 h-10 bg-slate-50 flex items-center justify-center text-slate-400 border border-slate-100"><FileText class="w-5 h-5" /></div>
                                    <div class="min-w-0">
                                        <h4 class="text-sm font-medium text-slate-900 truncate group-hover:text-indigo-600 transition-colors">{{ material.title }}</h4>
                                        <div class="flex items-center gap-2 mt-0.5 text-[10px] font-medium text-slate-400 uppercase tracking-wider">
                                            <span>{{ material.user.name }}</span>
                                            <span class="opacity-30">•</span>
                                            <span>{{ formatDate(material.created_at) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <a :href="route('materials.download', material.id)" class="p-2 text-slate-400 hover:text-indigo-600 transition-all"><Download class="w-4 h-4" /></a>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="space-y-6">
                    <div v-if="isAdmin" class="bg-white border border-gray-200 shadow-sm p-6 rounded-none">
                        <h3 class="font-bold text-[10px] uppercase tracking-widest text-gray-900 mb-6 flex items-center gap-2">
                            <ShieldCheck class="w-4 h-4 text-green-500" /> System Status
                        </h3>
                        <div class="space-y-5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-600 font-medium uppercase tracking-wider">Server Status</span>
                                <span class="text-green-700 font-bold bg-green-50 px-2 py-1 uppercase tracking-tighter">Operational</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-600 font-medium uppercase tracking-wider">AI Chatbot</span>
                                <span class="text-blue-700 font-bold bg-blue-50 px-2 py-1 uppercase tracking-tighter">Active</span>
                            </div>
                        </div>

                        <div class="mt-8">
                            <p class="text-xs text-slate-400 uppercase tracking-wide font-bold mb-3">Quick Actions</p>
                            <Link :href="route('upload.create')" class="block w-full text-center bg-blue-600 text-white py-3 hover:bg-blue-700 transition text-sm font-semibold rounded-sm">
                                Update Knowledge Base
                            </Link>
                        </div>
                    </div>

                    <div class="bg-white border border-slate-200 p-6 shadow-sm rounded-none">
                        <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-6 border-b border-slate-50 pb-2">Academic Hub</h3>
                        <div class="space-y-3">
                            <Link :href="route('forum.index')" class="flex items-center justify-between p-4 bg-slate-50 hover:bg-action hover:text-white transition-all group border border-slate-100">
                                <span class="text-xs font-semibold uppercase tracking-widest">Discussion Forum</span>
                                <ArrowRight class="w-4 h-4 opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all" />
                            </Link>
                            <Link v-if="!isAdmin" :href="route('activities.index')" class="flex items-center justify-between p-4 bg-slate-50 hover:bg-teal-600 hover:text-white transition-all group border border-slate-100">
                                <span class="text-xs font-semibold uppercase tracking-widest">Activities</span>
                                <ArrowRight class="w-4 h-4 opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all" />
                            </Link>
                        </div>
                    </div>

                    <div v-if="!isAdmin" class="bg-slate-900 p-6 text-white shadow-xl border-l-4 border-indigo-500 relative overflow-hidden">
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
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
