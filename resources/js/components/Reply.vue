<script setup lang="ts">
import { ref } from 'vue';
import { type Reply as ReplyType } from '@/types';
import { useForm, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { Trash2 } from 'lucide-vue-next'; // Import Trash2 icon

// Props
const props = defineProps<{
    reply: ReplyType & {
        user: { name: string, username: string };
        children: ReplyType[]; 
        // Assuming 'can_update' and 'can_delete' are passed on the reply object
        can_update: boolean;
        can_delete: boolean; 
    };
    post_id: number;
}>();

// State
const showReplyForm = ref(false);
const showEditForm = ref(false);

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
    router.delete(route('replies.destroy', props.reply.id), {
        preserveScroll: true,
    });
};
// --- END CUSTOM MODAL LOGIC ---


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

// REMOVED THE OLD deleteReply FUNCTION, replaced by openDeleteModal and confirmDelete
</script>

<template>
    <div class="bg-white rounded-xl shadow-md border border-gray-200 mb-4">
        <div class="p-5">
            <div class="flex items-center justify-between mb-2">
                <div class="font-semibold text-black">
                    @{{ reply.user.username }}
                </div>
                <span class="text-xs text-gray-500">
                    </span>
            </div>
            
            <form v-if="showEditForm" @submit.prevent="submitEdit" class="mt-2 space-y-3">
                <textarea
                    v-model="editForm.body"
                    rows="3"
                    autocomplete="off"
                    spellcheck="false"
                    data-1p-ignore                        
                    data-lpignore="true"
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

            <div v-else>
                <p class="text-black leading-relaxed">
                    {{ reply.body }}
                </p>

                <div class="flex items-center gap-4 mt-4 text-sm font-medium">
                    
                    <button 
                        @click="showReplyForm = !showReplyForm"
                        class="text-blue-600 hover:text-blue-700 transition-colors"
                    >
                        {{ showReplyForm ? 'Cancel Reply' : 'Reply' }}
                    </button>

                    <template v-if="props.reply.can_update || props.reply.can_delete">
                        
                        <button
                            v-if="props.reply.can_update"
                            @click="showEditForm = true"
                            class="text-gray-500 hover:text-gray-700 transition-colors"
                        >
                            Edit
                        </button>

                        <button
                            v-if="props.reply.can_delete"
                            @click="openDeleteModal"
                            class="text-red-600 hover:text-red-700 transition-colors"
                        >
                            Delete
                        </button>
                    </template>
                </div>
            </div>

            <form v-if="showReplyForm" @submit.prevent="submitNestedReply" class="mt-4 pl-4 border-l-2 border-blue-100">
                <textarea
                    v-model="nestedReplyForm.body"
                    rows="3"
                    autocomplete="off"                       
                    spellcheck="false"
                    data-1p-ignore
                    data-lpignore="true"
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

            <div v-if="props.reply.children && props.reply.children.length > 0" class="mt-4 ml-2 pl-4 border-l-2 border-gray-100 space-y-4">
                <Reply
                    v-for="childReply in props.reply.children"
                    :key="childReply.id"
                    :reply="childReply"
                    :post_id="props.post_id"
                />
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
                Are you sure you want to permanently delete this reply?
            </p>
            <p class="italic text-gray-500 text-sm mt-2 max-h-16 overflow-hidden">
                (Content: "{{ props.reply.body.substring(0, 100) }}...")
            </p>
            <div class="mt-6 flex justify-end gap-3">
                <button 
                    type="button"
                    @click="isDeleteModalOpen = false" 
                    class="px-4 py-2 text-sm font-medium border border-gray-300 rounded-lg hover:bg-gray-50"
                >
                    Cancel
                </button>
                <button 
                    type="button"
                    @click="confirmDelete" 
                    class="px-4 py-2 text-sm font-medium bg-red-600 text-white rounded-lg hover:bg-red-700"
                >
                    OK, Delete Reply
                </button>
            </div>
        </div>
    </div>
    </template>