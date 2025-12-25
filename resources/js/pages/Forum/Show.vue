<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { type BreadcrumbItem, type Post, type Reply as ReplyType } from '@/types';
import { route } from 'ziggy-js';
import Reply from '@/components/Reply.vue';
import { ref } from 'vue'; // Import ref for modal state
import { Trash2 } from 'lucide-vue-next'; // Import Trash2 icon

const props = defineProps<{
    post: Post & {
        user: { name: string, username: string };
        replies: ReplyType[];
        content: string;
        // Assumed properties for authorization check
        can_update: boolean; 
        can_delete: boolean;
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

// --- CUSTOM MODAL STATE ---
const isDeleteModalOpen = ref(false);

// 1. Function to open the custom confirmation modal
const openDeleteModal = () => {
    isDeleteModalOpen.value = true;
};

// 2. Function to handle the actual deletion when confirmed
const confirmDelete = () => {
    isDeleteModalOpen.value = false;
    // Perform the action (deletion)
    router.delete(route('forum.destroy', props.post.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Redirect or show a success message after deletion
            router.get(route('forum.index')); 
        }
    });
};
// --- END CUSTOM MODAL LOGIC ---

const submitReply = () => {
    replyForm.post(route('replies.store'), {
        preserveScroll: true,
        onSuccess: () => replyForm.reset(),
    });
};

// REMOVED THE OLD deletePost FUNCTION, replaced by openDeleteModal and confirmDelete
</script>

<template>
    <Head :title="post.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full mx-auto p-4">

            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden mb-6">
                <div class="p-6">
                    <h1 class="text-3xl font-bold mb-2 text-black">{{ post.title }}</h1>
                    
                    <div class="flex justify-between items-center mb-4 border-b border-gray-100 pb-4">
                        <div class="text-sm text-gray-600">
                            Posted by <span class="font-semibold text-black">@{{ post.user.username }}</span>
                        </div>
                        
                        <div v-if="props.post.can_update || props.post.can_delete" class="flex space-x-3 text-sm">
                            <Link
                                v-if="props.post.can_update"
                                :href="route('forum.edit', props.post.id)"
                                class="font-medium text-blue-600 hover:underline"
                            >
                                Edit
                            </Link>
                            <button
                                v-if="props.post.can_delete"
                                @click="openDeleteModal"
                                class="font-medium text-red-600 hover:underline"
                            >
                                Delete
                            </button>
                        </div>
                    </div>

                    <div class="prose max-w-none text-black leading-relaxed">
                        <p>{{ post.content }}</p>
                    </div>
                </div>
            </div>

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

                <div class="mt-6 bg-white rounded-xl shadow-md border border-gray-200 p-6">
                    <form @submit.prevent="submitReply">
                        <label for="body" class="block mb-2 text-sm font-medium text-black">
                            Write a Reply
                        </label>
                        
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
        
        <div v-if="isDeleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60">
            <div class="bg-white rounded-lg shadow-2xl p-6 max-w-sm w-full">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <Trash2 class="w-6 h-6 text-red-500" />
                    ELMU - Confirm Deletion
                </h2>
                <p class="mt-4 text-gray-700">
                    Are you sure you want to permanently delete the post titled **"{{ post.title }}"**?
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button 
                        @click="isDeleteModalOpen = false" 
                        class="px-4 py-2 text-sm font-medium border border-gray-300 rounded-lg hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button 
                        @click="confirmDelete" 
                        class="px-4 py-2 text-sm font-medium bg-red-600 text-white rounded-lg hover:bg-red-700"
                    >
                        OK, Delete Post
                    </button>
                </div>
            </div>
        </div>
        </AppLayout>
</template>