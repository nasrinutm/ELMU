<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { route } from 'ziggy-js';
import { ArrowLeft, Save, Upload, Calendar, FileText } from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';

// --- ROLE-BASED THEME LOGIC ---
const page = usePage();
const authUser = computed(() => (page.props as any).auth.user);

const themeActionClass = computed(() => {
    // Uses your CSS variables: --action-color and --action-hover
    return 'bg-[var(--action-color)] hover:bg-[var(--action-hover)] text-white border-none shadow-lg transition-all font-bold uppercase text-[10px] tracking-widest px-8 py-6 rounded-none flex items-center gap-2';
});

const breadcrumbs = [
    { title: 'Classroom Activities', href: route('activities.index') },
    { title: 'Create Activity', href: '#' },
];

const form = useForm({
    title: '',
    type: 'Submission', // Fixed to Submission as requested
    description: '',
    due_date: '',
    file: null as File | null,
});

const submit = () => {
    form.post(route('activities.store'), {
        forceFormData: true,
        onError: (errors) => {
            console.error('Creation Failed:', errors);
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

    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-slate-50 p-6 space-y-6">

            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900 uppercase">Create New Activity</h1>
                    <p class="text-slate-500 mt-1 text-sm italic font-medium">Define a new task for your students in the cloud vault.</p>
                </div>
                <Link :href="route('activities.index')">
                    <Button variant="outline" class="rounded-none border-slate-200 text-slate-600 hover:bg-slate-100 uppercase text-[10px] font-bold tracking-widest">
                        <ArrowLeft class="w-4 h-4 mr-2" /> Back to List
                    </Button>
                </Link>
            </div>

            <div class="bg-white border border-slate-200 shadow-sm max-w-4xl rounded-none">
                <form @submit.prevent="submit" class="p-8 space-y-8">

                    <div class="space-y-2">
                        <label for="title" class="text-xs font-bold uppercase tracking-[0.2em] text-slate-900 flex items-center gap-1">
                            Activity Title <span class="text-red-500">*</span>
                        </label>
                        <Input
                            id="title"
                            type="text"
                            v-model="form.title"
                            placeholder="e.g. JAVA PROGRAMMING CHALLENGE - CHAPTER 1"
                            class="rounded-none border-slate-200 h-12 focus:ring-slate-900 bg-slate-50/50"
                        />
                        <p v-if="form.errors.title" class="text-red-500 text-[10px] font-bold uppercase tracking-tight">{{ form.errors.title }}</p>
                    </div>

                    <div class="space-y-2">
                        <label for="description" class="text-xs font-bold uppercase tracking-[0.2em] text-slate-900">
                            Instructions / Description <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="6"
                            placeholder="Provide clear instructions for the students..."
                            class="w-full bg-slate-50/50 border border-slate-200 rounded-none px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all"
                        ></textarea>
                        <p v-if="form.errors.description" class="text-red-500 text-[10px] font-bold uppercase tracking-tight">{{ form.errors.description }}</p>
                    </div>

                    <div class="space-y-2">
                        <label for="due_date" class="text-xs font-bold uppercase tracking-[0.2em] text-slate-900">
                            Due Date <span class="text-red-500">*</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <Calendar class="h-4 w-4 text-slate-400 group-focus-within:text-slate-900 transition-colors" />
                            </div>
                            <input
                                id="due_date"
                                type="date"
                                v-model="form.due_date"
                                class="w-full bg-slate-50/50 border border-slate-200 rounded-none pl-10 pr-4 py-3 text-sm text-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all cursor-pointer"
                            />
                        </div>
                        <p v-if="form.errors.due_date" class="text-red-500 text-[10px] font-bold uppercase tracking-tight">{{ form.errors.due_date }}</p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-[0.2em] text-slate-900">
                            Attach Resource (Optional)
                        </label>
                        <div class="flex items-center justify-center w-full">
                            <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-40 border-2 border-slate-200 border-dashed rounded-none cursor-pointer bg-slate-50/50 hover:bg-slate-100 transition-colors group">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <Upload class="w-8 h-8 mb-3 text-slate-300 group-hover:text-slate-500 transition-colors" />
                                    <p class="mb-2 text-xs text-slate-500 tracking-wider font-medium"><span class="font-bold uppercase">Click to upload</span> or drag and drop</p>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest" v-if="!form.file">PDF, DOCX, PPTX, ZIP (MAX. 10MB)</p>
                                    <div v-else class="flex items-center gap-2 mt-2 px-4 py-2 bg-emerald-50 border border-emerald-200">
                                        <FileText class="w-4 h-4 text-emerald-600" />
                                        <p class="text-[10px] font-bold text-emerald-700 uppercase">{{ form.file.name }}</p>
                                    </div>
                                </div>
                                <input id="dropzone-file" type="file" class="hidden" @change="handleFileChange" />
                            </label>
                        </div>
                        <p v-if="form.errors.file" class="text-red-500 text-[10px] font-bold uppercase tracking-tight">{{ form.errors.file }}</p>
                    </div>

                    <div class="flex items-center justify-end space-x-4 pt-8 border-t border-slate-100">
                        <Link :href="route('activities.index')" class="text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-slate-900 transition-colors">
                            Cancel
                        </Link>
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            :class="themeActionClass"
                        >
                            <Save v-if="!form.processing" class="w-4 h-4" />
                            {{ form.processing ? 'Creating...' : 'Create Activity' }}
                        </Button>
                    </div>

                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>

<style scoped>
/* Ensure input[type="date"] picker trigger covers the area for easier click */
input[type="date"]::-webkit-calendar-picker-indicator {
    cursor: pointer;
    opacity: 0.6;
    transition: opacity 0.2s;
}
input[type="date"]::-webkit-calendar-picker-indicator:hover {
    opacity: 1;
}
</style>
