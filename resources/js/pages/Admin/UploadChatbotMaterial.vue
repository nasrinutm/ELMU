<script setup lang="ts">
import { useForm, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { route } from 'ziggy-js'; // Ensure route helper is available
import { FilePlus, UploadCloud, AlertCircle, ArrowLeft } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

// 1. Define Breadcrumbs matching your established style
const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'AI Knowledge Base', href: route('chatbot.details') },
    { title: 'Upload Material', href: route('upload.create') },
];

const form = useForm({
    title: '',
    file: null as File | null,
});

const uploadStatus = ref('');

const submit = () => {
    uploadStatus.value = 'Uploading to Google AI...';

    form.post(route('upload.store'), {
        onSuccess: () => {
            uploadStatus.value = 'Success! File is indexed.';
            form.reset();
        },
        onError: () => {
            uploadStatus.value = 'Failed. Please check file format or size.';
        }
    });
};
</script>

<template>
    <Head title="Upload Knowledge Base" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-8 antialiased font-sans font-normal text-gray-900">

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-200 pb-6">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900 uppercase">
                        Upload Knowledge Base
                    </h1>
                    <p class="text-sm text-slate-500 mt-1">
                        Add PDFs or text files to train your custom AI assistant.
                    </p>
                </div>

                <Link :href="route('chatbot.details')" class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-action transition-colors">
                    <ArrowLeft class="w-4 h-4" />
                    Back to Knowledge Base
                </Link>
            </div>

            <div class="flex justify-center w-full pt-4">
                <div class="w-full max-w-2xl bg-white border border-slate-200 rounded-none shadow-sm overflow-hidden">

                    <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                        <div class="p-2 bg-white rounded-sm border border-slate-200 shadow-sm text-action">
                            <UploadCloud class="w-5 h-5" />
                        </div>
                        <h2 class="text-sm font-bold text-gray-900 uppercase tracking-widest">
                            Document Details
                        </h2>
                    </div>

                    <div class="p-8 sm:p-12">
                        <form @submit.prevent="submit" class="space-y-10">

                            <div class="space-y-2">
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">
                                    Document Title
                                </label>
                                <input
                                    v-model="form.title"
                                    type="text"
                                    class="block w-full border border-slate-300 rounded-none h-12 px-4 bg-white text-sm text-gray-900 focus:outline-none focus:ring-1 focus:ring-action focus:border-action transition-all placeholder:text-slate-200"
                                    placeholder="e.g., Physics Chapter 1"
                                    required
                                />
                                <div v-if="form.errors.title" class="text-red-500 text-[10px] font-bold uppercase mt-2 tracking-tight flex items-center gap-1">
                                    <AlertCircle class="w-3 h-3" /> {{ form.errors.title }}
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">
                                    File Attachment (PDF, TXT, CSV)
                                </label>
                                <div class="relative group">
                                    <input
                                        type="file"
                                        @input="form.file = ($event.target as HTMLInputElement).files?.[0] || null"
                                        class="block w-full text-xs text-slate-500
                                            file:mr-4 file:py-3 file:px-6
                                            file:rounded-none file:border-0
                                            file:text-[10px] file:font-bold file:uppercase file:tracking-widest
                                            file:bg-slate-100 file:text-slate-600
                                            hover:file:bg-slate-200
                                            cursor-pointer border border-dashed border-slate-300 p-3 bg-slate-50/30 transition-colors hover:border-action/50"
                                        required
                                    />
                                </div>

                                <div v-if="form.progress" class="w-full bg-slate-100 rounded-none h-1.5 mt-4 overflow-hidden">
                                    <div class="bg-action h-full transition-all duration-300" :style="{ width: form.progress.percentage + '%' }"></div>
                                </div>

                                <div v-if="form.errors.file" class="text-red-500 text-[10px] font-bold uppercase mt-2 tracking-tight flex items-center gap-1">
                                    <AlertCircle class="w-3 h-3" /> {{ form.errors.file }}
                                </div>
                            </div>

                            <div v-if="uploadStatus"
                                 :class="uploadStatus.includes('Failed') ? 'bg-red-50 text-red-700 border-red-100' : 'bg-green-50 text-green-700 border-green-100'"
                                 class="p-4 rounded-none text-[10px] font-bold uppercase tracking-widest flex items-center gap-2 border shadow-sm">
                                <span :class="uploadStatus.includes('Success') ? 'animate-pulse' : ''" class="h-2 w-2 rounded-full bg-current"></span>
                                {{ uploadStatus }}
                            </div>

                            <div class="flex justify-end pt-6 border-t border-slate-100">
                                <Button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="bg-action hover:opacity-90 text-white font-bold shadow-md rounded-none transition-all text-[10px] uppercase tracking-widest px-10 py-7 h-auto"
                                >
                                    <FilePlus v-if="!form.processing" class="w-4 h-4 mr-2" />
                                    <span v-else class="h-4 w-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-2"></span>
                                    {{ form.processing ? 'Indexing to AI...' : 'Upload to Knowledge Base' }}
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
