<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { route } from 'ziggy-js';

const props = defineProps<{
    // posts: {
    //     data: Array<{
    //         id: number;
    //         title: string;
    //         user: {
    //             name: string;
    //         };
    //         created_at: string;
    //         replies_count: number;
    //     }>;
    // };
    // filters: object;
    posts: any,
    can_create: boolean
}>();

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
        <div class="w-full mx-auto p-4">
            
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-xl font-semibold">Forum Discussions</h1>
                
                <!-- <Link
                    :href="route('forum.create')" 
                    class="bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700 transition"
                >
                    Create New Post
                </Link> -->
                <Link v-if="can_create" :href="route('forum.create')">Create New Post</Link>
            </div>

            <div class="bg-white rounded-xl overflow-hidden border border-gray-200 shadow-sm">
                <div class="hidden md:flex bg-gray-50 p-4 border-b border-gray-200">
                    <div class="w-3/5 font-semibold text-gray-700">Topics</div>
                    <div class="w-1/5 font-semibold text-gray-700 text-center">Replies</div>
                    <div class="w-1/5 font-semibold text-gray-700 text-right">Last Activity</div>
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
                        <div class="w-full md:w-3/5 mb-2 md:mb-0">
                            <Link
                                :href="route('forum.show', { post: post.id })"
                                class="font-bold text-blue-600 hover:underline text-lg"
                            >
                                {{ post.title }}
                            </Link>
                            <div class="text-sm text-gray-500">
                                By <span class="font-medium text-gray-700">{{ post.user?.name || 'Deleted User' }}</span>
                            </div>
                        </div>

                        <div class="w-full md:w-1/5 text-left md:text-center text-sm text-gray-600">
                            <span class="md:hidden font-medium">Replies: </span>
                            {{ post.replies_count || 0 }}
                        </div>
                        
                        <div class="w-full md:w-1/5 text-left md:text-right text-sm text-gray-600">
                             <span class="md:hidden font-medium">Last Activity: </span>
                            {{ formatDate(post.created_at) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>