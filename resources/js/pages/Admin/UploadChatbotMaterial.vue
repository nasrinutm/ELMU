<script setup>
import { useForm, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

// 1. Define Breadcrumbs (matches Dashboard style)
const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'AI Details', href: route('chatbot.details') },
    { title: 'Upload Material', href: route('upload.create'),},
];

const form = useForm({
    title: '',
    file: null,
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
            uploadStatus.value = 'Failed. Check logs.';
        }
    });
};
</script>

<template>
    <Head title="Upload Knowledge Base" />

    <AppLayout :breadcrumbs="breadcrumbs">
        
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            
            <div class="w-full max-w-2xl mx-auto bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                
                <div class="p-6 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-bold text-gray-900">
                        Upload Knowledge Base
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Upload PDFs or text files to train the AI assistant.
                    </p>
                </div>
                
                <div class="p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Document Title
                            </label>
                            <input 
                                v-model="form.title" 
                                type="text" 
                                class="mt-1 block w-full border border-gray-300 rounded-md p-2 bg-white text-gray-900  focus:ring-blue-500 focus:border-blue-500"
                                placeholder="e.g., Physics Chapter 1"
                            />
                            <div v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                File (PDF, TXT, CSV)
                            </label>
                            <input 
                                type="file" 
                                @input="form.file = $event.target.files[0]"
                                class="mt-2 block w-full text-sm text-gray-500 dark:text-gray-400
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-full file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-blue-50 file:text-blue-700
                                    hover:file:bg-blue-100
                                    cursor-pointer"
                            />
                            
                            <div v-if="form.progress" class="w-full bg-gray-200 rounded-full h-2.5 mt-3">
                                <div class="bg-blue-600 h-2.5 rounded-full" :style="{ width: form.progress.percentage + '%' }"></div>
                            </div>
                            
                            <div v-if="form.errors.file" class="text-red-500 text-xs mt-1">{{ form.errors.file }}</div>
                        </div>

                        <div v-if="uploadStatus" class="p-3 rounded-md bg-blue-50 text-blue-700 text-sm font-medium">
                            {{ uploadStatus }}
                        </div>

                        <div class="flex justify-end">
                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                class="bg-blue-600 text-white py-2 px-6 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition font-medium shadow-sm"
                            >
                                {{ form.processing ? 'Indexing...' : 'Upload to AI' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>