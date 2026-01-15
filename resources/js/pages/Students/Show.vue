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
    stats: {
        total: number;
        completed: number;
        pending: number;
        overdue: number;
    };
    completion_stats: {
        activity: number;
        evaluation: number;
        raw_activity_total: number;
        raw_quiz_total: number;
    };
}>();

const page = usePage();
const authUser = computed(() => (page.props as any).auth?.user);
const isOwnProfile = computed(() => authUser.value?.id === props.student?.id);

const flashSuccess = computed(() => (page.props as any).flash?.success);
const showNotification = ref(false);

watch(flashSuccess, async (newVal) => {
    if (newVal) {
        showNotification.value = false;
        await nextTick();
        showNotification.value = true;
        setTimeout(() => {
            showNotification.value = false;
            if ((page.props as any).flash) (page.props as any).flash.success = null;
        }, 5000);
    }
}, { immediate: true });

const sortedActivities = computed(() => {
    if (!props.activities) return [];
    return [...props.activities].sort((a, b) => {
        const priority: Record<string, number> = {
            'Overdue': 0, 'OVERDUE': 0,
            'Pending': 1, 'PENDING': 1,
            'Completed': 2, 'COMPLETED': 2
        };
        return (priority[a.status] ?? 3) - (priority[b.status] ?? 3);
    });
});

const statsData = computed(() => {
    return [
        { label: 'Total Activities', val: props.stats?.total ?? 0, color: 'bg-slate-300' },
        { label: 'Completed', val: props.stats?.completed ?? 0, color: 'bg-teal-500' },
        { label: 'Pending', val: props.stats?.pending ?? 0, color: 'bg-amber-500' },
        { label: 'Overdue', val: props.stats?.overdue ?? 0, color: 'bg-red-500' }
    ];
});

const breadcrumbs = computed(() => {
    const crumbs = [{ title: 'Dashboard', href: route('dashboard') }];
    if (isOwnProfile.value) {
        crumbs.push({ title: 'My Progress', href: '#' });
    } else {
        crumbs.push({ title: 'Student Progress', href: route('students.index') });
        crumbs.push({ title: props.student?.name || 'Student', href: '#' });
    }
    return crumbs;
});

const showModal = ref(false);
const showDeleteModal = ref(false);
const showConfirmEditModal = ref(false);
const isEditing = ref(false);
const editingId = ref<number | null>(null);
const itemToDeleteId = ref<number | null>(null);

const form = useForm({ title: '', score: '' });

const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEditModal = (activity: any) => {
    isEditing.value = true;
    editingId.value = activity.id;
    form.title = activity.title;
    form.score = activity.score === '-' || activity.score === null ? '' : String(activity.score).replace('%', '');
    form.clearErrors();
    showModal.value = true;
};

const closeModal = () => { showModal.value = false; form.reset(); form.clearErrors(); };

const validateAndSubmit = () => {
    form.clearErrors();
    let hasError = false;
    if (!form.title) { form.setError('title', 'Activity title is required.'); hasError = true; }
    if (form.score === '' || form.score === null) {
        form.setError('score', 'Score is required.');
        hasError = true;
    } else {
        const scoreNum = Number(form.score);
        if (scoreNum > 100) { form.setError('score', 'Score cannot be more than 100.'); hasError = true; }
        else if (scoreNum < 0) { form.setError('score', 'Score cannot be negative.'); hasError = true; }
    }
    if (hasError) return;
    if (isEditing.value) { showConfirmEditModal.value = true; }
    else { proceedWithSave(); }
};

const proceedWithSave = () => {
    if (isEditing.value && editingId.value) {
        form.put(route('students.activities.update', [props.student.id, editingId.value]), {
            onSuccess: () => { showConfirmEditModal.value = false; closeModal(); }
        });
    } else {
        form.post(route('students.activities.store', props.student.id), {
            onSuccess: () => closeModal()
        });
    }
};

const openDeleteModal = (id: number) => { itemToDeleteId.value = id; showDeleteModal.value = true; };
const confirmDelete = () => {
    if (itemToDeleteId.value) {
        router.delete(route('students.activities.destroy', [props.student.id, itemToDeleteId.value]), {
            onSuccess: () => { showDeleteModal.value = false; itemToDeleteId.value = null; }
        });
    }
};

const formatDate = (d: string | null) => d ? new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) : '-';

const isOverdue = (activity: any) => {
    if (!activity?.due_date || ['Completed', 'COMPLETED'].includes(activity.status)) return false;
    return new Date() > new Date(activity.due_date);
};
</script>

<template>
    <Head :title="student?.name || 'Student Profile'" />

    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-slate-50 p-4 md:p-8 space-y-8 relative font-sans max-w-7xl mx-auto w-full">

            <transition name="toast">
                <div v-if="showNotification" class="fixed top-10 right-10 z-[100] flex items-center gap-4 bg-slate-900 text-white p-5 shadow-2xl border-l-4 border-emerald-500 min-w-[350px]">
                    <div class="bg-emerald-500/20 p-2"><CheckCircle2 class="w-6 h-6 text-emerald-500" /></div>
                    <div class="flex-grow">
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-emerald-500">System Notification</p>
                        <p class="text-sm font-medium">{{ flashSuccess }}</p>
                    </div>
                    <button @click="showNotification = false" class="text-slate-500 hover:text-white transition"><X class="w-4 h-4" /></button>
                </div>
            </transition>

            <div class="bg-white rounded-none border border-slate-200 shadow-sm p-6 md:p-10 flex flex-col md:flex-row items-start md:items-center gap-8 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1 bg-teal-500/20"></div>
                <div class="h-20 w-20 md:h-24 md:w-24 rounded-none bg-teal-50 text-teal-600 flex items-center justify-center text-4xl font-bold border border-teal-100 shrink-0 shadow-inner">
                    {{ student?.name?.charAt(0).toUpperCase() || 'S' }}
                </div>
                <div class="flex-1">
                    <h1 class="text-2xl md:text-4xl font-bold text-slate-900 uppercase tracking-tight leading-none">{{ student?.name || 'Loading...' }}</h1>
                    <div class="flex flex-wrap gap-4 md:gap-6 text-[10px] font-bold uppercase tracking-widest text-slate-400 mt-4">
                        <span class="flex items-center gap-2 px-3 py-1 bg-slate-50 border border-slate-100"><Mail class="w-3.5 h-3.5 text-teal-500" /> {{ student?.email }}</span>
                        <span class="flex items-center gap-2 px-3 py-1 bg-slate-50 border border-slate-100"><GraduationCap class="w-3.5 h-3.5 text-teal-500" /> @{{ student?.username }}</span>
                    </div>
                </div>
                <Link v-if="!isOwnProfile" :href="route('students.index')">
                    <Button
                        class="text-[10px] font-bold uppercase tracking-widest px-6 py-5 rounded-none transition shadow-lg flex items-center gap-2 text-white border-none"
                        :class="{
                            'bg-teal-600 hover:bg-teal-700': authUser?.role === 'teacher',
                            'bg-indigo-600 hover:bg-indigo-700': authUser?.role === 'admin',
                            'bg-slate-900 hover:bg-slate-800': !authUser?.role
                        }"
                    >
                        <ArrowLeft class="w-4 h-4 text-white" /> Back to Student List
                    </Button>
                </Link>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                <div class="bg-white p-6 md:p-10 border border-slate-200 rounded-none shadow-sm flex items-center justify-between">
                    <div>
                        <h4 class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Activity Completion</h4>
                        <p class="text-xl md:text-2xl font-bold text-slate-900 mt-1">Assignments</p>
                    </div>
                    <div class="relative h-20 w-20 md:h-24 md:w-24">
                        <svg class="h-full w-full -rotate-90" viewBox="0 0 36 36">
                            <circle cx="18" cy="18" r="16" fill="none" class="stroke-slate-100" stroke-width="3" />
                            <circle cx="18" cy="18" r="16" fill="none" class="stroke-teal-500 transition-all duration-1000" stroke-width="3"
                                stroke-linecap="round" :stroke-dasharray="`${completion_stats?.activity || 0}, 100`" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center text-xs font-bold">{{ completion_stats?.activity || 0 }}%</div>
                    </div>
                </div>
                <div class="bg-white p-6 md:p-10 border border-slate-200 rounded-none shadow-sm flex items-center justify-between">
                    <div>
                        <h4 class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Quiz Completion</h4>
                        <p class="text-xl md:text-2xl font-bold text-slate-900 mt-1">Evaluations</p>
                    </div>
                    <div class="relative h-20 w-20 md:h-24 md:w-24">
                        <svg class="h-full w-full -rotate-90" viewBox="0 0 36 36">
                            <circle cx="18" cy="18" r="16" fill="none" class="stroke-slate-100" stroke-width="3" />
                            <circle cx="18" cy="18" r="16" fill="none" class="stroke-purple-500 transition-all duration-1000" stroke-width="3"
                                stroke-linecap="round" :stroke-dasharray="`${completion_stats?.evaluation || 0}, 100`" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center text-xs font-bold">{{ completion_stats?.evaluation || 0 }}%</div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
                <div v-for="stat in statsData" :key="stat.label" class="bg-white p-5 md:p-8 border border-slate-200 rounded-none shadow-sm flex flex-col relative overflow-hidden group">
                    <div :class="['absolute top-0 left-0 w-full h-1', stat.color]"></div>
                    <span class="text-[9px] md:text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ stat.label }}</span>
                    <span class="text-3xl md:text-4xl font-bold mt-2 tracking-tighter" :class="stat.label === 'Overdue' && stat.val > 0 ? 'text-red-600' : 'text-slate-900'">
                        {{ stat.val }}
                    </span>
                    <div v-if="stat.label === 'Total Activities'" class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-white/95 px-4 text-center">
                        <div class="space-y-1">
                            <span class="block text-[9px] font-black uppercase text-slate-600">Assigned: {{ completion_stats?.raw_activity_total || 0 }}</span>
                            <span class="block text-[9px] font-black uppercase text-slate-600">Quizzes: {{ completion_stats?.raw_quiz_total || 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-none border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-5 md:px-8 md:py-6 border-b border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-slate-50/40">
                    <div class="flex items-center gap-3">
                        <h3 class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-900">Performance Timeline</h3>
                    </div>
                    <Button v-if="!isOwnProfile" @click="openCreateModal" class="bg-teal-600 hover:bg-teal-700 text-white text-[10px] font-bold uppercase tracking-widest px-6 py-3 h-auto rounded-none">
                        <Plus class="h-4 w-4 mr-2" /> Add Manual Score
                    </Button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-[10px] uppercase text-slate-500 font-bold tracking-widest border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-5 md:px-8">Activity Details</th>
                                <th class="px-6 py-5 text-center">Status</th>
                                <th class="px-6 py-5 text-center">Score</th>
                                <th class="px-6 py-5 text-right">Date</th>
                                <th v-if="!isOwnProfile" class="px-6 py-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="sortedActivities.length === 0">
                                <td colspan="5" class="px-8 py-20 text-center opacity-40 italic text-slate-400">No activity records found for this student.</td>
                            </tr>
                            <tr v-for="activity in sortedActivities" :key="activity.id"
                                class="hover:bg-slate-50/80 transition-colors group border-l-4"
                                :class="['Completed', 'COMPLETED'].includes(activity.status) ? 'border-l-emerald-500' : (isOverdue(activity) ? 'border-l-red-500' : 'border-l-transparent')"
                            >
                                <td class="px-6 py-5 md:px-8">
                                    <div class="font-bold text-slate-900 uppercase tracking-tight flex items-center gap-3">
                                        <component :is="activity.type === 'Quiz' ? ClipboardList : FileText" class="w-4 h-4 text-slate-400 group-hover:text-teal-600" />
                                        <div v-if="isOwnProfile && !activity.is_manual && ['Pending', 'Overdue', 'PENDING', 'OVERDUE'].includes(activity.status)">
                                            <Link v-if="activity?.id" :href="route('activities.show', activity.id)" class="hover:text-teal-600 hover:underline">
                                                {{ activity.title }}
                                            </Link>
                                            <span v-else>{{ activity.title }}</span>
                                        </div>
                                        <div v-else>{{ activity.title }}</div>
                                    </div>
                                    <div class="flex items-center gap-2 mt-2 font-bold uppercase text-[8px] ml-7">
                                        <Badge class="rounded-none border-none py-0 px-2" :class="activity.type === 'Quiz' ? 'bg-purple-100 text-purple-700' : 'bg-teal-100 text-teal-700'">{{ activity.type }}</Badge>
                                        <div v-if="activity.due_date" class="flex items-center gap-1.5" :class="isOverdue(activity) ? 'text-red-600' : 'text-slate-400'">
                                            <Calendar class="w-3 h-3" /> Due: {{ formatDate(activity.due_date) }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <Badge class="text-[9px] font-bold uppercase tracking-widest px-3 py-1 rounded-none border-none" :class="['Completed', 'COMPLETED'].includes(activity.status) ? 'bg-emerald-500 text-white' : 'bg-amber-100 text-amber-700'">
                                        {{ activity.status }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-5 text-center font-bold text-slate-900 text-lg tracking-tighter">
                                    {{ activity.score }}<span v-if="activity.is_manual && activity.score && activity.score !== '-'">%</span>
                                </td>
                                <td class="px-6 py-5 text-right text-[11px] font-medium text-slate-400 uppercase tracking-widest">
                                    {{ activity.submitted_at ? formatDate(activity.submitted_at) : '-' }}
                                </td>
                                <td v-if="!isOwnProfile" class="px-6 py-5 text-right">
                                    <div v-if="activity.is_manual" class="flex items-center justify-end gap-1">
                                        <button @click="openEditModal(activity)" class="p-2 text-slate-400 hover:text-blue-600 transition-all"><Pencil class="h-4 w-4" /></button>
                                        <button @click="openDeleteModal(activity.id)" class="p-2 text-slate-400 hover:text-red-600 transition-all"><Trash2 class="h-4 w-4" /></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div v-if="showModal && !isOwnProfile" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
            <div class="w-full max-w-md bg-white p-10 shadow-2xl border-t-4 border-teal-600 rounded-none">
                <div class="mb-8 flex items-center justify-between">
                    <h3 class="text-sm font-bold uppercase tracking-[0.2em] text-slate-900">{{ isEditing ? 'Edit Entry' : 'New Score' }}</h3>
                    <button @click="closeModal" class="text-slate-400 hover:text-slate-600"><X class="h-5 w-5" /></button>
                </div>
                <form @submit.prevent="validateAndSubmit" class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-400 mb-2 tracking-widest">Activity Title</label>
                        <input v-model="form.title" type="text" class="w-full rounded-none border-slate-300 bg-slate-50 text-sm py-4 px-4 focus:border-teal-500 focus:ring-0" />
                        <div v-if="form.errors.title" class="text-red-400 text-sm mt-1">{{ form.errors.title }}</div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-400 mb-2 tracking-widest">Score (%)</label>
                        <input v-model="form.score" type="number" class="w-full rounded-none border-slate-300 bg-slate-50 text-sm py-4 px-4 focus:border-teal-500 focus:ring-0" />
                        <div v-if="form.errors.score" class="text-red-400 text-sm mt-1">{{ form.errors.score }}</div>
                    </div>
                    <div class="flex justify-end gap-4 pt-6 border-t border-slate-100">
                        <button type="button" @click="closeModal" class="text-[10px] font-bold uppercase text-slate-400 tracking-widest">Cancel</button>
                        <Button type="submit" class="bg-slate-900 text-white rounded-none uppercase text-[10px] tracking-widest py-4 px-10 hover:bg-teal-600" :disabled="form.processing">
                            Save Entry
                        </Button>
                    </div>
                </form>
            </div>
        </div>

    </AppSidebarLayout>
</template>
