<script setup lang="ts">
import { ref, watch } from 'vue';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import {
    FileText, CheckCircle, Trophy, Download, PencilLine, 
    Trash2, X, MessageSquare, AlertTriangle, Edit3
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import SystemNotification from '@/Components/SystemNotification.vue';

const props = defineProps<{
    student: { id: number; name: string; email: string; username: string };
    activities: any[];
    quizzes: any[];
    existingReport: any | null;
    stats: { quiz_avg: number; activities_completed: number; quizzes_taken: number };
}>();

const isModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const isEditConfirmModalOpen = ref(false); // New state for Confirm Update
const modalTitle = ref('Teacher Evaluation'); 

// Notification states
const showNotification = ref(false);
const notificationMessage = ref('');

const breadcrumbs = [
    { title: 'Performance Reports', href: route('reports.index') },
    { title: props.student.name, href: '#' },
];

const form = useForm({
    student_id: props.student.id,
    comments: props.existingReport ? props.existingReport.comments : '',
});

watch(() => props.existingReport, (newVal) => {
    form.comments = newVal ? newVal.comments : '';
});

const openAddModal = () => {
    modalTitle.value = 'Teacher Evaluation';
    isModalOpen.value = true;
};

const openEditModal = () => {
    modalTitle.value = 'Edit Teacher Evaluation'; //
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    isEditConfirmModalOpen.value = false;
    form.clearErrors();
};

const triggerNotification = (msg: string) => {
    notificationMessage.value = msg;
    showNotification.value = true;
    setTimeout(() => { showNotification.value = false; }, 3000);
};

// Step 1: Handle the initial "Save" click
const handleInitialSave = () => {
    if (modalTitle.value.includes('Edit')) {
        isEditConfirmModalOpen.value = true; // Show "Confirm Update" modal
    } else {
        submitRemark(); // Save directly if it's a new evaluation
    }
};

const submitRemark = () => {
    form.post(route('reports.remark.save'), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            triggerNotification('Teacher evaluation recorded.'); //
        }
    });
};

const confirmDelete = () => {
    if (!props.existingReport) return;
    form.delete(route('reports.remark.delete', props.existingReport.id), {
        preserveScroll: true,
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            triggerNotification('Evaluation removed successfully.');
        }
    });
};

const formatDate = (dateString: string) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
};

const handlePrint = () => { window.print(); };
</script>

<template>
    <Head :title="student.name + ' - Report'" />
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        
        <SystemNotification 
            :show="showNotification" 
            :message="notificationMessage" 
            @close="showNotification = false" 
        />

        <div class="min-h-screen bg-slate-50 p-6 space-y-6">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 flex flex-col md:flex-row items-center gap-6 text-center md:text-left">
                <div class="h-20 w-20 rounded-full bg-teal-600 text-white flex items-center justify-center text-3xl font-bold border-4 border-teal-50 shrink-0 uppercase mx-auto md:mx-0">
                    {{ student.name.charAt(0) }}
                </div>
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-slate-900">{{ student.name }}</h1>
                    <p class="text-slate-500">{{ student.email }}</p>
                </div>
                <div class="flex gap-3 no-print justify-center md:justify-end">
                    <Button @click="openAddModal" class="bg-teal-600 hover:bg-teal-700 text-white gap-2">
                        <PencilLine class="w-4 h-4" /> Evaluation
                    </Button>
                    <Button @click="handlePrint" variant="outline" class="gap-2 text-slate-600">
                        <Download class="w-4 h-4" /> Download
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
                    <div class="p-3 bg-blue-50 text-blue-600 rounded-lg"><FileText class="w-6 h-6" /></div>
                    <div>
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Activities Completed</p>
                        <p class="text-2xl font-bold text-slate-900">{{ stats.activities_completed }}</p>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
                    <div class="p-3 bg-teal-50 text-teal-600 rounded-lg"><Trophy class="w-6 h-6" /></div>
                    <div>
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Quizzes Taken</p>
                        <p class="text-2xl font-bold text-slate-900">{{ stats.quizzes_taken }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden text-slate-900">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2 uppercase text-[10px] tracking-wider">
                        <MessageSquare class="w-4 h-4 text-teal-600" /> Official Teacher Evaluation
                    </h3>
                    <div class="flex items-center gap-4 no-print">
                        <button v-if="existingReport" @click="openEditModal" class="text-slate-400 hover:text-teal-600 transition-colors">
                            <Edit3 class="w-4 h-4" />
                        </button>
                        <button v-if="existingReport" @click="isDeleteModalOpen = true" class="text-slate-400 hover:text-red-600 transition-colors">
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div v-if="existingReport" class="italic text-slate-700 font-medium text-lg leading-relaxed">
                        "{{ existingReport.comments }}"
                    </div>
                    <div v-else class="text-center py-4 text-slate-400 italic">No evaluation recorded.</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pb-12 text-slate-900">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b bg-slate-50 font-bold text-xs uppercase tracking-tight flex items-center gap-2">
                        <CheckCircle class="w-4 h-4 text-teal-600" /> Activities
                    </div>
                    <div class="divide-y divide-slate-100">
                        <div v-for="(act, index) in activities" :key="index" class="p-4 flex justify-between items-center">
                            <span class="font-medium">{{ act.title }}</span>
                            <span class="text-xs text-slate-400">{{ formatDate(act.completed_at) }}</span>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b bg-slate-50 font-bold text-xs uppercase tracking-tight flex items-center gap-2">
                        <Trophy class="w-4 h-4 text-teal-600" /> Quizzes (Best Attempts)
                    </div>
                    <div class="divide-y divide-slate-100">
                        <div v-for="(quiz, index) in quizzes" :key="index" class="p-4 flex justify-between items-center">
                            <span class="font-medium">{{ quiz.title }}</span>
                            <Badge class="bg-teal-50 text-teal-700 border-teal-200 font-bold">{{ quiz.score }}%</Badge>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div class="bg-white w-full max-w-lg rounded-xl shadow-2xl border border-slate-200 overflow-hidden text-slate-900">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 font-bold">
                    {{ modalTitle }}
                    <button @click="closeModal" class="text-slate-400"><X /></button>
                </div>
                <form @submit.prevent="handleInitialSave" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-2">Enter evaluation...</label>
                        <textarea 
                            v-model="form.comments" 
                            rows="5" 
                            :class="['w-full border rounded-lg p-4 outline-none transition-all text-slate-900', form.errors.comments ? 'border-red-500' : 'border-slate-200 focus:ring-2 focus:ring-teal-500']"
                            placeholder="Write your remarks here..."
                        ></textarea>
                        <p v-if="form.errors.comments" class="mt-2 text-sm text-red-500 font-medium">Please enter the evaluation again</p>
                    </div>
                    <div class="flex justify-end gap-3">
                        <Button type="button" variant="outline" @click="closeModal">Cancel</Button>
                        <Button type="submit" class="bg-teal-600 text-white" :disabled="form.processing">Save</Button>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="isEditConfirmModalOpen" class="fixed inset-0 z-[70] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div class="bg-white w-full max-w-sm rounded-lg shadow-2xl p-8 text-center">
                <div class="flex justify-center mb-6">
                    <div class="bg-amber-50 p-4 rounded-full">
                        <AlertTriangle class="w-10 h-10 text-amber-500" />
                    </div>
                </div>
                <h2 class="text-2xl font-bold text-slate-900 mb-2 uppercase tracking-tight">Confirm Update</h2>
                <p class="text-slate-500 mb-8 px-4 leading-relaxed text-sm">Proceed with saving these changes? This will update the material details for all students.</p>
                <div class="grid grid-cols-2 gap-3">
                    <button @click="isEditConfirmModalOpen = false" class="px-4 py-3 border border-slate-200 rounded text-slate-600 font-bold uppercase text-[10px] tracking-widest hover:bg-slate-50">No, Cancel</button>
                    <button @click="submitRemark" class="px-4 py-3 bg-[#0f172a] rounded text-white font-bold uppercase text-[10px] tracking-widest hover:bg-slate-800">Yes, Proceed</button>
                </div>
            </div>
        </div>

        <div v-if="isDeleteModalOpen" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 text-slate-900">
            <div class="bg-white w-full max-w-sm rounded-lg shadow-2xl p-8 text-center">
                <div class="flex justify-center mb-6"><div class="bg-red-50 p-4 rounded-full"><AlertTriangle class="w-10 h-10 text-red-600" /></div></div>
                <h2 class="text-2xl font-bold mb-2 uppercase tracking-tight">Confirm Deletion</h2>
                <p class="text-slate-500 mb-8 px-4 leading-relaxed text-sm">Are you sure you want to remove this resource permanently? This action cannot be undone.</p>
                <div class="grid grid-cols-2 gap-3">
                    <button @click="isDeleteModalOpen = false" class="px-4 py-3 border border-slate-200 rounded text-slate-600 font-bold uppercase text-[10px] tracking-widest hover:bg-slate-50">Cancel</button>
                    <button @click="confirmDelete" class="px-4 py-3 bg-red-600 rounded text-white font-bold uppercase text-[10px] tracking-widest hover:bg-red-700">Delete</button>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>