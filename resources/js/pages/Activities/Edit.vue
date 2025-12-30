<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ArrowLeft, Save, Upload, FileText } from 'lucide-vue-next';

const props = defineProps<{
    activity: {
        id: number;
        title: string;
        description: string;
        type: string;
        due_date: string | null;
        file_path: string | null; 
    };
}>();

// Helper to format date for input (YYYY-MM-DD)
const formatDateForInput = (dateString: string | null) => {
    if (!dateString) return '';
    return dateString.split(' ')[0];
};

const form = useForm({
    // FIX: Define _method here as part of the data
    _method: 'PUT', 
    title: props.activity.title,
    description: props.activity.description,
    type: props.activity.type,
    due_date: formatDateForInput(props.activity.due_date),
    file: null as File | null,
});

const submit = () => {
    // FIX: Remove _method from options. 
    // We use .post() because HTML forms can't natively send PUT with files.
    form.post(route('activities.update', props.activity.id), {
        forceFormData: true,
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
    <Head title="Edit Activity" />

    <AppLayout :breadcrumbs="[
        { title: 'Activities', href: route('activities.index') }, 
        { title: 'Edit', href: '#' }
    ]">
        <div class="py-12">
            <div class="w-full sm:px-6 lg:px-8">
                <div class="w-full bg-[#003366] rounded-lg shadow-2xl overflow-hidden border border-white">
                    
                    <div class="p-6 border-b border-blue-800 flex justify-between items-center">
                        <div>
                            <h1 class="text-2xl font-bold text-white">Edit Activity</h1>
                            <p class="text-blue-200 text-sm mt-1">Update the details for "{{ activity.title }}"</p>
                        </div>
                        <Link :href="route('activities.index')">
                            <Button variant="outline" class="border-blue-400 text-blue-200 hover:text-white hover:bg-[#003366]">
                                <ArrowLeft class="w-4 h-4 mr-2" />
                                Back
                            </Button>
                        </Link>
                    </div>

                    <form @submit.prevent="submit" class="p-6 space-y-6">

                        <div>
                            <label class="block text-white font-bold mb-2">Activity Title</label>
                            <input type="text" v-model="form.title" class="w-full bg-[#1a3b5c] border border-blue-500/30 rounded-md px-4 py-3 text-white focus:ring-2 focus:ring-blue-400" />
                            <p v-if="form.errors.title" class="text-red-400 text-sm mt-1">{{ form.errors.title }}</p>
                        </div>

                        <div>
                            <label class="block text-white font-bold mb-2">Instructions</label>
                            <textarea rows="5" v-model="form.description" class="w-full bg-[#1a3b5c] border border-blue-500/30 rounded-md px-4 py-3 text-white focus:ring-2 focus:ring-blue-400"></textarea>
                            <p v-if="form.errors.description" class="text-red-400 text-sm mt-1">{{ form.errors.description }}</p>
                        </div>

                        <div>
                            <div class="p-4 bg-[#1a3b5c] rounded-md border border-blue-500/30 flex items-start gap-3">
                                <div class="flex items-center h-6">
                                    <input 
                                        id="edit_enable_submission" 
                                        type="checkbox" 
                                        v-model="form.type"
                                        true-value="Submission"
                                        false-value="Assignment"
                                        class="w-5 h-5 text-blue-600 bg-gray-700 border-gray-500 rounded focus:ring-blue-500 focus:ring-2 cursor-pointer"
                                    />
                                </div>
                                <label for="edit_enable_submission" class="text-white cursor-pointer select-none">
                                    <span class="block font-bold text-base">Enable Student Submission</span>
                                    <span class="block text-sm text-blue-200 mt-1">Check to allow student uploads.</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-white font-bold mb-2">Due Date</label>
                            <input type="date" v-model="form.due_date" class="w-full bg-[#1a3b5c] border border-blue-500/30 rounded-md px-4 py-3 text-white [color-scheme:dark]" />
                            <p v-if="form.errors.due_date" class="text-red-400 text-sm mt-1">{{ form.errors.due_date }}</p>
                        </div>

                        <div>
                            <label class="block text-white font-bold mb-2">Attached Resource</label>
                            
                            <div v-if="props.activity.file_path && !form.file" class="mb-3 flex items-center p-3 bg-blue-900/40 rounded border border-blue-500/30">
                                <FileText class="w-5 h-5 text-blue-300 mr-2" />
                                <span class="text-blue-100 text-sm">
                                    Current file: <strong>{{ props.activity.file_path.split('/').pop() }}</strong>
                                </span>
                            </div>

                            <div class="flex items-center justify-center w-full">
                                <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-blue-400/30 border-dashed rounded-lg cursor-pointer bg-[#1a3b5c] hover:bg-[#234b73] transition">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <Upload class="w-8 h-8 mb-3 text-blue-300" />
                                        <p class="mb-2 text-sm text-blue-100"><span class="font-semibold">Click to replace file</span> or drag and drop</p>
                                        <p class="text-xs text-blue-300" v-if="!form.file">Leave empty to keep current file</p>
                                        <p class="text-sm font-bold text-green-400 mt-2" v-else>New file selected: {{ form.file.name }}</p>
                                    </div>
                                    <input type="file" class="hidden" @change="handleFileChange" />
                                </label>
                            </div>
                            <p v-if="form.errors.file" class="text-red-400 text-sm mt-1">{{ form.errors.file }}</p>
                        </div>

                        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-blue-800">
                            <Link :href="route('activities.index')" class="text-white hover:underline">Cancel</Link>
                            <button type="submit" :disabled="form.processing" class="bg-[#1e5bb0] hover:bg-[#164485] text-white font-bold py-2 px-6 rounded-md shadow-md transition flex items-center border border-blue-400/50">
                                <Save class="w-4 h-4 mr-2" />
                                Save Changes
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>