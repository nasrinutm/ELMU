<script setup lang="ts">
import { useForm, Head, Link } from '@inertiajs/vue3';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { ArrowLeft, Upload, FileUp, X, Loader2 } from 'lucide-vue-next';
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
    }
};

const submit = () => {
    form.post(route('materials.store'));
};
</script>

<template>
    <Head title="Upload Material" />

    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-slate-50 p-4 sm:p-6 flex justify-center">

            <div class="w-full max-w-2xl">
                <div class="mb-6">
                    <Link :href="route('materials.index')" class="text-sm text-slate-500 hover:text-teal-600 flex items-center gap-1 mb-2 transition-colors">
                        <ArrowLeft class="w-4 h-4" /> Back to Materials
                    </Link>
                    <h1 class="text-2xl font-bold text-slate-900">Upload New Material</h1>
                    <p class="text-slate-500 mt-1">Share documents, assignments, or notes with your students.</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <form @submit.prevent="submit" class="p-6 sm:p-8 space-y-6">

                        <div class="space-y-2">
                            <Label class="text-slate-700 font-medium">Document File</Label>
                            <div
                                class="border-2 border-dashed border-slate-300 rounded-xl p-8 flex flex-col items-center justify-center text-center hover:bg-slate-50 hover:border-teal-400 transition-colors cursor-pointer relative"
                            >
                                <input
                                    type="file"
                                    @change="handleFileChange"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                    required
                                />

                                <div v-if="!form.file" class="flex flex-col items-center pointer-events-none">
                                    <div class="h-12 w-12 bg-teal-50 text-teal-600 rounded-full flex items-center justify-center mb-3">
                                        <FileUp class="w-6 h-6" />
                                    </div>
                                    <p class="text-sm font-medium text-slate-900">Click to upload or drag and drop</p>
                                    <p class="text-xs text-slate-500 mt-1">PDF, DOCX, PPTX (Max 10MB)</p>
                                </div>

                                <div v-else class="flex flex-col items-center pointer-events-none">
                                    <div class="h-12 w-12 bg-teal-100 text-teal-700 rounded-full flex items-center justify-center mb-3">
                                        <FileUp class="w-6 h-6" />
                                    </div>
                                    <p class="text-sm font-medium text-teal-700">{{ form.file.name }}</p>
                                    <p class="text-xs text-teal-600 mt-1">Click to change file</p>
                                </div>
                            </div>
                            <div v-if="form.errors.file" class="text-sm text-red-600">{{ form.errors.file }}</div>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="name" class="text-slate-700">Document Name</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    placeholder="e.g. Chapter 1 Notes"
                                    class="border-slate-200 focus:border-teal-500 focus:ring-teal-500"
                                    required
                                />
                                <div v-if="form.errors.name" class="text-sm text-red-600">{{ form.errors.name }}</div>
                            </div>

                            <div class="space-y-2">
                                <Label for="subject" class="text-slate-700">Subject / Topic</Label>
                                <Input
                                    id="subject"
                                    v-model="form.subject"
                                    placeholder="e.g. Mathematics"
                                    class="border-slate-200 focus:border-teal-500 focus:ring-teal-500"
                                    required
                                />
                                <div v-if="form.errors.subject" class="text-sm text-red-600">{{ form.errors.subject }}</div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="description" class="text-slate-700">Description (Optional)</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                placeholder="Briefly describe what this material covers..."
                                class="border-slate-200 focus:border-teal-500 focus:ring-teal-500 min-h-[100px]"
                            />
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                            <Link :href="route('materials.index')">
                                <Button type="button" variant="outline" class="border-slate-200 text-slate-700 hover:bg-slate-50">
                                    Cancel
                                </Button>
                            </Link>

                            <Button
                                type="submit"
                                :disabled="form.processing"
                                class="bg-teal-600 hover:bg-teal-700 text-white min-w-[120px]"
                            >
                                <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                                {{ form.processing ? 'Uploading...' : 'Upload File' }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
