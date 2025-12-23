<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ArrowLeft, Save, Upload } from 'lucide-vue-next';

// Setup the form object using Inertia
const form = useForm({
    title: '',
    type: 'Assignment', // Default value
    description: '',
    due_date: '',
    file: null as File | null,
});

const submit = () => {
    form.post(route('activities.store'), {
        forceFormData: true, // Required for file uploads
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
                
                <div class="w-full bg-[#003366] rounded-lg shadow-2xl overflow-hidden border border-white">
                    
                    <div class="p-6 border-b border-blue-800">
                        <h1 class="text-2xl font-bold text-white">Create New Activity</h1>
                        <p class="text-blue-200 text-sm mt-1">Fill in the details below to create a new classroom activity.</p>
                    </div>

                    <form @submit.prevent="submit" class="p-6 space-y-6">

                        <div>
                            <label for="type" class="block text-white font-bold mb-2">Activity Type</label>
                            <div class="relative">
                                <select 
                                    id="type" 
                                    v-model="form.type"
                                    class="w-full bg-[#1a3b5c] border border-blue-500/30 rounded-md px-4 py-3 text-white placeholder-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent appearance-none"
                                >
                                    <option value="Assignment">Assignment</option>
                                    <option value="Submission">Submission</option>
                                    <option value="Exercise">Exercise</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-white">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                </div>
                            </div>
                            <p v-if="form.errors.type" class="text-red-400 text-sm mt-1">{{ form.errors.type }}</p>
                        </div>

                        <div>
                            <label for="title" class="block text-white font-bold mb-2">Activity Title</label>
                            <input 
                                id="title" 
                                type="text" 
                                v-model="form.title"
                                placeholder="e.g. Chapter 1 Review"
                                class="w-full bg-[#1a3b5c] border border-blue-500/30 rounded-md px-4 py-3 text-white placeholder-blue-300/50 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                            />
                            <p v-if="form.errors.title" class="text-red-400 text-sm mt-1">{{ form.errors.title }}</p>
                        </div>

                        <div>
                            <label for="description" class="block text-white font-bold mb-2">Instructions / Description</label>
                            <textarea 
                                id="description" 
                                v-model="form.description"
                                rows="5"
                                placeholder="Enter detailed instructions for the students..."
                                class="w-full bg-[#1a3b5c] border border-blue-500/30 rounded-md px-4 py-3 text-white placeholder-blue-300/50 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                            ></textarea>
                            <p v-if="form.errors.description" class="text-red-400 text-sm mt-1">{{ form.errors.description }}</p>
                        </div>

                        <div>
                            <label for="due_date" class="block text-white font-bold mb-2">Due Date (Optional)</label>
                            <input 
                                id="due_date" 
                                type="date" 
                                v-model="form.due_date"
                                class="w-full bg-[#1a3b5c] border border-blue-500/30 rounded-md px-4 py-3 text-white placeholder-blue-300/50 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent [color-scheme:dark]"
                            />
                            <p v-if="form.errors.due_date" class="text-red-400 text-sm mt-1">{{ form.errors.due_date }}</p>
                        </div>

                        <div>
                            <label class="block text-white font-bold mb-2">Attach Resource (Optional)</label>
                            <div class="flex items-center justify-center w-full">
                                <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-32 border-2 border-blue-400/30 border-dashed rounded-lg cursor-pointer bg-[#1a3b5c] hover:bg-[#234b73] transition">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <Upload class="w-8 h-8 mb-3 text-blue-300" />
                                        <p class="mb-2 text-sm text-blue-100"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                                        <p class="text-xs text-blue-300" v-if="!form.file">PDF, DOCX, PPTX (MAX. 10MB)</p>
                                        <p class="text-sm font-bold text-green-400 mt-2" v-else>Selected: {{ form.file.name }}</p>
                                    </div>
                                    <input id="dropzone-file" type="file" class="hidden" @change="handleFileChange" />
                                </label>
                            </div>
                            <p v-if="form.errors.file" class="text-red-400 text-sm mt-1">{{ form.errors.file }}</p>
                        </div>

                        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-blue-800">
                            <Link 
                                :href="route('activities.index')"
                                class="bg-white text-[#003366] font-bold py-2 px-6 rounded-md hover:bg-gray-100 transition shadow-sm border border-transparent"
                            >
                                Cancel
                            </Link>

                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                class="bg-[#1e5bb0] hover:bg-[#164485] text-white font-bold py-2 px-6 rounded-md shadow-md transition flex items-center border border-blue-400/50"
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