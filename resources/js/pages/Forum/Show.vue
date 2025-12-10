<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { type BreadcrumbItem, type Post, type Reply as ReplyType } from '@/types';
import { route } from 'ziggy-js';
import Reply from '@/components/Reply.vue';

const props = defineProps<{
    post: Post & {
        user: { name: string, username: string };
        replies: ReplyType[];
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Forum', href: route('forum.index') },
    { title: 'Post', href: '#' },
];

const replyForm = useForm({
    body: '',
    post_id: props.post.id,
    parent_id: null as number | null,
});

const submitReply = () => {
    replyForm.post(route('replies.store'), {
        preserveScroll: true,
        onSuccess: () => replyForm.reset(),
    });
};

const deletePost = () => {
    if (confirm('Are you sure you want to delete this post?')) {
        router.delete(route('forum.destroy', props.post.id));
    }
};
</script>

<template>
    <Head :title="post.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full mx-auto p-4">

            <!-- MAIN TOPIC POST -->
            <!-- Changed: bg-white, text-black, added border to match Reply.vue style -->
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden mb-6">
                <div class="p-6">
                    <h1 class="text-3xl font-bold mb-2 text-black">{{ post.title }}</h1>
                    
                    <div class="flex justify-between items-center mb-4 border-b border-gray-100 pb-4">
                        <!-- Left Side: "Posted by" -->
                        <div class="text-sm text-gray-600">
                            Posted by <span class="font-semibold text-black">@{{ post.user.username }}</span>
                        </div>
                        
                        <!-- Right Side: "Edit/Delete" buttons -->
                        <div v-if="post.can_update || post.can_delete" class="flex space-x-3 text-sm">
                            <Link
                                v-if="post.can_update"
                                :href="route('forum.edit', post.id)"
                                class="font-medium text-blue-600 hover:underline"
                            >
                                Edit
                            </Link>
                            <button
                                v-if="post.can_delete"
                                @click="deletePost"
                                class="font-medium text-red-600 hover:underline"
                            >
                                Delete
                            </button>
                        </div>
                    </div>

                    <!-- Post Body: Enforce black text -->
                    <div class="prose max-w-none text-black leading-relaxed">
                        <p>{{ post.body }}</p>
                    </div>
                </div>
            </div>

            <!-- REPLIES SECTION -->
            <div class="mt-6">
                <h2 class="text-xl font-bold mb-4 text-black">Replies</h2>

                <div class="space-y-4">
                    <Reply 
                        v-for="reply in post.replies"
                        :key="reply.id"
                        :reply="reply"
                        :post_id="post.id"
                    />
                </div>

                <!-- MAIN REPLY FORM -->
                <!-- Changed: bg-white container with light inputs -->
                <div class="mt-6 bg-white rounded-xl shadow-md border border-gray-200 p-6">
                    <form @submit.prevent="submitReply">
                        <label for="body" class="block mb-2 text-sm font-medium text-black">
                            Write a Reply
                        </label>
                        
                        <!-- Textarea: Light gray background, black text, blue focus ring -->
                        <textarea
                            id="body"
                            v-model="replyForm.body"
                            rows="5"
                            class="w-full p-4 rounded-lg border border-gray-300 bg-gray-50 text-black focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all shadow-inner"
                            placeholder="Share your thoughts..."
                        ></textarea>
                        
                        <p v-if="replyForm.errors.body" class="text-red-500 text-sm mt-2 font-medium">
                            {{ replyForm.errors.body }}
                        </p>

                        <div class="flex justify-end mt-4">
                            <button
                                type="submit"
                                :disabled="replyForm.processing"
                                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-md"
                            >
                                <span v-if="replyForm.processing">Posting...</span>
                                <span v-else>Post Reply</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>