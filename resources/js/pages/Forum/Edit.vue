<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { type BreadcrumbItem, type Post } from '@/types';
import { route } from 'ziggy-js';
import { Save, Loader2 } from 'lucide-vue-next';

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
        <div class="w-full mx-auto p-4">
            <h1 class="text-2xl font-bold mb-4">Edit Post</h1>
            <div class="bg-white rounded-xl shadow-md p-6 border-1 border-gray-300">
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label for="title" class="block mb-1 font-medium">Title</label>
                        <input id="title" v-model="form.title" type="text" class="w-full rounded border-1 border-gray-300 p-1" />
                        <p v-if="form.errors.title" class="text-red-500 text-sm mt-1">
                            {{ form.errors.title }}
                        </p>
                    </div>
                    <div>
                        <label for="body" class="block mb-1 font-medium">Body</label>
                        <textarea id="body" v-model="form.body" rows="8" class="w-full rounded border-1 border-gray-300 p-1"></textarea>
                        <p v-if="form.errors.body" class="text-red-500 text-sm mt-1">
                            {{ form.errors.body }}
                        </p>
                    </div>
                    <div class="flex justify-end p-2">
                        <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-900 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm">
                            <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                            
                            <Save v-else class="w-4 h-4" />

                            <span>{{ form.processing ? 'Saving Changes...' : 'Save Changes' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>