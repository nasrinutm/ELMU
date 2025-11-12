<script setup lang="ts">
import { ref } from 'vue';
import { type Reply as ReplyType } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import Reply from '@/components/Reply.vue'; // <-- It imports itself!

// Props
const props = defineProps<{
    reply: ReplyType & {
        user: { name: string, username: string };
        children: ReplyType[]; // Nested children replies
    };
    post_id: number;
}>();

// State to show/hide the reply form for *this* comment
const showReplyForm = ref(false);

// Form for replying *to this reply*
const nestedReplyForm = useForm({
    body: '',
    post_id: props.post_id,
    parent_id: props.reply.id, // <-- This is the key for nesting
});

const submitNestedReply = () => {
    nestedReplyForm.post(route('replies.store'), {
        preserveScroll: true,
        onSuccess: () => {
            nestedReplyForm.reset();
            showReplyForm.value = false; // Hide the form
        },
    });
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md">
        <div class="p-4">
            <div class="text-sm font-semibold mb-2">
                @{{ reply.user.username }}
            </div>
            
            <p>{{ reply.body }}</p>

            <button 
                @click="showReplyForm = !showReplyForm"
                class="text-sm text-blue-600 dark:text-blue-400 mt-2"
            >
                {{ showReplyForm ? 'Cancel' : 'Reply' }}
            </button>

            <form v-if="showReplyForm" @submit.prevent="submitNestedReply" class="mt-3">
                <textarea
                    v-model="nestedReplyForm.body"
                    rows="3"
                    class="w-full rounded border px-3 py-2"
                    placeholder="Write your reply..."
                ></textarea>
                <p v-if="nestedReplyForm.errors.body" class="text-red-500 text-sm mt-1">
                    {{ nestedReplyForm.errors.body }}
                </p>
                <div class="flex justify-end mt-2">
                    <button
                        type="submit"
                        :disabled="nestedReplyForm.processing"
                        class="bg-blue-600 text-white font-bold py-2 px-4 rounded text-sm"
                    >
                        Post
                    </button>
                </div>
            </form>

            <div v-if="reply.children && reply.children.length > 0" class="mt-4 pl-4 border-l-2 border-gray-200 dark:border-gray-700 space-y-4">
                <Reply
                    v-for="childReply in reply.children"
                    :key="childReply.id"
                    :reply="childReply"
                    :post_id="post_id"
                />
            </div>
        </div>
    </div>
</template>