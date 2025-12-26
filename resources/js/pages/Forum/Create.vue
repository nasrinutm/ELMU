<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { route } from 'ziggy-js';

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/' },
    { title: 'Forum', href: route('forum.index') },
    { title: 'Create Post', href: route('forum.create') },
];

// Set up the form helper
// 'title' and 'body' match the columns in your 'posts' table
const form = useForm({
    title: '',
    content: '',
});

// Submit handler
const submit = () => {
    // This will POST the form data to your 'forum.store' route
    form.post(route('forum.store'), {
        onSuccess: () => {
            // This will clear the form after a successful post
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Create New Post" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full mx-auto p-4 bg-transparent">
            
            <h1 class="text-xl font-semibold mb-4">Create New Discussion</h1>

            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <form @submit.prevent="submit" class="p-6 space-y-4">
                    
                    <div>
                        <label for="title" class="block mb-1 font-medium">Title</label>
                        <input
                            id="title"
                            v-model="form.title"
                            type="text"
                            class="w-full rounded border px-3 py-2"
                            placeholder="What's your discussion about?"
                        />
                        <p v-if="form.errors.title" class="text-red-500 text-sm mt-1">
                            {{ form.errors.title }}
                        </p>
                    </div>

                    <div>
                        <label for="content" class="block mb-1 font-medium">Content</label>
                        <textarea
                            id="content"
                            v-model="form.content" 
                            rows="8"
                            autocomplete="off"
                            spellcheck="false"
                            data-1p-ignore
                            data-lpignore="true"
                            class="w-full rounded border px-3 py-2"
                            placeholder="Write the main content of your post..."
                        ></textarea>
                        <p v-if="form.errors.content" class="text-red-500 text-sm mt-1">
                            {{ form.errors.content }}
                        </p>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Posting...' : 'Create Post' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </AppLayout>
</template>