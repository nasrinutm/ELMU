<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { type BreadcrumbItem, type Post, type Reply as ReplyType } from '@/types';
import { route } from 'ziggy-js';
import Reply from '@/components/Reply.vue';
import { ref } from 'vue';
import { Trash2, Pencil } from 'lucide-vue-next';

const props = defineProps<{
    post: Post & {
        user: { name: string, username: string };
        replies: ReplyType[];
        content: string;
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

const openDeleteModal = () => {
    isDeleteModalOpen.value = true;
};

const confirmDelete = () => {
    isDeleteModalOpen.value = false;
    router.delete(route('forum.destroy', props.post.id), {
        preserveScroll: true,
        onSuccess: () => {
            router.get(route('forum.index')); 
        }
    });
};

const submitReply = () => {
    replyForm.post(route('replies.store'), {
        preserveScroll: true,
        onSuccess: () => replyForm.reset(),
    });
};
</script>

<template>
    <Head :title="post.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full mx-auto py-4 px-6">

            <div class="pb-5 mx-4 border-b border-gray-300 mb-5">
                <div class="flex justify-between items-start">
                    <h1 class="text-4xl font-extrabold text-black tracking-tight break-words whitespace-pre-wrap min-w-0 flex-1">
                        {{ post.title }}
                    </h1>
                    
                    <div v-if="props.post.can_update || props.post.can_delete" class="flex items-center gap-2">
                        <Link
                            v-if="props.post.can_update"
                            :href="route('forum.edit', props.post.id)"
                            class="p-2 text-gray-500 hover:text-blue-600 transition-colors"
                            title="Edit Post"
                        >
                            <Pencil class="w-5 h-5" />
                        </Link>
                        <button
                            v-if="props.post.can_delete"
                            @click="openDeleteModal"
                            class="p-2 text-red-600 transition-colors"
                            title="Delete Post"
                        >
                            <Trash2 class="w-5 h-5" />
                        </button>
                    </div>
                </div>
                
                <div class="flex items-center text-sm text-gray-500 mb-6 mt-2">
                    Posted by <span class="ml-1 font-bold text-black">@{{ post.user.username }}</span>
                </div>

                <div class="prose prose-lg max-w-none text-black leading-relaxed">
                    <p class="break-words whitespace-pre-wrap">{{ post.content }}</p>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 border-2 border-gray-300 mx-2">
                <h2 class="text-2xl font-bold mb-4">Leave a Reply</h2>
                <form @submit.prevent="submitReply">
                    <textarea
                        id="body"
                        v-model="replyForm.body"
                        rows="1"
                        class="w-full p-4 rounded-lg border border-gray-300 bg-gray-50 text-black focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                        placeholder="Share your thoughts..."
                    ></textarea>            
                    <p v-if="replyForm.errors.body" class="text-red-500 text-sm mt-2 font-medium">
                        {{ replyForm.errors.body }}
                    </p>
                    <div class="flex justify-end mt-4">
                        <button
                            type="submit"
                            :disabled="replyForm.processing"
                            class="px-6 py-2.5 text-sm font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-all shadow-md"
                        >
                            {{ replyForm.processing ? 'Posting...' : 'Post Reply' }}
                        </button>
                    </div>
                </form>
            </div>

            <div class="px-6">
                <div >
                    <h2 class="text-2xl font-bold mt-4">Replies</h2>
                    <Reply 
                        v-for="reply in post.replies"
                        :key="reply.id"
                        :reply="reply"
                        :post_id="post.id"
                    />
                </div>

            </div>
        </div>
        
        <div v-if="isDeleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-white rounded-xl shadow-2xl p-8 max-w-md w-full border border-gray-100 mx-4">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-3 bg-red-100 rounded-full">
                        <Trash2 class="w-6 h-6 text-red-600" />
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">Confirm Deletion</h2>
                </div>
                
                <p class="text-gray-600 leading-relaxed">
                    Are you sure you want to permanently delete this post? This action cannot be undone.
                </p>

                <div class="mt-8 flex justify-end gap-3">
                    <button 
                        @click="isDeleteModalOpen = false" 
                        class="px-5 py-2 text-sm font-bold text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        Cancel
                    </button>
                    <button 
                        @click="confirmDelete" 
                        class="px-5 py-2 text-sm font-bold text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors shadow-lg shadow-red-100"
                    >
                        Yes, Delete Post
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>