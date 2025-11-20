<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { type BreadcrumbItem } from '@/types';
import { route } from 'ziggy-js';

// 1. Define Props (from MaterialController@edit)
const props = defineProps<{
    material: {
        id: number;
        name: string;
        subject: string;
        description: string | null;
        file_name: string;
    };
}>();

// 2. Setup Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Materials', href: route('materials.index') },
    { title: 'Edit', href: route('materials.edit', props.material.id) },
];

// 3. Setup the form
// NOTE: We must use POST for file uploads, even for an update.
// We add `_method: 'put'` to tell Laravel to treat it as a PUT request.
const form = useForm({
    _method: 'put',
    name: props.material.name,
    subject: props.material.subject,
    description: props.material.description || '',
    file: null as File | null,
});

// 4. Submit handler
const submit = () => {
    form.post(route('materials.update', props.material.id), {
        onError: () => {
            if (form.errors.file) {
                form.reset('file');
            }
        },
    });
};
</script>

<template>
    <Head title="Edit Material" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white bg-gray-800 overflow-hidden shadow-md sm:rounded-lg">
                    <div class="p-6 bg-[#ffd900]   border-[#003366]">

                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            Edit Material
                        </h3>

                        <form @submit.prevent="submit">
                            <!-- Name -->
                            <div class="text-[#003366]">
                                <Label for="name">Name / Title</Label>
                                <Input
                                    id="name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.name"
                                    required
                                    autofocus
                                />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <!-- Subject -->
                            <div class="mt-4 text-[#003366]">
                                <Label for="subject">Subject</Label>
                                <Input
                                    id="subject"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.subject"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.subject" />
                            </div>

                            <!-- Description -->
                            <div class="mt-4 text-[#003366]">
                                <Label for="description">Description (Optional)</Label>
                                <textarea
                                    id="description"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    v-model="form.description"
                                    rows="4"
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.description" />
                            </div>

                            <!-- File Upload (Optional) -->
                            <div class="mt-4 text-[#003366]">
                                <Label for="file">Replace File (Optional)</Label>
                                <p class="text-sm text-gray-500 mb-2">
                                    Current file: {{ material.file_name }}
                                </p>
                                <Input
                                    id="file"
                                    type="file"
                                    class="mt-1 block w-full"
                                    @input="form.file = ($event.target as HTMLInputElement).files?.[0] ?? null"
                                />
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Leave blank to keep the current file.
                                </p>
                                <InputError class="mt-2" :message="form.errors.file" />
                            </div>

                            <!-- Submit Button -->
                            <div class="flex items-center justify-end mt-4">
                                <Link :href="route('materials.index')" class="mr-4">
                                    <Button  type="button">Cancel</Button>
                                </Link>
                                <Button type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Save Changes
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
