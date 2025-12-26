<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { type BreadcrumbItem } from '@/types';
import { route } from 'ziggy-js';
import { FileText, X } from 'lucide-vue-next';
import { Upload } from 'lucide-vue-next';

// 1. Setup Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Materials', href: route('materials.index') },
    { title: 'Upload', href: route('materials.create') },
];

// 2. Setup the form (Changed file to files array)
const form = useForm({
    name: '',
    subject: '',
    description: '',
    files: [] as File[],
});

// 3. Handle File Input
const handleFileChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files) {
        // Convert FileList to Array
        const newFiles = Array.from(input.files);
        form.files = [...form.files, ...newFiles];
    }
};

// 4. Remove File
const removeFile = (index: number) => {
    form.files.splice(index, 1);
};

// 5. Submit handler
const submit = () => {
    form.post(route('materials.store'), {
        onError: () => {
        },
    });
};
</script>

<template>
    <Head title="Upload Material" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-md sm:rounded-lg">
                    <div class="p-6 bg-[#ffd900] border-b border-gray-200">

                        <h3 class="text-lg font-medium text-[#003366] mb-4">
                            Upload New Materials
                        </h3>

                        <form @submit.prevent="submit">

                            <div class="text-[#003366]">
                                <Label for="subject">Subject</Label>
                                <Input
                                    id="subject"
                                    type="text"
                                    class="mt-1 block w-full "
                                    v-model="form.subject"
                                    required
                                    autofocus
                                    placeholder="e.g. Mathematics 101"
                                />
                                <InputError class="mt-2" :message="form.errors.subject" />
                            </div>


                            <div class="mt-4 text-[#003366]">
                                <Label for="description">Description (Optional)</Label>
                                <textarea
                                    id="description"
                                    class="mt-1 block w-full bg-[#003366] border-black  text-white focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm p-2"
                                    v-model="form.description"
                                    rows="3"
                                    placeholder="Brief description for these files..."
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.description" />
                            </div>

                            <!-- Optional Name Prefix -->
                            <div class="mt-4 text-[#003366]">
                                <Label for="name">Document Title (Optional)</Label>
                                <Input
                                    id="name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.name"
                                    placeholder="Leave blank to use filenames"
                                />
                                <p class="text-xs text-gray-500 mt-1">If blank, the original filename will be used as the title.</p>
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <!-- Multi-File Upload -->
                            <div class="mt-6 text-[#003366]">
                                <Label for="files">Select Files (PDF, DOCX, PPT)</Label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-indigo-400 transition-colors cursor-pointer relative">
                                    <div class="space-y-1 text-center">
                                        <Upload class="mx-auto h-12 w-12 text-gray-400" />
                                        <div class="flex text-sm text-gray-600">
                                            <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                <span>Upload files</span>
                                                <input id="file-upload" name="file-upload" type="file" class="sr-only" multiple @change="handleFileChange" accept=".pdf,.doc,.docx,.ppt,.pptx">
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">
                                            PDF, DOC, PPT up to 10MB each
                                        </p>
                                    </div>
                                    <!-- Cover the whole area for drag/drop feel -->
                                    <input type="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" multiple @change="handleFileChange" accept=".pdf,.doc,.docx,.ppt,.pptx" />
                                </div>
                                <InputError class="mt-2" :message="form.errors.files" />
                            </div>

                            <!-- Selected Files List -->
                            <div v-if="form.files.length > 0" class="mt-4 space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Selected Files:</h4>
                                <div v-for="(file, index) in form.files" :key="index" class="flex items-center justify-between bg-gray-50 p-2 rounded-md border">
                                    <div class="flex items-center overflow-hidden">
                                        <FileText class="h-4 w-4 text-blue-500 mr-2 shrink-0" />
                                        <span class="text-sm truncate">{{ file.name }}</span>
                                        <span class="text-xs text-gray-400 ml-2">({{ (file.size / 1024 / 1024).toFixed(2) }} MB)</span>
                                    </div>
                                    <button type="button" @click="removeFile(index)" class="text-gray-400 hover:text-red-500">
                                        <X class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('materials.index')" class="mr-4">
                                    <Button variant="default" class="text-[#003366]" type="button">Cancel</Button>
                                </Link>
                                <Button type="submit" variant="secondary" :class="{ 'opacity-25': form.processing }" :disabled="form.processing || form.files.length === 0">
                                    Upload {{ form.files.length > 0 ? `(${form.files.length})` : '' }}
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
