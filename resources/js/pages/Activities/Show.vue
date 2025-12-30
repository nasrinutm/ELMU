<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3'; 
import { route } from 'ziggy-js';
import { ArrowLeft, Calendar, FileText, UploadCloud, Save, CheckCircle, Trash2, Clock, Download, Users } from 'lucide-vue-next';
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps<{
    activity: {
        id: number;
        title: string;
        description: string;
        type: string; 
        file_path: string | null;
        due_date: string | null;
        created_at: string;
        my_submission: {
            id: number;
            created_at: string;
            file_path: string;
        } | null;
    };
    allSubmissions: Array<any>; // For Teachers
    isTeacher: boolean;
}>();

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
const removeSubmission = () => {
    if (confirm('Are you sure you want to remove your submission?')) {
        router.delete(route('activities.unsubmit', props.activity.id), { preserveScroll: true });
    }
};

const deleteStudentSubmission = (id: number) => {
    if (confirm('Teacher Action: Permanently delete this student submission?')) {
        router.delete(route('submissions.destroy', id), { preserveScroll: true });
    }
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
</script>

<template>
    <Head :title="activity.title" />

    <AppLayout :breadcrumbs="[
        { title: 'Activities', href: route('activities.index') }, 
        { title: activity.title, href: '#' }
    ]">
        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <Link :href="route('activities.index')" class="flex items-center text-blue-400 hover:text-white transition mb-4">
                    <ArrowLeft class="w-4 h-4 mr-2" /> Back to Activities
                </Link>

                <div class="bg-[#003366] rounded-lg shadow-xl border border-[#004080]">
                    <div class="p-6 md:p-8">
                        <div class="flex flex-col md:flex-row justify-between gap-4 mb-6">
                            <div>
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase border mb-3 inline-block bg-blue-900/50 text-blue-200 border-blue-700">
                                    {{ activity.type }}
                                </span>
                                <h1 class="text-3xl font-bold text-white">{{ activity.title }}</h1>
                            </div>
                            <div v-if="activity.due_date" class="flex items-center bg-red-900/20 border border-red-500/30 text-red-300 px-4 py-2 rounded-md h-fit">
                                <Calendar class="w-4 h-4 mr-2" />
                                <span class="font-bold text-sm">Due: {{ new Date(activity.due_date).toLocaleDateString() }}</span>
                            </div>
                        </div>

                        <div class="prose prose-invert max-w-none mb-8">
                            <h3 class="text-lg font-bold text-blue-200 mb-2 underline">Instructions</h3>
                            <p class="text-gray-300 whitespace-pre-wrap leading-relaxed">{{ activity.description }}</p>
                        </div>

                        <div v-if="activity.file_path" class="bg-[#002244] border border-[#004080] rounded-lg p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <FileText class="w-8 h-8 text-blue-300" />
                                <div>
                                    <p class="text-sm font-medium text-white">Attachment</p>
                                    <p class="text-xs text-blue-300">Teacher Resource</p>
                                </div>
                            </div>
                            <a :href="route('activities.download', activity.id)" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded text-xs font-bold transition">Download</a>
                        </div>
                    </div>
                </div>

                <div v-if="isTeacher" class="bg-[#003366] rounded-lg shadow-xl border border-[#004080]">
                    <div class="p-6 md:p-8">
                        <h2 class="text-xl font-bold text-white mb-6 flex items-center">
                            <Users class="w-6 h-6 mr-2 text-blue-400" />
                            Student Submissions ({{ allSubmissions.length }})
                        </h2>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm text-gray-300">
                                <thead class="bg-[#002244] text-white uppercase text-xs">
                                    <tr>
                                        <th class="px-4 py-3">Student</th>
                                        <th class="px-4 py-3">Submitted At</th>
                                        <th class="px-4 py-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#004080]">
                                    <tr v-for="sub in allSubmissions" :key="sub.id" class="hover:bg-[#002244]/50 transition">
                                        <td class="px-4 py-4">
                                            <div class="font-bold text-white">{{ sub.user.name }}</div>
                                            <div class="text-xs text-gray-400">{{ sub.user.email }}</div>
                                        </td>
                                        <td class="px-4 py-4">{{ new Date(sub.submitted_at).toLocaleString() }}</td>
                                        <td class="px-4 py-4 text-right">
                                            <div class="flex justify-end gap-2">
                                                <a :href="route('activities.downloadSubmission', sub.id)" class="p-2 bg-blue-900/50 hover:bg-blue-500 rounded text-blue-200 transition" title="Download">
                                                    <Download class="w-4 h-4" />
                                                </a>
                                                <button @click="deleteStudentSubmission(sub.id)" class="p-2 bg-red-900/50 hover:bg-red-500 rounded text-red-200 transition" title="Delete Submission">
                                                    <Trash2 class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="allSubmissions.length === 0">
                                        <td colspan="3" class="px-4 py-8 text-center text-gray-500 italic">No submissions yet.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div v-if="activity.type === 'Submission' && !isTeacher" class="bg-[#003366] rounded-lg shadow-xl border border-[#004080]">
                    <div class="p-6 md:p-8">
                        <h2 class="text-xl font-bold text-white mb-6 flex items-center">
                            <UploadCloud class="w-6 h-6 mr-2 text-green-400" />
                            Submit Your Work
                        </h2>

                        <div v-if="activity.my_submission" class="bg-green-900/20 border border-green-500/30 rounded-lg p-6 text-center">
                            <CheckCircle class="w-12 h-12 text-green-400 mx-auto mb-3" />
                            <h3 class="text-lg font-bold text-green-300">Successfully Submitted!</h3>
                            <p class="text-xs text-gray-400 mt-2">At: {{ new Date(activity.my_submission.created_at).toLocaleString() }}</p>

                            <div v-if="canUnsubmit" class="mt-6 pt-4 border-t border-green-500/20">
                                <p class="text-xs text-orange-300 mb-3 flex items-center justify-center">
                                    <Clock class="w-3 h-3 mr-1" /> Undo window: {{ timeRemaining }}
                                </p>
                                <button @click="removeSubmission" class="border border-red-500/50 text-red-300 hover:bg-red-500 hover:text-white px-4 py-2 rounded text-sm transition flex items-center mx-auto">
                                    <Trash2 class="w-4 h-4 mr-2" /> Remove Submission
                                </button>
                            </div>
                        </div>

                        <form v-else @submit.prevent="submitWork">
                            <label for="file-upload" class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-blue-400/30 rounded-lg bg-[#002244] hover:bg-[#002a55] transition cursor-pointer">
                                <UploadCloud class="w-10 h-10 text-blue-300 mb-2" />
                                <p class="text-sm text-blue-100 font-semibold" v-if="!form.file">Click to upload file</p>
                                <p class="text-sm text-green-400 font-bold" v-else>{{ form.file.name }}</p>
                                <input id="file-upload" type="file" class="hidden" @change="handleFileChange" />
                            </label>
                            
                            <div class="flex justify-end mt-4">
                                <button type="submit" :disabled="form.processing || !form.file" class="bg-green-600 hover:bg-green-500 disabled:opacity-50 text-white font-bold py-2 px-6 rounded transition">
                                    {{ form.processing ? 'Uploading...' : 'Turn In' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>