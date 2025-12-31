<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ArrowLeft, Save, Upload, CheckSquare } from 'lucide-vue-next';

// Setup the form object using Inertia
const form = useForm({
    title: '',
    // Default is 'Assignment'. Checking the box changes this to 'Submission'
    type: 'Assignment', 
    description: '',
    due_date: '',
    file: null as File | null,
});

const submit = () => {
    form.post(route('activities.store'), {
        forceFormData: true,
        // Optional: Log errors to console to help debug
        onError: (errors) => {
            console.error('Submission Failed:', errors);
        }
    });
};

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        form.file = target.files[0];
    }
};
</script>

<template>
    <Head title="Create Activity" />

    <AppLayout :breadcrumbs="[{ title: 'Activities', href: route('activities.index') }, { title: 'Create', href: '#' }]">
        <div class="py-12">
            <div class="w-full sm:px-6 lg:px-8">
                
                <div class="w-full bg-[#ffffff] rounded-lg shadow-2xl overflow-hidden border border-white">
                    
                    <div class="p-6 border-b border-gray-800">
                        <h1 class="text-2xl font-bold text-[#212121]">Create New Activity</h1>
                        <p class="text-gray-800 text-sm mt-1">Fill in the details below to create a new classroom activity.</p>
                    </div>

                    <form @submit.prevent="submit" class="p-6 space-y-6">

                        <div>
                            <label for="title" class="block text-[#212121] font-bold mb-2">Activity Title</label>
                            <input 
                                id="title" 
                                type="text" 
                                v-model="form.title"
                                placeholder="e.g. Chapter 1 Review"
                                class="w-full bg-[#ffffff] border border-gray-500/30 rounded-md px-4 py-3 text-[#212121] placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                            />
                            <p v-if="form.errors.title" class="text-red-400 text-sm mt-1">{{ form.errors.title }}</p>
                        </div>

                        <div>
                            <label for="description" class="block text-[#212121] font-bold mb-2">Instructions / Description</label>
                            <textarea 
                                id="description" 
                                v-model="form.description"
                                rows="5"
                                autocomplete="off"
                                spellcheck="false"
                                data-1p-ignore
                                data-lpignore="true"
                                placeholder="Enter detailed instructions for the students..."
                                class="w-full bg-[#ffffff] border border-gray-500/30 rounded-md px-4 py-3 text-[#212121] placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                            ></textarea>
                            <p v-if="form.errors.description" class="text-red-400 text-sm mt-1">{{ form.errors.description }}</p>
                        </div>

                        <div>
                            <div class="p-4 bg-[#ffffff] rounded-md border border-gray-500/30 flex items-start gap-3">
                                <div class="flex items-center h-6">
                                    <input 
                                        id="enable_submission" 
                                        type="checkbox" 
                                        v-model="form.type"
                                        true-value="Submission"
                                        false-value="Assignment"
                                        class="w-5 h-5 text-blue-600 bg-gray-700 border-gray-500 rounded focus:ring-blue-500 focus:ring-2 cursor-pointer"
                                    />
                                </div>
                                <label for="enable_submission" class="text-[#212121] cursor-pointer select-none">
                                    <span class="block font-bold text-base">Enable Student Submission</span>
                                    <span class="block text-sm text-gray-400 mt-1">
                                        Check this box if you want students to upload a file or submit work for this activity.
                                    </span>
                                </label>
                            </div>
                            <p v-if="form.errors.type" class="text-red-400 text-sm mt-2 font-bold animate-pulse">
                                Error: {{ form.errors.type }}
                            </p>
                        </div>

                        <div>
                            <label for="due_date" class="block text-[#212121] font-bold mb-2">Due Date (Optional)</label>
                            <input 
                                id="due_date" 
                                type="date" 
                                v-model="form.due_date"
                                class="w-full bg-[#ffffff] border border-gray-500/30 rounded-md px-4 py-3 text-[#212121] placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent [color-scheme:dark]"
                            />
                            <p v-if="form.errors.due_date" class="text-red-400 text-sm mt-1">{{ form.errors.due_date }}</p>
                        </div>

                        <div>
                            <label class="block text-[#212121] font-bold mb-2">Attach Resource (Optional)</label>
                            <div class="flex items-center justify-center w-full">
                                <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-500/30 border-dashed rounded-lg cursor-pointer bg-[#ffffff] hover:bg-[#808080] transition">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <Upload class="w-8 h-8 mb-3 text-gray-400" />
                                        <p class="mb-2 text-sm text-gray-400"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                                        <p class="text-xs text-gray-400" v-if="!form.file">PDF, DOCX, PPTX (MAX. 10MB)</p>
                                        <p class="text-sm font-bold text-green-400 mt-2" v-else>Selected: {{ form.file.name }}</p>
                                    </div>
                                    <input id="dropzone-file" type="file" class="hidden" @change="handleFileChange" />
                                </label>
                            </div>
                            <p v-if="form.errors.file" class="text-red-400 text-sm mt-1">{{ form.errors.file }}</p>
                        </div>

                        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-800">
                            <Link 
                                :href="route('activities.index')"
                                class="bg-white text-[#003366] font-bold py-2 px-6 rounded-md hover:bg-gray-100 transition shadow-sm border border-transparent"
                            >
                                Cancel
                            </Link>

                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                class="bg-[#0060df] hover:bg-[#164485] text-[#ffffff] font-bold py-2 px-6 rounded-md shadow-md transition flex items-center border border-blue-400/50"
                            >
                                <Save class="w-4 h-4 mr-2" />
                                {{ form.processing ? 'Creating...' : 'Create Activity' }}
                            </button>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </AppLayout>
</template>