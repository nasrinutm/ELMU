<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { type BreadcrumbItem, type Post, type Reply as ReplyType } from '@/types';
import { route } from 'ziggy-js';
import Reply from '@/components/Reply.vue'; // <-- We will create this next

// Props
const props = defineProps<{
    post: Post & {
        user: { name: string, username: string };
        replies: ReplyType[]; // Top-level replies
    };
}>();

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/' },
    { title: 'Forum', href: route('forum.index') },
    { title: 'Post', href: '#' }, // Current page
];

// Form for adding a new (top-level) reply
const replyForm = useForm({
    body: '',
    post_id: props.post.id,
    parent_id: null as number | null, // null = top-level reply
});

const submitReply = () => {
    replyForm.post(route('replies.store'), { // We need to add this route
        preserveScroll: true,
        onSuccess: () => replyForm.reset(),
    });
};
</script>

<template>
    <Head :title="post.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-4xl mx-auto p-4 bg-transparent">

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden">
                <div class="p-6">
                    <h1 class="text-3xl font-bold mb-2">{{ post.title }}</h1>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Posted by @{{ post.user.username }}
                    </div>
                    
                    <div classs="prose dark:prose-invert max-w-none">
                        <p>{{ post.body }}</p>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <h2 class="text-xl font-bold mb-4">Replies</h2>

                <div class="space-y-4">
                    <Reply 
                        v-for="reply in post.replies"
                        :key="reply.id"
                        :reply="reply"
                        :post_id="post.id"
                    />
                </div>

                <div class="mt-6 bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
                    <form @submit.prevent="submitReply">
                        <label for="body" class="block mb-2 font-medium">Your Reply</label>
                        <textarea
                            id="body"
                            v-model="replyForm.body"
                            rows="5"
                            class="w-full rounded border px-3 py-2"
                            placeholder="Write your reply..."
                        ></textarea>
                        <p v-if="replyForm.errors.body" class="text-red-500 text-sm mt-1">
                            {{ replyForm.errors.body }}
                        </p>
                        <div class="flex justify-end mt-4">
                            <button
                                type="submit"
                                :disabled="replyForm.processing"
                                class="bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700 disabled:opacity-50"
                            >
                                Post Reply
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </AppLayout>
</template>