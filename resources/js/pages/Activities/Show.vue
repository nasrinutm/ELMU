<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ArrowLeft, Calendar, FileText, Upload, Save, CheckCircle, RefreshCw, Download, X } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    activity: any;
}>();

// 1. INITIALIZE STATE: 
// If a submission exists, start in "View" mode (true). If not, start in "Form" mode (false).
const hasPriorSubmission = !!props.activity.my_submission;
const isSubmitted = ref(hasPriorSubmission);

const form = useForm({
    file: null as File | null,
});

const submitWork = () => {
    form.post(route('activities.submit', props.activity.id), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            isSubmitted.value = true;
            form.reset();
        },
    });
};

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        form.file = target.files[0];
    }
};

// Switch to Edit Mode (Show Form)
const enableEditMode = () => {
    isSubmitted.value = false;
};

// Cancel Editing (Go back to Submission View)
const cancelEdit = () => {
    isSubmitted.value = true;
    form.reset();
};
</script>

<template>
    <Head :title="activity.title" />

    <AppLayout :breadcrumbs="[{ title: 'Activities', href: route('activities.index') }, { title: activity.title, href: '#' }]">
        <div class="py-12">
            <div class="w-full sm:px-6 lg:px-8">
                <div class="max-w-4xl mx-auto space-y-6">
                    
                    <Link :href="route('activities.index')" class="flex items-center text-blue-300 hover:text-white transition mb-4">
                        <ArrowLeft class="w-4 h-4 mr-1" /> Back to Activities
                    </Link>

                    <div class="bg-[#003366] rounded-lg shadow-xl border border-blue-800 overflow-hidden">
                        <div class="p-8">
                            <div class="flex justify-between items-start mb-6">
                                <div>
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-blue-900 text-blue-200 border border-blue-700 mb-2">
                                        {{ activity.type }}
                                    </span>
                                    <h1 class="text-3xl font-bold text-white">{{ activity.title }}</h1>
                                </div>
                                <div v-if="activity.due_date" class="flex items-center text-red-300 bg-red-900/30 px-3 py-1 rounded border border-red-800/50">
                                    <Calendar class="w-4 h-4 mr-2" />
                                    <span class="text-sm font-semibold">Due: {{ new Date(activity.due_date).toLocaleDateString() }}</span>
                                </div>
                            </div>
                            <div class="prose prose-invert max-w-none mb-8">
                                <h3 class="text-blue-200 font-bold text-lg mb-2">Instructions</h3>
                                <p class="text-gray-300 leading-relaxed whitespace-pre-line">{{ activity.description }}</p>
                            </div>
                             <div v-if="activity.file_path" class="bg-[#002244] p-4 rounded-lg border border-blue-900/50 flex items-center justify-between">
                                <div class="flex items-center text-blue-200">
                                    <FileText class="w-5 h-5 mr-3" />
                                    <span class="text-sm">Attached Resource provided by teacher</span>
                                </div>
                                <a :href="route('activities.download', activity.id)" class="text-white hover:text-blue-300 font-semibold text-sm underline">Download Material</a>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#003366] rounded-lg shadow-xl border border-blue-800 p-8">
                        
                        <div v-if="isSubmitted" class="text-center py-6 animate-in fade-in zoom-in duration-300">
                            <div class="bg-green-900/30 border border-green-500/50 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                                <CheckCircle class="w-8 h-8 text-green-400" />
                            </div>
                            <h2 class="text-2xl font-bold text-white mb-2">Submitted</h2>
                            <p class="text-blue-200 mb-6">You have submitted this assignment.</p>

                            <div class="bg-[#002855] border border-blue-700 rounded-lg p-4 max-w-md mx-auto mb-8 flex items-center justify-between group hover:border-blue-500 transition">
                                <div class="flex items-center overflow-hidden">
                                    <FileText class="w-8 h-8 text-blue-400 mr-3 flex-shrink-0" />
                                    <div class="text-left overflow-hidden">
                                        <p class="text-white font-semibold truncate text-sm">
                                            {{ activity.my_submission?.file_name || 'Attached File' }}
                                        </p>
                                        <p class="text-xs text-gray-400 mt-1">
                                            Submitted: {{ new Date(activity.my_submission?.submitted_at).toLocaleString() }}
                                        </p>
                                    </div>
                                </div>
                                <a :href="route('activities.submission.download', activity.id)" 
                                   class="ml-4 p-2 bg-blue-800 hover:bg-blue-600 text-white rounded-full transition shadow-md" 
                                   title="Download my file">
                                    <Download class="w-5 h-5" />
                                </a>
                            </div>
                            
                            <button 
                                @click="enableEditMode"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-md shadow transition flex items-center mx-auto"
                            >
                                <RefreshCw class="w-4 h-4 mr-2" />
                                Edit / Resubmit
                            </button>
                        </div>

                        <div v-else>
                            <div class="flex justify-between items-center mb-6">
                                <h2 class="text-xl font-bold text-white flex items-center">
                                    <Upload class="w-5 h-5 mr-2 text-green-400" />
                                    {{ activity.my_submission ? 'Resubmit Your Work' : 'Submit Your Work' }}
                                </h2>
                                
                                <button v-if="activity.my_submission" @click="cancelEdit" class="text-gray-400 hover:text-white transition flex items-center text-sm">
                                    <X class="w-4 h-4 mr-1" /> Cancel
                                </button>
                            </div>

                            <form @submit.prevent="submitWork" class="space-y-6">
                                <div class="border-2 border-dashed border-blue-700/50 rounded-lg p-8 flex flex-col items-center justify-center bg-[#002855] hover:bg-[#002f61] transition cursor-pointer relative">
                                    <input 
                                        type="file" 
                                        id="file-upload" 
                                        @change="handleFileChange" 
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" 
                                    />
                                    <div v-if="!form.file" class="text-center">
                                        <Upload class="w-10 h-10 text-blue-400 mx-auto mb-3" />
                                        <p class="text-blue-200 font-semibold">Click or drag {{ activity.my_submission ? 'new' : '' }} file to upload</p>
                                        <p class="text-blue-400 text-sm mt-1">Supported: PDF, DOCX, ZIP (Max 10MB)</p>
                                    </div>
                                    <div v-else class="text-center">
                                        <CheckCircle class="w-10 h-10 text-green-400 mx-auto mb-3" />
                                        <p class="text-white font-bold text-lg">{{ form.file.name }}</p>
                                        <p class="text-green-400 text-sm mt-1">Ready to submit</p>
                                    </div>
                                </div>
                                <p v-if="form.errors.file" class="text-red-400 text-sm">{{ form.errors.file }}</p>

                                <div class="flex justify-end">
                                    <button 
                                        type="submit" 
                                        :disabled="form.processing || !form.file"
                                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-md shadow-lg transition flex items-center disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <Save class="w-4 h-4 mr-2" />
                                        {{ form.processing ? 'Uploading...' : 'Submit Assignment' }}
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>