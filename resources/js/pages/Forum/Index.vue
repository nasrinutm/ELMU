<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3'; // Import Link
import { type BreadcrumbItem } from '@/types';
// We'll assume a 'Post' type, you can define this in your types/index.ts
// import { type Post } from '@/types';
import { route } from 'ziggy-js';

// Props from the controller
const props = defineProps<{
    posts: {
        data: Array<{
            id: number;
            title: string;
            user: {
                name: string;
            };
            created_at: string;
            replies_count: number; // From the withCount()
        }>;
        // ... pagination links etc.
    };
    filters: object; // For search/filters later
}>();

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/' }, // Assuming you have a dashboard route
    { title: 'Forum', href: route('forum.index') },
];

// Helper to format date (optional, but nice)
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Forum" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full mx-auto p-4 bg-transparent">
            
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-xl font-semibold">Forum Discussions</h1>
                
                <Link
                    :href="route('forum.create')" 
                    class="bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700"
                >
                    Create New Post
                </Link>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden">
                <div class="hidden md:flex bg-gray-100 dark:bg-gray-700 p-3">
                    <div class="w-3/5 font-semibold text-lg">Topics</div>
                    <div class="w-1/5 font-semibold text-center text-lg">Replies</div>
                    <div class="w-1/5 font-semibold text-right text-lg mr-px">Last Activity</div>
                </div>

                <div v-if="posts.data.length > 0">
                    <div
                        v-for="post in posts.data"
                        :key="post.id"
                        class="flex flex-col md:flex-row p-3 border-t dark:border-gray-600 items-start md:items-center"
                    >
                        <div class="w-full md:w-3/5 mb-2 md:mb-0">
                            <Link
                                :href="route('forum.show', { post: post.id })"
                                class="font-semibold text-black dark:text-blue-400"
                            >
                                {{ post.title }}
                            </Link>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ post.user.name }}
                            </div>
                        </div>

                        <div class="w-full md:w-1/5 text-left md:text-center text-sm">
                            <span class="md:hidden font-medium">Replies: </span>
                            {{ post.replies_count }}
                        </div>
                        
                        <div class="w-full md:w-1/5 text-left md:text-right text-sm">
                             <span class="md:hidden font-medium">Last Activity: </span>
                            {{ formatDate(post.created_at) }}
                        </div>
                    </div>
                </div>

                <div v-else>
                    <p class="p-4 text-center">No discussions have been started yet.</p>
                </div>
            </div>

            </div>
    </AppLayout>
</template>