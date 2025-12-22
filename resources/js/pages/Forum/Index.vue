<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { route } from 'ziggy-js';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce'; // Optional: npm install lodash

const props = defineProps<{
    posts: {
        data: Array<{
            id: number;
            title: string;
            user: { name: string };
            created_at: string;
            replies_count: number;
        }>;
    };
    filters: {
        search?: string;
        sort?: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/' },
    { title: 'Forum', href: route('forum.index') },
];

// Search and Sort State
const search = ref(props.filters.search || '');
const sort = ref(props.filters.sort || 'latest');

// Watcher to handle filtering
watch([search, sort], debounce(([newSearch, newSort]) => {
    router.get(route('forum.index'), { 
        search: newSearch, 
        sort: newSort 
    }, {
        preserveState: true,
        replace: true
    });
}, 300));

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
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Forum Discussions</h1>
                
                <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                    <input 
                        v-model="search"
                        type="text" 
                        placeholder="Search topics..." 
                        class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm p-2 w-full md:w-64"
                    />

                    <select 
                        v-model="sort"
                        class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm p-2"
                    >
                        <option value="latest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="popular">Most Popular</option>
                    </select>

                    <Link
                        :href="route('forum.create')" 
                        class="bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700 transition"
                    >
                        Create New Post
                    </Link>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
                <div class="hidden md:flex bg-gray-50 dark:bg-gray-700/50 p-3 border-b dark:border-gray-700">
                    <div class="w-3/5 font-semibold text-gray-700 dark:text-gray-300">Topics</div>
                    <div class="w-1/5 font-semibold text-center text-gray-700 dark:text-gray-300">Replies</div>
                    <div class="w-1/5 font-semibold text-right text-gray-700 dark:text-gray-300">Last Activity</div>
                </div>

                <div v-if="posts.data.length > 0">
                    <div
                        v-for="post in posts.data"
                        :key="post.id"
                        class="flex flex-col md:flex-row p-4 border-b last:border-0 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition shadow-sm"
                    >
                        <div class="w-full md:w-3/5 mb-2 md:mb-0">
                            <Link
                                :href="route('forum.show', { post: post.id })"
                                class="font-bold text-blue-600 dark:text-blue-400 hover:underline text-lg"
                            >
                                {{ post.title }}
                            </Link>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Started by <span class="font-medium text-gray-700 dark:text-gray-300">{{ post.user.name }}</span>
                            </div>
                        </div>

                        <div class="w-full md:w-1/5 text-left md:text-center text-sm text-gray-600 dark:text-gray-400">
                            <span class="md:hidden font-medium">Replies: </span>
                            {{ post.replies_count }}
                        </div>
                        
                        <div class="w-full md:w-1/5 text-left md:text-right text-sm text-gray-600 dark:text-gray-400">
                             <span class="md:hidden font-medium">Last Activity: </span>
                            {{ formatDate(post.created_at) }}
                        </div>
                    </div>
                </div>

                <div v-else class="p-12 text-center">
                    <p class="text-gray-500 dark:text-gray-400">No discussions found matching your criteria.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>