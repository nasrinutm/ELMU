<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { route } from 'ziggy-js';
import {
    ArrowLeft, Calendar, FileText, UploadCloud,
    CheckCircle, Trash2, Clock, Download, Users, Info, Pencil, X, AlertCircle, CheckCircle2
} from 'lucide-vue-next';

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

// --- ROLE-BASED THEME LOGIC ---
const page = usePage();
const authUser = computed(() => (page.props as any).auth.user);

const themeActionClass = computed(() => {
    // Dynamic binding to your CSS variables (--action-color) based on user role
    return 'bg-[var(--action-color)] hover:bg-[var(--action-hover)] text-white border-none transition-all font-bold uppercase text-[10px] tracking-widest rounded-none shadow-md';
});

// --- NOTIFICATION & MODAL STATE ---
const flashSuccess = computed(() => (page.props as any).flash?.success);
const flashError = computed(() => (page.props as any).flash?.error);

const showSuccessNotification = ref(false);
const showDeleteActivityModal = ref(false);
const showDeleteSubmissionModal = ref(false);
const showUnsubmitModal = ref(false);
const targetSubId = ref<number | null>(null);

// --- TIMER LOGIC ---
const canUnsubmit = ref(false);
const timeRemaining = ref('');
let timerInterval: any = null;

const checkTimeLimit = () => {
    if (!props.activity.my_submission) { canUnsubmit.value = false; return; }
    const submissionTime = new Date(props.activity.my_submission.created_at).getTime();
    const now = new Date().getTime();
    const diffInSeconds = (now - submissionTime) / 1000;
    if (diffInSeconds < 120) {
        canUnsubmit.value = true;
        const secondsLeft = Math.floor(120 - diffInSeconds);
        timeRemaining.value = `${Math.floor(secondsLeft / 60)}:${(secondsLeft % 60).toString().padStart(2, '0')}`;
    } else { canUnsubmit.value = false; if (timerInterval) clearInterval(timerInterval); }
};

watch(flashSuccess, async (newVal) => {
    if (newVal) {
        showSuccessNotification.value = false;
        await nextTick();
        showSuccessNotification.value = true;
        setTimeout(() => { showSuccessNotification.value = false; }, 5000);
    }
}, { immediate: true });

onMounted(() => { checkTimeLimit(); timerInterval = setInterval(checkTimeLimit, 1000); });
onUnmounted(() => { if (timerInterval) clearInterval(timerInterval); });

// --- ACTIONS ---
const confirmDeleteActivity = () => { router.delete(route('activities.destroy', props.activity.id)); };
const confirmUnsubmit = () => { router.delete(route('activities.unsubmit', props.activity.id), { onFinish: () => showUnsubmitModal.value = false }); };

const openDelSubModal = (id: number) => { targetSubId.value = id; showDeleteSubmissionModal.value = true; };
const confirmDeleteSubmission = () => {
    if (targetSubId.value) {
        router.delete(route('submissions.destroy', targetSubId.value), { preserveScroll: true, onFinish: () => showDeleteSubmissionModal.value = false });
    }
};

const form = useForm({ file: null as File | null });
const submitWork = () => { form.post(route('activities.submit', props.activity.id), { preserveScroll: true, forceFormData: true }); };

const formatDate = (d: string) => new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
</script>

<template>
    <Head :title="activity.title" />

    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-slate-50 p-6 space-y-6 max-w-5xl mx-auto relative">

            <transition name="toast">
                <div v-if="showSuccessNotification" class="fixed top-10 right-10 z-[100] flex items-center gap-4 bg-slate-900 text-white p-5 shadow-2xl border-l-4 border-emerald-500 min-w-[350px]">
                    <div class="bg-emerald-500/20 p-2"><CheckCircle2 class="w-6 h-6 text-emerald-500" /></div>
                    <div class="flex-grow">
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-emerald-500">System Success</p>
                        <p class="text-sm font-medium">{{ flashSuccess }}</p>
                    </div>
                    <button @click="showSuccessNotification = false" class="text-slate-500 hover:text-white transition"><X class="w-4 h-4" /></button>
                </div>
            </transition>

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <Link :href="route('activities.index')">
                    <Button variant="ghost" class="text-slate-500 hover:text-slate-900 gap-2 p-0 h-auto font-bold uppercase text-[10px] tracking-widest">
                        <ArrowLeft class="w-4 h-4" /> Back to List
                    </Button>
                </Link>

                <div v-if="isTeacher" class="flex items-center gap-2">
                    <Link :href="route('activities.edit', activity.id)">
                        <Button variant="outline" class="border-slate-200 text-[10px] font-bold uppercase tracking-widest h-10 px-6 rounded-none hover:bg-slate-50">
                            <Pencil class="w-4 h-4 mr-2" /> Edit Details
                        </Button>
                    </Link>
                    <Button variant="outline" @click="showDeleteActivityModal = true" class="border-slate-200 text-red-600 hover:bg-red-50 text-[10px] font-bold uppercase tracking-widest h-10 px-6 rounded-none">
                        <Trash2 class="w-4 h-4 mr-2" /> Delete
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-none border border-slate-200 shadow-sm overflow-hidden border-t-4 border-t-slate-900">
                        <div class="p-8">
                            <div class="space-y-2 mb-8">
                                <Badge variant="outline" class="uppercase text-[9px] font-bold tracking-[0.2em] bg-slate-50 text-slate-500 border-slate-200 rounded-none">
                                    {{ activity.type }}
                                </Badge>
                                <h1 class="text-3xl font-bold text-slate-900 uppercase tracking-tight">{{ activity.title }}</h1>
                            </div>

                            <h3 class="flex items-center gap-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">
                                <Info class="w-4 h-4" /> Activity Instructions
                            </h3>
                            <div class="text-slate-600 whitespace-pre-wrap leading-relaxed font-medium text-sm mb-10">
                                {{ activity.description || 'No instructions provided.' }}
                            </div>

                            <div v-if="activity.file_path" class="p-6 bg-slate-50 border border-slate-200 rounded-none flex items-center justify-between group transition-all hover:bg-white">
                                <div class="flex items-center gap-4">
                                    <div class="h-12 w-12 bg-white border border-slate-200 flex items-center justify-center text-slate-400 shadow-sm">
                                        <FileText class="w-6 h-6" />
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-900 uppercase tracking-widest">Resource Attachment</p>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase mt-0.5 truncate max-w-[200px]">{{ activity.file_name }}</p>
                                    </div>
                                </div>
                                <a :href="route('activities.download', activity.id)" target="_blank">
                                    <Button size="sm" class="h-10 px-6" :class="themeActionClass">
                                        <Download class="w-4 h-4 mr-2" /> Download
                                    </Button>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div v-if="isTeacher" class="bg-white rounded-none border border-slate-200 shadow-sm overflow-hidden">
                        <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                            <h2 class="font-bold text-slate-900 flex items-center gap-2 uppercase tracking-widest text-[10px]">
                                <Users class="w-4 h-4 text-slate-500" /> Student Progress
                            </h2>
                            <Badge class="bg-slate-900 text-white text-[9px] font-bold uppercase px-3 py-1 rounded-none border-none">{{ allSubmissions.length }} Submissions</Badge>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-slate-50/50 text-slate-400 text-[9px] font-bold uppercase tracking-widest border-b border-slate-100">
                                    <tr>
                                        <th class="px-6 py-4">Student</th>
                                        <th class="px-6 py-4">Timestamp</th>
                                        <th class="px-6 py-4 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="sub in allSubmissions" :key="sub.id" class="group hover:bg-slate-50/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-slate-900 uppercase text-xs">{{ sub.user.name }}</div>
                                            <div class="text-[9px] text-slate-400 font-bold uppercase tracking-tighter">{{ sub.user.email }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase italic">
                                            {{ new Date(sub.submitted_at).toLocaleString('en-GB', { dateStyle: 'medium', timeStyle: 'short' }) }}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex justify-end gap-1">
                                                <a :href="route('activities.downloadSubmission', sub.id)" target="_blank">
                                                    <Button variant="ghost" size="icon" class="text-slate-300 hover:text-slate-900"><Download class="w-4 h-4" /></Button>
                                                </a>
                                                <Button variant="ghost" size="icon" class="text-slate-300 hover:text-red-600" @click="openDelSubModal(sub.id)">
                                                    <Trash2 class="w-4 h-4" />
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="allSubmissions.length === 0">
                                        <td colspan="3" class="px-6 py-12 text-center text-slate-400 italic text-[10px] uppercase tracking-widest">No work submitted yet.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div v-if="activity.type === 'Submission' && !isTeacher" class="bg-white rounded-none border border-slate-200 shadow-sm p-8 border-t-4 border-t-slate-900">
                        <h2 class="text-[10px] font-bold text-slate-900 mb-8 flex items-center gap-2 uppercase tracking-[0.2em]">
                            <UploadCloud class="w-4 h-4 text-slate-500" /> Hand In Work
                        </h2>

                        <div v-if="activity.my_submission" class="space-y-6">
                            <div class="bg-emerald-50 border border-emerald-100 rounded-none p-8 text-center">
                                <CheckCircle class="w-12 h-12 text-emerald-600 mx-auto mb-3" />
                                <h3 class="font-bold text-emerald-900 text-xs uppercase tracking-widest">Status: Submitted</h3>
                            </div>

                            <div v-if="canUnsubmit" class="p-6 bg-slate-50 border border-slate-200 rounded-none text-center">
                                <p class="text-[9px] text-slate-500 font-bold uppercase tracking-widest mb-4 flex items-center justify-center gap-2">
                                    <Clock class="w-4 h-4 text-[var(--action-color)]" /> Resubmit Window: {{ timeRemaining }}
                                </p>
                                <Button
                                    class="w-full h-11 text-[9px] font-bold uppercase tracking-widest rounded-none shadow-none"
                                    :class="themeActionClass"
                                    @click="showUnsubmitModal = true"
                                >
                                    Remove and Resubmit
                                </Button>
                            </div>
                        </div>

                        <form v-else @submit.prevent="submitWork" class="space-y-6">
                            <label for="file-upload" class="flex flex-col items-center justify-center w-full h-52 border border-dashed border-slate-300 rounded-none bg-slate-50 hover:bg-white hover:border-slate-900 transition-all cursor-pointer group">
                                <div class="text-center p-4">
                                    <UploadCloud class="w-10 h-10 text-slate-300 group-hover:text-slate-900 mx-auto mb-4 transition-colors" />
                                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest" v-if="!form.file">Select your file</p>
                                    <div v-else>
                                        <p class="text-[10px] text-slate-900 font-bold uppercase truncate max-w-[150px]">{{ form.file.name }}</p>
                                        <p class="text-[8px] text-slate-400 font-bold uppercase mt-1">Click to replace</p>
                                    </div>
                                </div>
                                <input id="file-upload" type="file" class="hidden" @change="(e) => form.file = (e.target as HTMLInputElement).files?.[0] || null" />
                            </label>
                            <Button type="submit" :disabled="form.processing || !form.file" class="w-full h-12" :class="themeActionClass">
                                {{ form.processing ? 'Uploading...' : 'Turn In Now' }}
                            </Button>
                        </form>
                    </div>

                    <div class="bg-white rounded-none border border-slate-200 shadow-sm p-6 space-y-5">
                        <h4 class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.2em]">Details</h4>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center text-[10px]">
                                <span class="text-slate-500 font-bold uppercase tracking-tighter">Due Date</span>
                                <span class="text-slate-900 font-bold italic">{{ activity.due_date ? formatDate(activity.due_date) : 'Open' }}</span>
                            </div>
                            <div class="flex justify-between items-center text-[10px]">
                                <span class="text-slate-500 font-bold uppercase tracking-tighter">Status</span>
                                <Badge v-if="activity.my_submission" class="bg-emerald-600 text-white rounded-none text-[8px] font-bold uppercase border-none">Success</Badge>
                                <Badge v-else class="bg-slate-200 text-slate-600 rounded-none text-[8px] font-bold uppercase border-none">Pending</Badge>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showDeleteActivityModal" class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white max-w-sm w-full p-8 border border-slate-200 rounded-none shadow-2xl">
                <h3 class="text-sm font-bold uppercase tracking-[0.2em] text-slate-900 mb-2">Delete Activity</h3>
                <p class="text-xs text-slate-500 font-medium mb-8 leading-relaxed italic">Are you sure? This will permanently delete the activity and all student work.</p>
                <div class="flex gap-3">
                    <Button variant="ghost" @click="showDeleteActivityModal = false" class="flex-1 text-[10px] font-bold uppercase tracking-widest">Cancel</Button>
                    <Button @click="confirmDeleteActivity" class="flex-1 bg-red-600 text-white text-[10px] font-bold uppercase hover:bg-red-700">Confirm</Button>
                </div>
            </div>
        </div>

        <div v-if="showDeleteSubmissionModal" class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white max-w-sm w-full p-8 border border-slate-200 rounded-none shadow-2xl">
                <h3 class="text-sm font-bold uppercase tracking-[0.2em] text-slate-900 mb-2">Remove Submission</h3>
                <p class="text-xs text-slate-500 font-medium mb-8 leading-relaxed italic">Delete this student's work file permanently from the server?</p>
                <div class="flex gap-3">
                    <Button variant="ghost" @click="showDeleteSubmissionModal = false" class="text-[10px] font-bold uppercase tracking-widest">Cancel</Button>
                    <Button @click="confirmDeleteSubmission" class="flex-1 bg-red-600 text-white text-[10px] font-bold uppercase tracking-widest hover:bg-red-700">Confirm</Button>
                </div>
            </div>
        </div>

        <div v-if="showUnsubmitModal" class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white max-w-sm w-full p-8 border border-slate-200 rounded-none shadow-2xl">
                <h3 class="text-sm font-bold uppercase tracking-[0.2em] text-slate-900 mb-2 flex items-center gap-2">
                    <AlertCircle class="w-4 h-4 text-orange-500" /> Confirm Removal
                </h3>
                <p class="text-xs text-slate-500 font-medium mb-8 leading-relaxed italic">Your submission will be deleted. You can then upload a new file.</p>
                <div class="flex gap-3">
                    <Button variant="ghost" @click="showUnsubmitModal = false" class="text-[10px] font-bold uppercase tracking-widest">Cancel</Button>
                    <Button @click="confirmUnsubmit" class="flex-1 text-[10px] font-bold uppercase tracking-widest" :class="themeActionClass">Confirm</Button>
                </div>
            </div>
        </div>

    </AppSidebarLayout>
</template>

<style scoped>
.toast-enter-active, .toast-leave-active { transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.toast-enter-from { transform: translateX(100%); opacity: 0; }
.toast-leave-to { transform: translateY(-20px); opacity: 0; }
</style>
