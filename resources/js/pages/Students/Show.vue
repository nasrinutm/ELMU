<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Plus, Pencil, Trash2, X, ChevronRight } from 'lucide-vue-next';
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
        title: string;
        type: string;
        status: string;
        score: string | number;
        submitted_at: string | null;
        due_date: string | null; // Added due_date here
        is_manual: boolean;
    }>;
}>();

// --- Role & Permission Logic ---
const page = usePage();
const currentUser = computed(() => page.props.auth.user);
const isOwnProfile = computed(() => currentUser.value?.id === props.student.id);

// --- Modal Logic ---
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({ title: '', score: '' });

const openCreateModal = () => { isEditing.value = false; form.reset(); showModal.value = true; };
const openEditModal = (activity: any) => { isEditing.value = true; editingId.value = activity.id; form.title = activity.title; form.score = activity.score === '-' ? '' : String(activity.score); showModal.value = true; };
const closeModal = () => { showModal.value = false; form.reset(); };

const submit = () => {
    if (isEditing.value && editingId.value) {
        form.put(route('students.activities.update', [props.student.id, editingId.value]), { onSuccess: () => closeModal() });
    } else {
        form.post(route('students.activities.store', props.student.id), { onSuccess: () => closeModal() });
    }
};

const deleteActivity = (id: number) => {
    if (confirm('Are you sure you want to delete this activity record?')) {
        const deleteForm = useForm({});
        deleteForm.delete(route('students.activities.destroy', [props.student.id, id]));
    }
};

// --- Date Logic ---
const formatDate = (dateString: string) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

const isOverdue = (activity: any) => {
    if (!activity.due_date) return false;
    // If completed, it's not overdue anymore
    if (activity.status === 'Completed') return false;
    
    return new Date() > new Date(activity.due_date);
};
</script>

<template>
    <Head :title="`${student.name}'s Progress`" />

    <AppLayout>
        <Teleport to="body">
            <div class="dynamic-breadcrumbs fixed top-2 z-[9999] flex items-center h-16 pointer-events-none">
                <nav class="flex items-center text-[16px] font-normal pointer-events-auto">
                    <Link 
                        :href="route('dashboard')" 
                        class="text-black transition-colors duration-200 hover:text-[#FFD700] tracking-tight"
                    >
                        Dashboard
                    </Link>
                    
                    <ChevronRight class="mx-3 h-3 w-3 text-black stroke-[2px]" />
                    
                    <template v-if="!isOwnProfile">
                        <Link 
                            :href="route('students.index')" 
                            class="text-black transition-colors duration-200 hover:text-[#FFD700] tracking-tight"
                        >
                            Students
                        </Link>
                        <ChevronRight class="mx-3 h-3 w-3 text-black stroke-[2px]" />
                        <span class="text-[#FFD700] font-medium tracking-tight">
                            {{ student.name }}
                        </span>
                    </template>

                    <template v-else>
                        <span class="text-[#FFD700] font-medium tracking-tight">
                            My Progress
                        </span>
                    </template>
                </nav>
            </div>
        </Teleport>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-6 overflow-hidden bg-[#1a2c4e] shadow-sm sm:rounded-lg border border-gray-700">
                    <div class="p-6">
                        <div class="flex items-center gap-4">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-blue-900 text-2xl font-bold text-blue-200 border border-blue-700">
                                {{ student.name.charAt(0) }}
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">{{ student.name }}</h3>
                                <p class="text-sm text-gray-400">{{ student.email }}</p>
                                <p class="text-xs text-blue-400 uppercase tracking-wide mt-1 font-semibold">Student</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden bg-[#1a2c4e] shadow-sm sm:rounded-lg border border-gray-700">
                    <div class="flex items-center justify-between border-b border-gray-700 bg-[#243b61] px-6 py-4">
                        <h3 class="text-lg font-medium text-white">Activity Reports</h3>
                        <button v-if="!isOwnProfile" @click="openCreateModal" class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-blue-500 shadow-lg">
                            <Plus class="h-4 w-4" /> Add Activity
                        </button>
                    </div>
                    
                    <div class="relative overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-400">
                            <thead class="bg-[#243b61] text-xs uppercase text-gray-300">
                                <tr>
                                    <th class="px-6 py-3 font-bold">Activity Name</th>
                                    <th class="px-6 py-3 font-bold">Status</th>
                                    <th class="px-6 py-3 font-bold">Score</th>
                                    <th class="px-6 py-3 font-bold">Date Completed</th>
                                    <th v-if="!isOwnProfile" class="px-6 py-3 text-right font-bold">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="activity in activities" :key="activity.id" class="border-b border-gray-700 bg-[#1a2c4e] hover:bg-[#243b61] transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-white">
                                            {{ activity.title }} 
                                            <span v-if="activity.is_manual" class="ml-2 text-[10px] text-gray-500 italic uppercase bg-gray-800 px-1 rounded">Manual</span>
                                        </div>
                                        <div v-if="activity.due_date" class="mt-1 text-xs">
                                            <span :class="isOverdue(activity) ? 'text-red-400 font-bold' : 'text-gray-500'">
                                                {{ isOverdue(activity) ? 'Overdue:' : 'Due:' }} {{ formatDate(activity.due_date) }}
                                            </span>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium shadow-sm" :class="activity.status === 'Completed' ? 'bg-green-900/50 text-green-400 border border-green-800' : 'bg-yellow-900/50 text-yellow-400 border border-yellow-800'">
                                            {{ activity.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-white">{{ activity.score }}</td>
                                    <td class="px-6 py-4">{{ activity.submitted_at ? formatDate(activity.submitted_at) : '-' }}</td>
                                    <td v-if="!isOwnProfile" class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button @click="openEditModal(activity)" class="rounded p-1 text-gray-400 hover:bg-blue-900/50 hover:text-blue-400"><Pencil class="h-4 w-4" /></button>
                                            <button @click="deleteActivity(activity.id)" class="rounded p-1 text-gray-400 hover:bg-red-900/50 hover:text-red-400"><Trash2 class="h-4 w-4" /></button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="activities.length === 0"><td :colspan="!isOwnProfile ? 5 : 4" class="p-8 text-center text-gray-500 italic">No activities recorded.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showModal && !isOwnProfile" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-lg bg-[#1a2c4e] p-6 shadow-2xl border border-gray-700">
                <div class="mb-4 flex items-center justify-between"><h3 class="text-lg font-bold text-white">{{ isEditing ? 'Edit Activity' : 'Add New Activity' }}</h3><button @click="closeModal"><X class="h-5 w-5 text-gray-400" /></button></div>
                <form @submit.prevent="submit">
                    <div class="space-y-4 text-left">
                        <div><label class="mb-1 block text-sm font-medium text-gray-300">Activity Name</label><input v-model="form.title" type="text" class="w-full rounded-md border-gray-600 bg-[#0f1a30] text-white" required /></div>
                        <div><label class="mb-1 block text-sm font-medium text-gray-300">Marks Received</label><input v-model="form.score" type="number" min="0" max="100" class="w-full rounded-md border-gray-600 bg-[#0f1a30] text-white" required /></div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="closeModal" class="rounded-md border border-gray-600 px-4 py-2 text-sm text-gray-300">Cancel</button>
                        <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm text-white" :disabled="form.processing">{{ isEditing ? 'Update' : 'Save' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<style>
/* SMOOTH MOVEMENT:
   Using 'ease-in-out' matches the sidebar's default animation speed.
*/
.dynamic-breadcrumbs {
    left: 305px;
    transition: left 0.3s ease-in-out;
}

/* COLLAPSED STATE:
   115px provides safe clearance from the button.
*/
body:has([data-state="collapsed"]) .dynamic-breadcrumbs,
body:has(aside[data-state="collapsed"]) .dynamic-breadcrumbs {
    left: 115px !important;
}
</style>