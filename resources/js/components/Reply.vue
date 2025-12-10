<script setup lang="ts">
import { ref } from 'vue';
import { type Reply as ReplyType } from '@/types';
import { useForm, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import Reply from '@/components/Reply.vue'; 

// Props
const props = defineProps<{
    reply: ReplyType & {
        user: { name: string, username: string };
        children: ReplyType[]; 
    };
    post_id: number;
}>();

// State
const showReplyForm = ref(false);
const showEditForm = ref(false);

// Form for replying to THIS reply
const nestedReplyForm = useForm({
    body: '',
    post_id: props.post_id,
    parent_id: props.reply.id, 
});

const submitNestedReply = () => {
    nestedReplyForm.post(route('replies.store'), {
        preserveScroll: true,
        onSuccess: () => {
            nestedReplyForm.reset();
            showReplyForm.value = false; 
        },
    });
};

// Form for editing THIS reply
const editForm = useForm({
    body: props.reply.body,
});

const submitEdit = () => {
    editForm.put(route('replies.update', props.reply.id), {
        preserveScroll: true,
        onSuccess: () => showEditForm.value = false,
    });
};

const deleteReply = () => {
    if (confirm('Are you sure you want to delete this reply?')) {
        router.delete(route('replies.destroy', props.reply.id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <!-- Changed: Removed dark:bg-gray-800 and dark:border-gray-700 -->
    <div class="bg-white rounded-xl shadow-md border border-gray-200 mb-4">
        <div class="p-5">
            <!-- Header: User info -->
            <div class="flex items-center justify-between mb-2">
                <!-- Changed: Force text-black -->
                <div class="font-semibold text-black">
                    @{{ reply.user.username }}
                </div>
                <!-- Changed: Removed dark:text-gray-400 -->
                <span class="text-xs text-gray-500">
                    <!-- You can add a date here later, e.g., reply.created_at -->
                </span>
            </div>
            
            <!-- MODE 1: EDITING -->
            <form v-if="showEditForm" @submit.prevent="submitEdit" class="mt-2 space-y-3">
                <!-- Changed: Input forced to light theme style -->
                <textarea
                    v-model="editForm.body"
                    rows="3"
                    class="w-full p-3 rounded-lg border border-gray-300 bg-gray-50 text-black focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all shadow-inner text-sm"
                    placeholder="Update your reply..."
                ></textarea>
                <p v-if="editForm.errors.body" class="text-red-500 text-xs font-medium">
                    {{ editForm.errors.body }}
                </p>
                <div class="flex justify-end items-center gap-2">
                    <button 
                        type="button" 
                        @click="showEditForm = false" 
                        class="px-3 py-1.5 text-sm font-medium text-gray-600 hover:text-gray-800 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="editForm.processing"
                        class="px-4 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors shadow-md"
                    >
                        Save
                    </button>
                </div>
            </form>

            <!-- MODE 2: VIEWING (Default) -->
            <div v-else>
                <!-- Changed: Force text-black -->
                <p class="text-black leading-relaxed">
                    {{ reply.body }}
                </p>

                <!-- ACTION BUTTONS ROW -->
                <div class="flex items-center gap-4 mt-4 text-sm font-medium">
                    
                    <!-- Reply Button -->
                    <button 
                        @click="showReplyForm = !showReplyForm"
                        class="text-blue-600 hover:text-blue-700 transition-colors"
                    >
                        {{ showReplyForm ? 'Cancel Reply' : 'Reply' }}
                    </button>

                    <!-- Edit/Delete Buttons (Only if authorized) -->
                    <template v-if="reply.can_update || reply.can_delete">
                        
                        <button
                            v-if="reply.can_update"
                            @click="showEditForm = true"
                            class="text-gray-500 hover:text-gray-700 transition-colors"
                        >
                            Edit
                        </button>

                        <button
                            v-if="reply.can_delete"
                            @click="deleteReply"
                            class="text-red-600 hover:text-red-700 transition-colors"
                        >
                            Delete
                        </button>
                    </template>
                </div>
            </div>

            <!-- NESTED REPLY FORM -->
            <!-- Changed: Border color fixed to light gray/blue -->
            <form v-if="showReplyForm" @submit.prevent="submitNestedReply" class="mt-4 pl-4 border-l-2 border-blue-100">
                <textarea
                    v-model="nestedReplyForm.body"
                    rows="3"
                    class="w-full p-3 rounded-lg border border-gray-300 bg-white text-black focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all text-sm shadow-sm"
                    placeholder="Write a reply..."
                ></textarea>
                <p v-if="nestedReplyForm.errors.body" class="text-red-500 text-xs mt-1 font-medium">
                    {{ nestedReplyForm.errors.body }}
                </p>
                <div class="flex justify-end mt-2">
                    <button
                        type="submit"
                        :disabled="nestedReplyForm.processing"
                        class="px-4 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors shadow-md"
                    >
                        Post Reply
                    </button>
                </div>
            </form>

            <!-- RECURSIVE CHILDREN (Nested Replies) -->
            <div v-if="reply.children && reply.children.length > 0" class="mt-4 ml-2 pl-4 border-l-2 border-gray-100 space-y-4">
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