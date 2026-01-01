<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3'; 
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { route } from 'ziggy-js';
import { 
    ArrowLeft, Calendar, FileText, UploadCloud, 
    CheckCircle, Trash2, Clock, Download, Users, Info, Pencil, X, AlertCircle
} from 'lucide-vue-next';
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps<{
    activity: {
        id: number;
        title: string;
        description: string;
        type: string; 
        file_path: string | null;
        file_name: string | null;
        due_date: string | null;
        created_at: string;
        my_submission: {
            id: number;
            created_at: string;
            file_path: string;
        } | null;
    };
    allSubmissions: Array<any>; 
    isTeacher: boolean;
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Classroom Activities', href: route('activities.index') },
    { title: props.activity.title, href: '#' },
];

// --- MODAL STATES ---
const isDeleteActivityModalOpen = ref(false);
const isDeleteSubmissionModalOpen = ref(false);
const isUnsubmitModalOpen = ref(false);
const targetSubmissionId = ref<number | null>(null);

// --- TIMER LOGIC ---
const canUnsubmit = ref(false);
const timeRemaining = ref('');
let timerInterval: any = null;

const checkTimeLimit = () => {
    if (!props.activity.my_submission) {
        canUnsubmit.value = false;
        return;
    }
    const submissionTime = new Date(props.activity.my_submission.created_at).getTime();
    const now = new Date().getTime();
    const diffInSeconds = (now - submissionTime) / 1000;
    const limitInSeconds = 120; 

    if (diffInSeconds < limitInSeconds) {
        canUnsubmit.value = true;
        const secondsLeft = Math.floor(limitInSeconds - diffInSeconds);
        timeRemaining.value = `${Math.floor(secondsLeft / 60)}:${(secondsLeft % 60).toString().padStart(2, '0')}`;
    } else {
        canUnsubmit.value = false;
        if (timerInterval) clearInterval(timerInterval);
    }
};

onMounted(() => {
    checkTimeLimit();
    timerInterval = setInterval(checkTimeLimit, 1000);
});

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
});

// --- ACTIONS ---

// Teacher deletes the whole activity
const confirmDeleteActivity = () => {
    router.delete(route('activities.destroy', props.activity.id));
};

// Teacher deletes a specific student submission
const openDeleteSubmissionModal = (id: number) => {
    targetSubmissionId.value = id;
    isDeleteSubmissionModalOpen.value = true;
};

const confirmDeleteSubmission = () => {
    if (targetSubmissionId.value) {
        router.delete(route('submissions.destroy', targetSubmissionId.value), {
            preserveScroll: true,
            onFinish: () => {
                isDeleteSubmissionModalOpen.value = false;
                targetSubmissionId.value = null;
            }
        });
    }
};

// Student unsubmits their work
const confirmUnsubmit = () => {
    router.delete(route('activities.unsubmit', props.activity.id), {
        preserveScroll: true,
        onFinish: () => isUnsubmitModalOpen.value = false
    });
};

const form = useForm({ file: null as File | null });

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files?.length) form.file = target.files[0];
};

const submitWork = () => {
    form.post(route('activities.submit', props.activity.id), {
        preserveScroll: true,
        forceFormData: true,
    });
};

const formatDate = (d: string) => new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
</script>

<template>
    <Head :title="activity.title" />

    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-slate-50 p-6 space-y-6">
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <Link :href="route('activities.index')">
                    <Button variant="ghost" class="text-slate-600 hover:text-teal-600 gap-2 p-0 hover:bg-transparent">
                        <ArrowLeft class="w-4 h-4" /> Back to Activities
                    </Button>
                </Link>

                <div v-if="isTeacher" class="flex items-center gap-2">
                    <Link :href="route('activities.edit', activity.id)">
                        <Button variant="outline" class="border-slate-200 text-slate-600 hover:text-teal-600 gap-2">
                            <Pencil class="w-4 h-4" /> Edit Activity
                        </Button>
                    </Link>
                    <Button variant="outline" @click="isDeleteActivityModalOpen = true" class="border-slate-200 text-slate-600 hover:text-red-600 hover:bg-red-50 gap-2">
                        <Trash2 class="w-4 h-4" /> Delete
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-slate-100 bg-white">
                            <div class="flex flex-wrap items-center justify-between gap-4">
                                <div class="space-y-1">
                                    <Badge variant="outline" class="uppercase text-[10px] tracking-wider bg-teal-50 text-teal-700 border-teal-100">
                                        {{ activity.type || 'Activity' }}
                                    </Badge>
                                    <h1 class="text-2xl font-bold text-slate-900 leading-tight">{{ activity.title }}</h1>
                                </div>
                                <div v-if="activity.due_date" class="flex items-center gap-2 px-3 py-1.5 bg-orange-50 text-orange-700 border border-orange-100 rounded-lg text-sm font-medium">
                                    <Calendar class="w-4 h-4" />
                                    Due: {{ formatDate(activity.due_date) }}
                                </div>
                            </div>
                        </div>

                        <div class="p-6">
                            <h3 class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">
                                <Info class="w-4 h-4" /> Instructions
                            </h3>
                            <div class="text-slate-600 whitespace-pre-wrap leading-relaxed">
                                {{ activity.description || 'No specific instructions provided.' }}
                            </div>

                            <div v-if="activity.file_path" class="mt-8 p-4 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 bg-white border border-slate-200 rounded flex items-center justify-center text-teal-600 shadow-sm">
                                        <FileText class="w-5 h-5" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-slate-900">Resource Attachment</p>
                                        <p class="text-xs text-slate-500 truncate">{{ activity.file_name || 'Material' }}</p>
                                    </div>
                                </div>
                                <a :href="route('activities.download', activity.id)">
                                    <Button size="sm" class="bg-teal-600 hover:bg-teal-700 text-white gap-2">
                                        <Download class="w-4 h-4" /> Download
                                    </Button>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div v-if="isTeacher" class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                            <h2 class="font-bold text-slate-900 flex items-center gap-2 uppercase tracking-widest text-xs">
                                <Users class="w-4 h-4 text-teal-600" /> Student Submissions
                            </h2>
                            <Badge class="bg-teal-100 text-teal-700 hover:bg-teal-100 border-none">{{ allSubmissions.length }} Turned In</Badge>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm text-slate-600">
                                <thead class="bg-slate-50/50 text-slate-400 text-[10px] font-bold uppercase tracking-widest border-b border-slate-100">
                                    <tr>
                                        <th class="px-6 py-4">Student</th>
                                        <th class="px-6 py-4">Submitted Date</th>
                                        <th class="px-6 py-4 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="sub in allSubmissions" :key="sub.id" class="hover:bg-slate-50/50 transition-colors group">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-slate-900">{{ sub.user.name }}</div>
                                            <div class="text-[10px] text-slate-400">{{ sub.user.email }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-xs font-medium">
                                            {{ new Date(sub.submitted_at).toLocaleString('en-GB', { dateStyle: 'medium', timeStyle: 'short' }) }}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex justify-end gap-1 opacity-40 group-hover:opacity-100 transition-opacity">
                                                <a :href="route('activities.downloadSubmission', sub.id)">
                                                    <Button variant="ghost" size="icon" class="text-slate-400 hover:text-teal-600"><Download class="w-4 h-4" /></Button>
                                                </a>
                                                <Button variant="ghost" size="icon" class="text-slate-400 hover:text-red-600" @click="openDeleteSubmissionModal(sub.id)">
                                                    <Trash2 class="w-4 h-4" />
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="allSubmissions.length === 0">
                                        <td colspan="3" class="px-6 py-12 text-center text-slate-400 italic text-xs">No students have submitted work yet.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div v-if="activity.type === 'Submission' && !isTeacher" class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                        <h2 class="text-sm font-bold text-slate-900 mb-6 flex items-center gap-2 uppercase tracking-widest">
                            <UploadCloud class="w-4 h-4 text-teal-600" /> Your Submission
                        </h2>

                        <div v-if="activity.my_submission" class="space-y-4">
                            <div class="bg-teal-50/50 border border-teal-100 rounded-xl p-6 text-center shadow-inner">
                                <CheckCircle class="w-10 h-10 text-teal-600 mx-auto mb-3" />
                                <h3 class="font-bold text-teal-900 text-sm">Work Turned In</h3>
                                <p class="text-[10px] text-teal-600 mt-1 uppercase tracking-tight">At: {{ new Date(activity.my_submission.created_at).toLocaleString() }}</p>
                            </div>

                            <div v-if="canUnsubmit" class="p-4 bg-orange-50 border border-orange-100 rounded-xl">
                                <p class="text-[10px] text-orange-700 font-bold uppercase tracking-wider mb-3 flex items-center gap-2">
                                    <Clock class="w-4 h-4" /> Undo window: {{ timeRemaining }}
                                </p>
                                <Button variant="destructive" size="sm" class="w-full gap-2 rounded-none uppercase text-[10px] font-bold" @click="isUnsubmitModalOpen = true">
                                    <Trash2 class="w-4 h-4" /> Remove Submission
                                </Button>
                            </div>
                        </div>

                        <form v-else @submit.prevent="submitWork" class="space-y-4">
                            <label for="file-upload" class="flex flex-col items-center justify-center w-full h-48 border-2 border-dashed border-slate-200 rounded-xl bg-slate-50 hover:bg-slate-100 hover:border-teal-300 transition-all cursor-pointer group">
                                <div class="flex flex-col items-center justify-center p-4">
                                    <UploadCloud class="w-10 h-10 text-slate-300 group-hover:text-teal-500 mb-3 transition-colors" />
                                    <p class="text-xs text-slate-500 font-bold uppercase tracking-widest" v-if="!form.file">Click to upload work</p>
                                    <div v-else class="text-center px-4">
                                        <p class="text-sm text-teal-600 font-bold truncate max-w-[180px]">{{ form.file.name }}</p>
                                        <p class="text-[10px] text-slate-400 mt-1">Click to replace file</p>
                                    </div>
                                </div>
                                <input id="file-upload" type="file" class="hidden" @change="handleFileChange" />
                            </label>
                            
                            <Button type="submit" :disabled="form.processing || !form.file" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold h-12 rounded-none text-[10px] uppercase tracking-widest shadow-lg shadow-teal-100">
                                {{ form.processing ? 'Uploading...' : 'Submit Now' }}
                            </Button>
                        </form>
                    </div>

                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-4">
                        <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-4">Metadata</h4>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-500 font-medium">Posted</span>
                                <span class="text-slate-900 font-bold">{{ formatDate(activity.created_at) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-500 font-medium">Status</span>
                                <Badge v-if="activity.my_submission" class="bg-green-100 text-green-700 rounded-none text-[9px] border-none font-bold uppercase">Completed</Badge>
                                <Badge v-else class="bg-slate-100 text-slate-600 rounded-none text-[9px] border-none font-bold uppercase">Pending</Badge>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="isDeleteActivityModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-none shadow-2xl w-full max-w-sm border border-slate-200 animate-in fade-in zoom-in duration-200 p-8">
                <h2 class="text-lg font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2">
                    <Trash2 class="w-5 h-5 text-red-500" /> Confirm Action
                </h2>
                <p class="mt-4 text-slate-600 text-sm leading-relaxed font-medium">Are you sure? This will delete the activity and all associated student work. This is permanent.</p>
                <div class="mt-8 flex justify-end gap-3">
                    <Button variant="ghost" @click="isDeleteActivityModalOpen = false" class="text-[10px] font-bold uppercase tracking-widest">Cancel</Button>
                    <Button @click="confirmDeleteActivity" class="bg-red-600 hover:bg-red-700 text-white text-[10px] font-bold uppercase tracking-widest px-8 shadow-md">Confirm Delete</Button>
                </div>
            </div>
        </div>

        <div v-if="isDeleteSubmissionModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-none shadow-2xl w-full max-w-sm border border-slate-200 animate-in fade-in zoom-in duration-200 p-8">
                <h2 class="text-lg font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2">
                    <AlertCircle class="w-5 h-5 text-red-500" /> Confirm
                </h2>
                <p class="mt-4 text-slate-600 text-sm leading-relaxed font-medium">Permanently delete this student's submission file?</p>
                <div class="mt-8 flex justify-end gap-3">
                    <Button variant="ghost" @click="isDeleteSubmissionModalOpen = false" class="text-[10px] font-bold uppercase tracking-widest">Cancel</Button>
                    <Button @click="confirmDeleteSubmission" class="bg-red-600 hover:bg-red-700 text-white text-[10px] font-bold uppercase tracking-widest px-8 shadow-md">Delete File</Button>
                </div>
            </div>
        </div>

        <div v-if="isUnsubmitModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-none shadow-2xl w-full max-w-sm border border-slate-200 animate-in fade-in zoom-in duration-200 p-8">
                <h2 class="text-lg font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2">
                    <Clock class="w-5 h-5 text-orange-500" /> Undo Submission
                </h2>
                <p class="mt-4 text-slate-600 text-sm leading-relaxed font-medium">Remove your work? You will have to re-upload to turn it in again.</p>
                <div class="mt-8 flex justify-end gap-3">
                    <Button variant="ghost" @click="isUnsubmitModalOpen = false" class="text-[10px] font-bold uppercase tracking-widest">Cancel</Button>
                    <Button @click="confirmUnsubmit" class="bg-orange-600 hover:bg-orange-700 text-white text-[10px] font-bold uppercase tracking-widest px-8 shadow-md">Confirm</Button>
                </div>
            </div>
        </div>

    </AppSidebarLayout>
</template>