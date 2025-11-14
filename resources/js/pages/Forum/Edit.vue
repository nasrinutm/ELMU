<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { type BreadcrumbItem, type Post } from '@/types';
import { route } from 'ziggy-js';

const props = defineProps<{
    post: Post;
}>();

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Forum', href: route('forum.index') },
    { title: 'Post', href: route('forum.show', props.post.id) },
    { title: 'Edit', href: '#' },
];

// Set up the form, pre-filled with the post's data
const form = useForm({
    title: props.post.title,
    body: props.post.body,
});

// Submit handler
const submit = () => {
    // Use PUT to send to the 'update' route
    form.put(route('forum.update', props.post.id));
};
</script>

<template>
    <Head title="Edit Post" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-4xl mx-auto p-4">
            <h1 class="text-2xl font-bold mb-4">Edit Post</h1>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label for="title" class="block mb-1 font-medium">Title</label>
                        <input id="title" v-model="form.title" type="text" class="w-full rounded" />
                        <p v-if="form.errors.title" class="text-red-500 text-sm mt-1">
                            {{ form.errors.title }}
                        </p>
                    </div>
                    <div>
                        <label for="body" class="block mb-1 font-medium">Body</label>
                        <textarea id="body" v-model="form.body" rows="8" class="w-full rounded"></textarea>
                        <p v-if="form.errors.body" class="text-red-500 text-sm mt-1">
                            {{ form.errors.body }}
                        </p>
                    </div>
                    <div class="flex justify-end pt-2">
                        <button type="submit" :disabled="form.processing" class="bg-blue-600 ...">
                            {{ form.processing ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>