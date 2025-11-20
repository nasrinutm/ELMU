<script setup lang="ts">
import { ref } from 'vue';
import { type Reply as ReplyType } from '@/types';
import { useForm, router } from '@inertiajs/vue3';
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

const showEditForm = ref(false);

// Form for editing *this* reply
const editForm = useForm({
    body: props.reply.body,
});

const submitEdit = () => {
    // Sends a PUT request to your ForumController@updateReply
    editForm.put(route('replies.update', props.reply.id), {
        preserveScroll: true,
        onSuccess: () => showEditForm.value = false, // Hide form on success
    });
};

const deleteReply = () => {
    if (confirm('Are you sure you want to delete this reply?')) {
        // Sends a DELETE request to your ForumController@destroyReply
        router.delete(route('replies.destroy', props.reply.id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md">
        <div class="p-4">
            <div class="text-sm font-semibold mb-2">
                @{{ reply.user.username }}
            </div>
            
            <form v-if="showEditForm" @submit.prevent="submitEdit" class="mt-3">
                <textarea
                    v-model="editForm.body"
                    rows="3"
                    class="w-full rounded border px-3 py-2"
                    placeholder="Write your reply..."
                ></textarea>
                <p v-if="editForm.errors.body" class="text-red-500 text-sm mt-1">
                    {{ editForm.errors.body }}
                </p>
                <div class="flex justify-end items-center mt-2 space-x-2">
                    <button 
                        type="button" 
                        @click="showEditForm = false" 
                        class="text-sm font-medium text-gray-600"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="editForm.processing"
                        class="bg-blue-600 text-white font-bold py-2 px-3 rounded text-sm"
                    >
                        Save
                    </button>
                </div>
            </form>
            <p>{{ reply.body }}</p>

            <button 
                @click="showReplyForm = !showReplyForm"
                class="text-sm text-blue-600 dark:text-blue-400 mt-2"
            >
                {{ showReplyForm ? 'Cancel' : 'Reply' }}
            </button>

            <button
                    v-if="reply.can_update"
                    @click="showEditForm = true"
                    class="text-sm text-gray-500 hover:underline"
                >
                    Edit
                </button>
                <button
                    v-if="reply.can_delete"
                    @click="deleteReply"
                    class="text-sm text-red-500 hover:underline"
                >
                    Delete
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