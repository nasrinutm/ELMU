<script setup lang="ts">
import { ref, computed, watch, nextTick } from 'vue';
import { Head, useForm, usePage, router, Link } from '@inertiajs/vue3';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Plus, Pencil, Trash2, X, Clock, Calendar,
    GraduationCap, Mail, ArrowLeft, CheckCircle2,
    AlertTriangle, AlertCircle, PlusCircle, ClipboardList,
    FileText
} from 'lucide-vue-next';
import { route } from 'ziggy-js';

const props = defineProps<{
    student: {
        id: number;
        name: string;
        email: string;
        username: string;
    };
    activities: Array<{
        id: number;
        submission_id: number | null;
        title: string;
        type: string;
        status: string;
        score: string | number;
        percentage: number | null;
        submitted_at: string | null;
        due_date: string | null;
        is_manual: boolean;
    }>;
    // Data for circular charts and hover tooltips
    completion_stats: {
        activity: number;
        quiz: number;
        raw_activity_total: number;
        raw_quiz_total: number;
    };
}>();

const page = usePage();
const authUser = computed(() => (page.props as any).auth.user);
const isOwnProfile = computed(() => authUser.value?.id === props.student.id);

// --- NOTIFICATION STATE ---
const flashSuccess = computed(() => (page.props as any).flash?.success);
const showNotification = ref(false);

watch(flashSuccess, async (newVal) => {
    if (newVal) {
        showNotification.value = false;
        await nextTick();
        showNotification.value = true;
        setTimeout(() => {
            showNotification.value = false;
            (page.props as any).flash.success = null;
        }, 5000);
    }
}, { immediate: true });

// --- STATS LOGIC ---
const stats = computed(() => {
    // FIX: Total Activities now uses the raw unique count from props
    const totalUnique = props.completion_stats.raw_activity_total + props.completion_stats.raw_quiz_total;
    const completed = props.activities.filter(a => a.status === 'Completed').length;
    const pending = props.activities.filter(a => a.status === 'Pending').length;
    const overdue = props.activities.filter(a => {
        if (!a.due_date || a.status === 'Completed') return false;
        return new Date() > new Date(a.due_date);
    }).length;

    return [
        { label: 'Total Activities', val: totalUnique, color: 'bg-slate-300' },
        { label: 'Completed', val: completed, color: 'bg-teal-500' },
        { label: 'Pending', val: pending, color: 'bg-amber-500' },
        { label: 'Overdue', val: overdue, color: 'bg-red-500' }
    ];
});

const breadcrumbs = computed(() => {
    const crumbs = [{ title: 'Dashboard', href: route('dashboard') }];
    if (isOwnProfile.value) {
        crumbs.push({ title: 'My Progress', href: '#' });
    } else {
        crumbs.push({ title: 'Student Roster', href: route('students.index') });
        crumbs.push({ title: props.student.name, href: '#' });
    }
    return crumbs;
});

// --- MANUAL SCORE LOGIC ---
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref<number | null>(null);
const form = useForm({ title: '', score: '' });

const openCreateModal = () => { isEditing.value = false; form.reset(); showModal.value = true; };
const openEditModal = (activity: any) => {
    isEditing.value = true;
    editingId.value = activity.id;
    form.title = activity.title;
    form.score = activity.score === '-' ? '' : String(activity.score);
    showModal.value = true;
};
const closeModal = () => { showModal.value = false; form.reset(); };

const submit = () => {
    if (isEditing.value && editingId.value) {
        form.put(route('students.activities.update', [props.student.id, editingId.value]), { onSuccess: () => closeModal() });
    } else {
        form.post(route('students.activities.store', props.student.id), { onSuccess: () => closeModal() });
    }
};

const handleDeleteManualActivity = (id: number) => {
    if (window.confirm('Are you sure you want to delete this manual record?')) {
        router.delete(route('students.activities.destroy', [props.student.id, id]));
    }
};

const formatDate = (d: string) => d ? new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) : '-';

const isOverdue = (activity: any) => {
    if (!activity.due_date || activity.status === 'Completed') return false;
    return new Date() > new Date(activity.due_date);
};
</script>

<template>
    <Head :title="student.name" />

    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-slate-50 p-6 space-y-6 relative font-sans">

            <transition name="toast">
                <div v-if="showNotification" class="fixed top-10 right-10 z-[100] flex items-center gap-4 bg-slate-900 text-white p-5 shadow-2xl border-l-4 border-emerald-500 min-w-[350px]">
                    <div class="bg-emerald-500/20 p-2"><CheckCircle2 class="w-6 h-6 text-emerald-500" /></div>
                    <div class="flex-grow">
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-emerald-500 font-sans">System Notification</p>
                        <p class="text-sm font-medium font-sans">{{ flashSuccess }}</p>
                    </div>
                    <button @click="showNotification = false" class="text-slate-500 hover:text-white transition"><X class="w-4 h-4" /></button>
                </div>
            </transition>

            <div class="bg-white rounded-none border border-slate-200 shadow-sm p-8 flex flex-col md:flex-row items-start md:items-center gap-8 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1 bg-teal-500/20"></div>
                <div class="h-24 w-24 rounded-none bg-teal-50 text-teal-600 flex items-center justify-center text-4xl font-bold border border-teal-100 shrink-0">
                    {{ student.name.charAt(0).toUpperCase() }}
                </div>
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-slate-900 uppercase tracking-tight leading-none">{{ student.name }}</h1>
                    <div class="flex flex-wrap gap-6 text-[10px] font-bold uppercase tracking-widest text-slate-400 mt-4 font-sans">
                        <span class="flex items-center gap-2 px-3 py-1 bg-slate-50 border border-slate-100"><Mail class="w-3.5 h-3.5 text-teal-500" /> {{ student.email }}</span>
                        <span class="flex items-center gap-2 px-3 py-1 bg-slate-50 border border-slate-100"><GraduationCap class="w-3.5 h-3.5 text-teal-500" /> @{{ student.username }}</span>
                    </div>
                </div>

                <Link v-if="!isOwnProfile" :href="route('students.index')">
                    <Button
                        class="text-[10px] font-bold uppercase tracking-widest px-8 py-6 rounded-none transition shadow-lg flex items-center gap-2"
                        :class="{
                            'bg-teal-600 hover:bg-teal-700 text-white': authUser.role === 'teacher',
                            'bg-indigo-600 hover:bg-indigo-700 text-white': authUser.role === 'admin',
                            'bg-slate-900 hover:bg-slate-800 text-white': !authUser.role
                        }"
                    >
                        <ArrowLeft class="w-4 h-4" /> Back to Roster
                    </Button>
                </Link>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white p-8 border border-slate-200 rounded-none shadow-sm flex items-center justify-between group">
                    <div>
                        <h4 class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Activity Completion</h4>
                        <p class="text-2xl font-bold text-slate-900 mt-1">Assignments</p>
                    </div>
                    <div class="relative h-24 w-24">
                        <svg class="h-full w-full -rotate-90" viewBox="0 0 36 36">
                            <circle cx="18" cy="18" r="16" fill="none" class="stroke-slate-100" stroke-width="3" />
                            <circle cx="18" cy="18" r="16" fill="none" class="stroke-teal-500 transition-all duration-1000 ease-out" stroke-width="3"
                                stroke-linecap="round" :stroke-dasharray="`${completion_stats.activity}, 100`" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center text-xs font-bold">{{ completion_stats.activity }}%</div>
                    </div>
                </div>
                <div class="bg-white p-8 border border-slate-200 rounded-none shadow-sm flex items-center justify-between group">
                    <div>
                        <h4 class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Quiz Completion</h4>
                        <p class="text-2xl font-bold text-slate-900 mt-1">Evaluations</p>
                    </div>
                    <div class="relative h-24 w-24">
                        <svg class="h-full w-full -rotate-90" viewBox="0 0 36 36">
                            <circle cx="18" cy="18" r="16" fill="none" class="stroke-slate-100" stroke-width="3" />
                            <circle cx="18" cy="18" r="16" fill="none" class="stroke-purple-500 transition-all duration-1000 ease-out" stroke-width="3"
                                stroke-linecap="round" :stroke-dasharray="`${completion_stats.quiz}, 100`" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center text-xs font-bold">{{ completion_stats.quiz }}%</div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <div v-for="stat in stats" :key="stat.label"
                    class="bg-white p-6 border border-slate-200 rounded-none shadow-sm flex flex-col relative overflow-hidden group transition-all duration-300"
                    :class="stat.label === 'Total Activities' ? 'cursor-help' : ''"
                >
                    <div :class="['absolute top-0 left-0 w-full h-1', stat.color]"></div>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 font-sans">{{ stat.label }}</span>

                    <span class="text-4xl font-bold mt-2 tracking-tighter transition-opacity duration-300 font-sans"
                        :class="[
                            stat.label === 'Overdue' ? 'text-red-600' : 'text-slate-900',
                            stat.label === 'Total Activities' ? 'group-hover:opacity-0' : ''
                        ]"
                    >
                        {{ stat.val }}
                    </span>

                    <div v-if="stat.label === 'Total Activities'"
                        class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 px-4 bg-white/95"
                    >
                        <div class="text-center space-y-1.5">
                            <div class="flex items-center gap-2 justify-center">
                                <FileText class="w-3 h-3 text-teal-500" />
                                <span class="text-[10px] font-black uppercase text-slate-600 tracking-wider font-sans">Assigned: {{ completion_stats.raw_activity_total }}</span>
                            </div>
                            <div class="flex items-center gap-2 justify-center">
                                <ClipboardList class="w-3 h-3 text-purple-500" />
                                <span class="text-[10px] font-black uppercase text-slate-600 tracking-wider font-sans">Quizzes: {{ completion_stats.raw_quiz_total }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-none border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/40">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-teal-50 border border-teal-100"><ClipboardList class="w-5 h-5 text-teal-600" /></div>
                        <h3 class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-900 font-sans">Activity History & Performance</h3>
                    </div>
                    <Button v-if="!isOwnProfile" @click="openCreateModal" class="bg-teal-600 hover:bg-teal-700 text-white text-[10px] font-bold uppercase tracking-widest px-5 h-10 rounded-none shadow-md">
                        <Plus class="h-4 w-4 mr-2" /> Add Manual Score
                    </Button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm font-sans">
                        <thead class="bg-slate-50 text-[10px] uppercase text-slate-500 font-bold tracking-widest border-b border-slate-100">
                            <tr>
                                <th class="px-8 py-5">Activity Details</th>
                                <th class="px-8 py-5 text-center">Status</th>
                                <th class="px-8 py-5 text-center">Score</th>
                                <th class="px-8 py-5 text-right">Date</th>
                                <th v-if="!isOwnProfile" class="px-8 py-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="activities.length === 0">
                                <td colspan="5" class="px-8 py-20 text-center opacity-40">
                                    <PlusCircle class="w-12 h-12 text-slate-200 mx-auto mb-2" />
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 font-sans">No records found.</p>
                                </td>
                            </tr>
                            <tr v-for="activity in activities" :key="activity.id"
                                class="hover:bg-slate-50/80 transition-colors group border-l-4"
                                :class="activity.status === 'Completed' ? 'border-l-emerald-500' : 'border-l-transparent'"
                            >
                                <td class="px-8 py-5">
                                    <div class="font-bold text-slate-900 uppercase tracking-tight flex items-center gap-3 font-sans">
                                        <div class="p-1.5 bg-slate-100 text-slate-400 group-hover:bg-teal-50 group-hover:text-teal-600 transition-colors">
                                            <component :is="activity.type === 'Quiz' ? ClipboardList : FileText" class="w-4 h-4" />
                                        </div>
                                        {{ activity.title }}
                                    </div>
                                    <div class="flex items-center gap-3 mt-2 font-bold uppercase text-[8px] ml-9 font-sans">
                                        <Badge class="rounded-none px-2 py-0 border-none"
                                            :class="{'bg-purple-100 text-purple-700': activity.type === 'Quiz', 'bg-teal-100 text-teal-700': activity.type === 'Activity', 'bg-slate-100 text-slate-500': activity.type === 'Manual'}"
                                        >{{ activity.type }}</Badge>
                                        <div v-if="activity.due_date" class="flex items-center gap-1.5 px-2 py-0.5 bg-white border border-slate-100" :class="isOverdue(activity) ? 'text-red-600' : 'text-slate-400'">
                                            <Calendar class="w-3 h-3" /> Due: {{ formatDate(activity.due_date) }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <Badge class="text-[9px] font-bold uppercase tracking-widest px-4 py-1.5 rounded-none border-none shadow-sm font-sans" :class="activity.status === 'Completed' ? 'bg-emerald-500 text-white' : 'bg-amber-100 text-amber-700'">
                                        {{ activity.status }}
                                    </Badge>
                                </td>
                                <td class="px-8 py-5 text-center font-bold text-slate-900 text-lg tracking-tighter font-sans">{{ activity.score }}</td>
                                <td class="px-8 py-5 text-right text-[11px] font-medium text-slate-400 uppercase tracking-widest font-sans">
                                    {{ activity.submitted_at ? formatDate(activity.submitted_at) : '-' }}
                                </td>
                                <td v-if="!isOwnProfile" class="px-8 py-5 text-right font-sans">
                                    <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity font-sans">
                                        <template v-if="activity.is_manual">
                                            <button @click="openEditModal(activity)" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all"><Pencil class="h-4 w-4" /></button>
                                            <button @click="handleDeleteManualActivity(activity.id)" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 transition-all"><Trash2 class="h-4 w-4" /></button>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div v-if="showModal && !isOwnProfile" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 font-sans">
            <div class="w-full max-w-md bg-white p-10 shadow-2xl border-t-4 border-teal-600 rounded-none font-sans">
                <div class="mb-8 flex items-center justify-between font-sans">
                    <h3 class="text-sm font-bold uppercase tracking-[0.2em] text-slate-900 font-sans">{{ isEditing ? 'Edit Entry' : 'New Score' }}</h3>
                    <button @click="closeModal" class="text-slate-400 hover:text-slate-600 transition"><X class="h-5 w-5" /></button>
                </div>
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-400 mb-2 tracking-widest font-sans">Activity Title</label>
                        <input v-model="form.title" type="text" class="w-full rounded-none border-slate-200 bg-slate-50 text-sm focus:border-teal-500 focus:ring-0 py-4 px-4 font-medium transition-all" required />
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-400 mb-2 tracking-widest font-sans">Score / Grade</label>
                        <input v-model="form.score" type="text" class="w-full rounded-none border-slate-200 bg-slate-50 text-sm focus:border-teal-500 focus:ring-0 py-4 px-4 font-medium transition-all" required />
                    </div>
                    <div class="flex justify-end gap-4 pt-6 border-t border-slate-100 font-sans">
                        <button type="button" @click="closeModal" class="text-[10px] font-bold uppercase text-slate-400 tracking-widest hover:text-slate-600 transition-colors font-sans">Cancel</button>
                        <Button type="submit" class="bg-slate-900 text-white rounded-none uppercase text-[10px] tracking-widest py-4 px-10 hover:bg-teal-600 transition-colors shadow-lg font-sans" :disabled="form.processing">
                            Save Entry
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>

<style scoped>
.toast-enter-active, .toast-leave-active { transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.toast-enter-from { transform: translateX(100%); opacity: 0; }
.toast-leave-to { transform: translateY(-20px); opacity: 0; }
</style>
