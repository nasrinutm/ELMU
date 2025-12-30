<script setup lang="ts">
import { ref } from 'vue';
import { type Reply as ReplyType } from '@/types';
import { useForm, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { Trash2, Reply as ReplyIcon, Pencil } from 'lucide-vue-next';

// Props
const props = defineProps<{
    reply: ReplyType & {
        user: { name: string, username: string };
        children: ReplyType[]; 
        can_update: boolean;
        can_delete: boolean; 
    };
    post_id: number;
}>();

// State
const showReplyForm = ref(false);
const showEditForm = ref(false);
const isDeleteModalOpen = ref(false);

// Deletion Logic
const openDeleteModal = () => {
    isDeleteModalOpen.value = true;
};

const confirmDelete = () => {
    isDeleteModalOpen.value = false;
    router.delete(route('replies.destroy', props.reply.id), {
        preserveScroll: true,
    });
};

// Form for nested replies
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

// Form for editing
const editForm = useForm({
    body: props.reply.body,
});

const submitEdit = () => {
    editForm.put(route('replies.update', props.reply.id), {
        preserveScroll: true,
        onSuccess: () => showEditForm.value = false,
    });
};
</script>

<template>
    <div class="border-b border-gray-300 py-4 last:border-0">
        <div> 
            <div class="flex items-center justify-between mb-0.5">
                <div class="font-bold text-sm text-black">
                    @{{ reply.user.username }}
                </div>
            </div>
            
            <form v-if="showEditForm" @submit.prevent="submitEdit" class="mt-2 space-y-3">
                <textarea
                    v-model="editForm.body"
                    rows="3"
                    class="w-full p-3 rounded-lg border border-gray-300 bg-gray-50 text-black focus:ring-2 focus:ring-blue-500 outline-none text-sm"
                ></textarea>
                <div class="flex justify-end items-center gap-2">
                    <button type="button" @click="showEditForm = false" class="text-sm text-gray-500">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">Save</button>
                </div>
            </form>

            <div v-else>
                <p class="text-gray-800 leading-snug text-[15px] break-words whitespace-pre-wrap">
                    {{ reply.body }}
                </p>

                <div class="flex justify-between mt-2">
                    <button 
                        @click="showReplyForm = !showReplyForm"
                        class="flex items-center gap-1.5 text-blue-600 hover:text-blue-800 transition-colors h-6"
                        title="Reply"
                    >
                        <ReplyIcon class="w-4 h-4" />
                        <span class="text-xs font-bold uppercase tracking-tight">
                            {{ showReplyForm ? 'Cancel' : 'Reply' }}
                        </span>
                    </button>

                    <div v-if="props.reply.can_update || props.reply.can_delete" class="flex items-center gap-3 h-6">
                        <button
                            v-if="props.reply.can_update"
                            @click="showEditForm = true"
                            class="p-1 text-gray-500 hover:text-blue-600 transition-colors"
                            title="Edit"
                        >
                            <Pencil class="w-5 h-5" />
                        </button>

                        <button
                            v-if="props.reply.can_delete"
                            @click="openDeleteModal"
                            class="p-1 text-red-600 transition-colors"
                            title="Delete"
                        >
                            <Trash2 class="w-5 h-5" />
                        </button>
                    </div>
                </div>
            </div>

            <form v-if="showReplyForm" @submit.prevent="submitNestedReply" class="mt-3 pl-4 border-l-2 border-blue-500">
                <textarea
                    v-model="nestedReplyForm.body"
                    rows="2"
                    class="w-full p-3 rounded-lg border border-gray-200 bg-white text-black text-sm outline-none focus:ring-1 focus:ring-blue-500"
                    placeholder="Write a reply..."
                ></textarea>
                <div class="flex justify-end mt-2">
                    <button type="submit" :disabled="nestedReplyForm.processing" class="px-4 py-1 bg-blue-600 text-white text-xs font-bold rounded-md hover:bg-blue-700">
                        Post Reply
                    </button>
                </div>
            </form>

            <div v-if="props.reply.children && props.reply.children.length > 0" class="mt-1 ml-4 pl-4 border-l-2 border-gray-100">
                <Reply
                    v-for="childReply in props.reply.children"
                    :key="childReply.id"
                    :reply="childReply"
                    :post_id="props.post_id"
                />
            </div>
        </div>
    </div>

    <div v-if="isDeleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-lg shadow-2xl p-6 max-w-sm w-full border border-gray-100">
            <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <Trash2 class="w-6 h-6 text-red-500" />
                Confirm Deletion
            </h2>
            <p class="mt-4 text-gray-700">
                Are you sure you want to permanently delete this reply?
            </p>
            <div class="mt-6 flex justify-end gap-3">
                <button 
                    type="button"
                    @click="isDeleteModalOpen = false" 
                    class="px-4 py-2 text-sm font-medium border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
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
