<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, usePage, router, Link } from '@inertiajs/vue3';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Plus, Pencil, Trash2, X, Clock, Calendar,
    GraduationCap, Mail, ArrowLeft
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
        submitted_at: string | null;
        due_date: string | null;
        is_manual: boolean;
    }>;
}>();

const page = usePage();
const currentUser = computed(() => page.props.auth.user as any);
const isOwnProfile = computed(() => currentUser.value?.id === props.student.id);

// --- Breadcrumb Logic ---
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

// --- Modal Logic ---
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

// --- Helpers ---
const deleteActivity = (id: number) => {
    if (confirm('Delete this activity record?')) {
        router.delete(route('students.activities.destroy', [props.student.id, id]));
    }
};

const deleteStudentSubmission = (submissionId: number) => {
    if (confirm('Delete this student submission? This cannot be undone.')) {
        router.delete(route('submissions.destroy', submissionId), { preserveScroll: true });
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
        <div class="min-h-screen bg-slate-50 p-6 space-y-6">

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 flex flex-col md:flex-row items-start md:items-center gap-6">
                <div class="h-20 w-20 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center text-3xl font-bold border-4 border-white shadow-sm shrink-0">
                    {{ student.name.charAt(0).toUpperCase() }}
                </div>

                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-slate-900">{{ student.name }}</h1>
                    <div class="flex flex-wrap gap-4 mt-2 text-sm text-slate-500">
                        <span class="flex items-center gap-1.5">
                            <Mail class="w-4 h-4 text-slate-400" /> {{ student.email }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <GraduationCap class="w-4 h-4 text-slate-400" /> @{{ student.username }}
                        </span>
                    </div>
                </div>

                <Link v-if="!isOwnProfile" :href="route('students.index')">
                    <Button variant="outline" class="gap-2">
                        <ArrowLeft class="w-4 h-4" /> Back to Roster
                    </Button>
                </Link>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <Clock class="w-5 h-5 text-teal-600" />
                        Activity History & Grades
                    </h3>

                    <Button
                        v-if="!isOwnProfile"
                        @click="openCreateModal"
                        class="bg-teal-600 hover:bg-teal-700 text-white gap-2 shadow-sm"
                        size="sm"
                    >
                        <Plus class="h-4 w-4" /> Add Manual Score
                    </Button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-4">Activity Name</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Score</th>
                                <th class="px-6 py-4">Date</th>
                                <th v-if="!isOwnProfile" class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="activities.length === 0">
                                <td :colspan="!isOwnProfile ? 5 : 4" class="px-6 py-12 text-center text-slate-500">
                                    No activity records found for this student.
                                </td>
                            </tr>

                            <tr
                                v-for="activity in activities"
                                :key="activity.id"
                                class="hover:bg-slate-50 transition-colors group"
                            >
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-900">{{ activity.title }}</div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <Badge v-if="activity.is_manual" variant="outline" class="text-[10px] uppercase text-slate-500 border-slate-200 bg-slate-50">
                                            Manual Entry
                                        </Badge>
                                        <div v-if="activity.due_date" class="text-xs flex items-center gap-1" :class="isOverdue(activity) ? 'text-red-600 font-bold' : 'text-slate-400'">
                                            <Calendar class="w-3 h-3" />
                                            {{ isOverdue(activity) ? 'Overdue:' : 'Due:' }} {{ formatDate(activity.due_date) }}
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <Badge class="font-medium"
                                        :class="activity.status === 'Completed'
                                            ? 'bg-green-100 text-green-700 hover:bg-green-100 border-none'
                                            : 'bg-amber-100 text-amber-700 hover:bg-amber-100 border-none'"
                                    >
                                        {{ activity.status }}
                                    </Badge>
                                </td>

                                <td class="px-6 py-4 font-bold text-slate-700 text-base">
                                    {{ activity.score }}
                                </td>

                                <td class="px-6 py-4 text-slate-500">
                                    {{ activity.submitted_at ? formatDate(activity.submitted_at) : '-' }}
                                </td>

                                <td v-if="!isOwnProfile" class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button
                                            v-if="!activity.is_manual && activity.status === 'Completed' && activity.submission_id"
                                            @click="deleteStudentSubmission(activity.submission_id)"
                                            class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors"
                                            title="Delete Submission"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>

                                        <template v-if="activity.is_manual">
                                            <button @click="openEditModal(activity)" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded">
                                                <Pencil class="h-4 w-4" />
                                            </button>
                                            <button @click="deleteActivity(activity.id)" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded">
                                                <Trash2 class="h-4 w-4" />
                                            </button>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div v-if="showModal && !isOwnProfile" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-2xl border border-slate-200">
                <div class="mb-5 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900">{{ isEditing ? 'Edit Manual Score' : 'Add Manual Score' }}</h3>
                    <button @click="closeModal" class="text-slate-400 hover:text-slate-600 transition"><X class="h-5 w-5" /></button>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Activity Title</label>
                        <input
                            v-model="form.title"
                            type="text"
                            class="w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 transition"
                            placeholder="e.g. Pop Quiz 1"
                            required
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Score (0-100)</label>
                        <input
                            v-model="form.score"
                            type="number"
                            min="0"
                            max="100"
                            class="w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 transition"
                            placeholder="85"
                            required
                        />
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <Button type="button" variant="outline" @click="closeModal">Cancel</Button>
                        <Button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white" :disabled="form.processing">
                            {{ isEditing ? 'Update Score' : 'Save Score' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>
