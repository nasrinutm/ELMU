<script setup lang="ts">
import { useForm, Head, Link } from '@inertiajs/vue3';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { ArrowLeft, FileUp, Loader2, FolderPlus, AlertCircle, FileCheck } from 'lucide-vue-next';
import { route } from 'ziggy-js';

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Materials', href: route('materials.index') },
    { title: 'Upload', href: route('materials.create') },
];

const form = useForm({
    name: '',
    subject: '',
    description: '',
    file: null as File | null,
});

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        form.file = target.files[0];
        // Clear error when user selects a file
        form.clearErrors('file');
    }
};

const submit = () => {
    // Optional: Manual client-side check before sending to Laravel
    if (!form.file) form.setError('file', 'Please upload a document file');
    if (!form.name) form.setError('name', 'Please input document name');
    if (!form.subject) form.setError('subject', 'Please input subject/topic');

    if (!form.hasErrors) {
        form.post(route('materials.store'));
    }
};
</script>

<template>
    <Head title="Upload Material" />

    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-6xl mx-auto p-6 space-y-6 font-sans antialiased text-slate-900">

            <div class="flex items-center justify-between border-b border-slate-100 pb-6">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-slate-100 rounded-lg text-slate-600 border border-slate-200 shadow-sm">
                        <FolderPlus class="w-6 h-6" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight uppercase">Upload Material</h1>
                        <p class="text-sm text-slate-500 font-medium">Add new resources to the learning library.</p>
                    </div>
                </div>
                <Link :href="route('materials.index')" class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-teal-600 transition-all group">
                    <ArrowLeft class="w-4 h-4 transition-transform group-hover:-translate-x-1" /> Back to Materials
                </Link>
            </div>

            <div class="bg-white border border-slate-200 rounded-none shadow-sm overflow-hidden">
                <form @submit.prevent="submit">
                    <div class="flex flex-col lg:flex-row">

                        <div class="lg:w-2/5 p-8 bg-slate-50/50 border-r border-slate-100 flex flex-col justify-center">
                            <Label class="block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 mb-4 text-center lg:text-left">
                                Document File <span class="text-red-500">*</span>
                            </Label>

                            <div
                                class="h-full min-h-[250px] border-2 border-dashed rounded-none flex flex-col items-center justify-center text-center hover:bg-white hover:border-teal-500 transition-all cursor-pointer relative bg-white shadow-inner"
                                :class="[
                                    form.file ? 'border-teal-500 ring-2 ring-teal-500/10' : 'border-slate-200',
                                    form.errors.file ? 'border-red-500 bg-red-50/30' : ''
                                ]"
                            >
                                <input
                                    type="file"
                                    @change="handleFileChange"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                />

                                <div v-if="!form.file" class="flex flex-col items-center p-6 pointer-events-none">
                                    <div class="h-16 w-16 bg-slate-50 text-slate-400 rounded-full flex items-center justify-center mb-4 border border-slate-100">
                                        <FileUp class="w-8 h-8" />
                                    </div>
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Click to Browse</p>
                                    <p class="text-[9px] font-bold uppercase tracking-widest text-slate-400 mt-2">PDF, DOCX, PPTX (MAX 10MB)</p>
                                </div>

                                <div v-else class="flex flex-col items-center p-6 pointer-events-none text-teal-700">
                                    <div class="h-16 w-16 bg-teal-50 rounded-full flex items-center justify-center mb-4 border border-teal-100">
                                        <FileCheck class="w-8 h-8" />
                                    </div>
                                    <p class="text-sm font-bold uppercase tracking-tight break-all max-w-[200px]">{{ form.file.name }}</p>
                                    <p class="text-[9px] font-black uppercase tracking-widest mt-2 bg-teal-600 text-white px-2 py-1 rounded-none">Ready to Upload</p>
                                </div>
                            </div>
                            <p v-if="form.errors.file" class="text-red-600 text-[10px] font-bold uppercase mt-3 italic flex items-center justify-center gap-1">
                                <AlertCircle class="w-3 h-3" /> {{ form.errors.file }}
                            </p>
                        </div>

                        <div class="lg:w-3/5 p-8 sm:p-10 space-y-6">

                            <div class="space-y-2">
                                <Label for="name" class="block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">
                                    Document Name <span class="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    @input="form.clearErrors('name')"
                                    placeholder="e.g. Chapter 1 Notes"
                                    class="h-12 rounded-none border-slate-200 focus:ring-1 focus:ring-teal-500 focus:border-teal-500 placeholder:text-slate-300 font-medium"
                                    :class="{'border-red-500 ring-1 ring-red-500': form.errors.name}"
                                />
                                <p v-if="form.errors.name" class="text-red-600 text-[10px] font-bold uppercase mt-1 italic flex items-center gap-1">
                                    <AlertCircle class="w-3 h-3" /> {{ form.errors.name }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="subject" class="block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">
                                    Subject / Topic <span class="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="subject"
                                    v-model="form.subject"
                                    @input="form.clearErrors('subject')"
                                    placeholder="e.g. Mathematics"
                                    class="h-12 rounded-none border-slate-200 focus:ring-1 focus:ring-teal-500 focus:border-teal-500 placeholder:text-slate-300 font-medium"
                                    :class="{'border-red-500 ring-1 ring-red-500': form.errors.subject}"
                                />
                                <p v-if="form.errors.subject" class="text-red-600 text-[10px] font-bold uppercase mt-1 italic flex items-center gap-1">
                                    <AlertCircle class="w-3 h-3" /> {{ form.errors.subject }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="description" class="block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">
                                    Description (Optional)
                                </Label>
                                <Textarea
                                    id="description"
                                    v-model="form.description"
                                    placeholder="Briefly describe what this material covers..."
                                    class="rounded-none border-slate-200 focus:ring-1 focus:ring-teal-500 focus:border-teal-500 min-h-[140px] placeholder:text-slate-300 font-medium"
                                />
                            </div>

                            <div class="pt-6 flex justify-end gap-3 border-t border-slate-50">
                                <Link :href="route('materials.index')">
                                    <Button type="button" variant="outline" class="rounded-none border-slate-200 text-[10px] font-bold uppercase tracking-[0.2em] px-8 py-5 h-auto hover:bg-slate-50 transition-colors">
                                        Cancel
                                    </Button>
                                </Link>

                                <Button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="bg-teal-600 hover:bg-teal-700 text-white font-bold text-[10px] uppercase tracking-[0.3em] px-12 py-5 rounded-none shadow-md transition-all disabled:opacity-50 h-auto"
                                >
                                    <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                                    {{ form.processing ? 'Uploading...' : 'Upload File' }}
                                </Button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>
