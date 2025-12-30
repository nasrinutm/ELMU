<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3'; // Added router
import { ref, watch } from 'vue'; // Added ref and watch
import { type BreadcrumbItem } from '@/types';
import { route } from 'ziggy-js';
import debounce from 'lodash/debounce';

const props = defineProps<{
    posts: any,
    can_create: boolean,
    filters: {
        search?: string;
        sort?: string;
    }
}>();

// Initialize local state with current filter values
const search = ref(props.filters.search || '');
const sort = ref(props.filters.sort || 'latest');

// Function to handle the actual request
const updateFilters = debounce(() => {
    router.get(route('forum.index'), { 
        search: search.value, 
        sort: sort.value 
    }, { 
        preserveState: true, 
        replace: true 
    });
}, 300);

// Watch for changes in search or sort
watch([search, sort], () => {
    updateFilters();
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/' },
    { title: 'Forum', href: route('forum.index') },
];

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
        <div class="w-full mx-auto py-6 px-8">
            
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Forum Discussions</h1>              

                <Link
                    :href="route('forum.create')" 
                    class="bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700 transition"
                >
                    Create New Post
                </Link>
                
            </div>

            <div class="flex flex-col md:flex-row gap-4 mb-6">
                <div class="flex-1">
                    <input 
                        v-model="search"
                        type="text" 
                        placeholder="Search posts..." 
                        class="w-full h-12 border border-search-border rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 px-4 text-lg"
                    />
                </div>
                <div class="w-full md:w-56">
                    <select 
                        v-model="sort"
                        class="w-full h-12 border border-search-border rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 px-4 text-lg"
                    >
                        <option value="latest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="replies">Most Replies</option>
                    </select>
                </div>
            </div>

            <div class="bg-white rounded-xl overflow-hidden border border-gray-200 shadow-sm">
                <div class="hidden md:flex bg-gray-50 p-4 border-b border-gray-200">
                    <div class="w-3/5 font-semibold text-lg text-gray-700">Topics</div>
                    <div class="w-1/5 font-semibold text-lg text-gray-700 text-center">Replies</div>
                    <div class="w-1/5 font-semibold text-lg text-gray-700 text-right">Last Activity</div>
                </div>

                <div v-if="!posts.data || posts.data.length === 0" class="p-8 text-center text-gray-500">
                    No discussions have been started yet.
                </div>

                <div v-else>
                    <div
                        v-for="post in posts.data"
                        :key="post.id"
                        class="flex flex-col md:flex-row p-4 border-b border-gray-100 last:border-0 hover:bg-gray-50 transition-colors items-start md:items-center"
                    >
                        <div class="w-full md:w-3/5 mb-2 md:mb-0 min-w-0">
                            <Link
                                :href="route('forum.show', { post: post.id })"
                                class="font-bold text-blue-600 underline text-lg block break-words"
                            >
                                {{ post.title }}
                            </Link>
                            <div class="text-sm text-gray-500">
                                By <span class="font-medium text-gray-700">{{ post.user?.name || 'Deleted User' }}</span>
                            </div>
                        </div>

                        <div class="w-full md:w-1/5 text-left md:text-center text-sm text-gray-600 shrink-0">
                            <span class="md:hidden font-medium">Replies: </span>
                            {{ post.replies_count || 0 }}
                        </div>
                        
                        <div class="w-full md:w-1/5 text-left md:text-right text-sm text-gray-600 shrink-0">
                            <span class="md:hidden font-medium">Last Activity: </span>
                            {{ formatDate(post.created_at) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>