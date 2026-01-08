<script setup lang="ts">
import { ref, watch, nextTick, computed } from 'vue';
import { type Reply as ReplyType } from '@/types';
import { useForm, router, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { Trash2, Reply as ReplyIcon, Pencil, AlertCircle, CheckCircle2, X } from 'lucide-vue-next';

const props = defineProps<{
    reply: ReplyType & {
        user: { name: string, username: string };
        children: ReplyType[]; 
        can_update: boolean;
        can_delete: boolean; 
    };
    post_id: number;
}>();

const page = usePage();
const showReplyForm = ref(false);
const showEditForm = ref(false);
const isDeleteModalOpen = ref(false);
const showSuccessNotification = ref(false);

// --- VALIDATION LIMITS ---
const limits = {
    max: 2500 // Only enforcing the ceiling
};

// --- FORMS ---
const nestedReplyForm = useForm({
    body: '',
    post_id: props.post_id,
    parent_id: props.reply.id, 
});

const editForm = useForm({ 
    body: props.reply.body 
});

// --- COMPUTED VALIDATION ---
// Valid if not empty/whitespace and under the limit
const isEditValid = computed(() => {
    return editForm.body.trim().length > 0 && editForm.body.length <= limits.max;
});

const isNestedValid = computed(() => {
    return nestedReplyForm.body.trim().length > 0 && nestedReplyForm.body.length <= limits.max;
});

// --- NOTIFICATION WATCHER ---
watch(
    () => (page.props as any).flash,
    (flash) => {
        if (flash?.success) {
            showSuccessNotification.value = false;
            nextTick(() => {
                showSuccessNotification.value = true;
                setTimeout(() => { showSuccessNotification.value = false; }, 5000);
            });
        }
    },
    { deep: true }
);

// --- ACTIONS ---
const confirmDelete = () => {
    isDeleteModalOpen.value = false;
    router.delete(route('replies.destroy', props.reply.id), { preserveScroll: true });
};

const submitNestedReply = () => {
    if (!isNestedValid.value) return;
    nestedReplyForm.post(route('replies.store'), {
        preserveScroll: true,
        onSuccess: () => {
            nestedReplyForm.reset();
            showReplyForm.value = false; 
        },
    });
};

const submitEdit = () => {
    if (!isEditValid.value) return;
    editForm.put(route('replies.update', props.reply.id), {
        preserveScroll: true,
        onSuccess: () => showEditForm.value = false,
    });
};
</script>

<template>
    <div class="border-b border-slate-100 py-8 last:border-0 group">
        <div class="relative"> 
            
            <transition name="toast">
                <div v-if="showSuccessNotification" class="fixed top-10 right-10 z-[100] flex items-center gap-4 bg-slate-900 text-white p-5 shadow-2xl border-l-4 border-emerald-500 min-w-[350px]">
                    <div class="bg-emerald-500/20 p-2"><CheckCircle2 class="w-6 h-6 text-emerald-500" /></div>
                    <div class="flex-grow">
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-emerald-500">System Notification</p>
                        <p class="text-sm font-medium">{{ (page.props as any).flash.success }}</p>
                    </div>
                    <button @click="showSuccessNotification = false" class="text-slate-500 hover:text-white transition"><X class="w-4 h-4" /></button>
                </div>
            </transition>

            <div class="flex items-center justify-between mb-3">
                <div class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-900 bg-slate-50 px-2 py-0.5 border border-slate-100">
                    @{{ reply.user.username }}
                </div>
            </div>
            
            <form v-if="showEditForm" @submit.prevent="submitEdit" class="mt-4 space-y-4 bg-slate-50 p-6 border border-slate-200">
                <div class="flex justify-end items-end mb-2">
                    <span :class="[
                        editForm.body.length === 0 ? 'text-slate-300' :
                        editForm.body.length > limits.max ? 'text-red-500' : 'text-emerald-500'
                    ]" class="text-[9px] font-mono font-bold transition-colors">
                        {{ editForm.body.length }} / {{ limits.max }}
                    </span>
                </div>
                
                <textarea
                    v-model="editForm.body"
                    rows="3"
                    :maxlength="limits.max"
                    class="w-full p-4 rounded-none border bg-white text-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-sm shadow-inner transition-all"
                    :class="editForm.errors.body ? 'border-red-500' : 'border-slate-300'"
                    required
                ></textarea>
                
                <div v-if="editForm.errors.body" class="flex items-center gap-2 text-red-500">
                    <AlertCircle class="w-3 h-3" />
                    <p class="text-[10px] font-bold uppercase tracking-widest">{{ editForm.errors.body }}</p>
                </div>
                
                <div class="flex justify-end items-center gap-4">
                    <button type="button" @click="showEditForm = false" class="text-[10px] font-bold uppercase text-slate-400 hover:text-slate-600 transition-colors">Cancel</button>
                    <button type="submit" :disabled="editForm.processing || !isEditValid" class="px-6 py-2 bg-slate-900 text-white text-[10px] font-bold uppercase tracking-widest rounded-none shadow-md disabled:opacity-30 disabled:cursor-not-allowed transition-all">
                        {{ editForm.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>

            <div v-else>
                <div class="text-slate-700 leading-relaxed text-[15px] break-words whitespace-pre-wrap font-medium">
                    {{ reply.body }}
                </div>

                <div class="flex justify-between items-center mt-6 h-6">
                    <button @click="showReplyForm = !showReplyForm" class="flex items-center gap-2 text-teal-600 hover:text-slate-900 transition-colors">
                        <ReplyIcon class="w-6 h-6" />
                        <span class="text-sm font-bold uppercase tracking-widest">
                            {{ showReplyForm ? 'Cancel' : 'Reply' }}
                        </span>
                    </button>

                    <div v-if="props.reply.can_update || props.reply.can_delete" class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button v-if="props.reply.can_update" @click="showEditForm = true" class="p-2 text-slate-300 hover:text-blue-600 transition-colors">
                            <Pencil class="w-6 h-6" />
                        </button>
                        <button v-if="props.reply.can_delete" @click="isDeleteModalOpen = true" class="p-2 text-slate-300 hover:text-red-600 transition-colors">
                            <Trash2 class="w-6 h-6" />
                        </button>
                    </div>
                </div>
            </div>

            <form v-if="showReplyForm" @submit.prevent="submitNestedReply" class="mt-6 pl-6 border-l-2 border-slate-900 animate-in slide-in-from-left-2 duration-200">
                <div class="flex justify-end items-end mb-2">
                    <span :class="[
                        nestedReplyForm.body.length === 0 ? 'text-slate-300' :
                        nestedReplyForm.body.length > limits.max ? 'text-red-500' : 'text-emerald-500'
                    ]" class="text-[9px] font-mono font-bold transition-colors">
                        {{ nestedReplyForm.body.length }} / {{ limits.max }}
                    </span>
                </div>
                <textarea
                    v-model="nestedReplyForm.body"
                    rows="2"
                    :maxlength="limits.max"
                    class="w-full p-4 rounded-none border border-slate-300 bg-white text-slate-900 text-sm outline-none focus:ring-1 focus:ring-slate-900 shadow-inner"
                    placeholder="Write a response..."
                    required
                ></textarea>
                <div class="flex justify-end mt-3">
                    <button type="submit" :disabled="nestedReplyForm.processing || !isNestedValid" class="px-6 py-2 bg-slate-900 text-white text-[10px] font-bold uppercase tracking-widest shadow-md disabled:opacity-30 disabled:cursor-not-allowed transition-all">
                        Post Response
                    </button>
                </div>
            </form>

            <div v-if="props.reply.children && props.reply.children.length > 0" class="mt-6 ml-4 pl-8 border-l border-slate-100">
                <Reply v-for="childReply in props.reply.children" :key="childReply.id" :reply="childReply" :post_id="props.post_id" />
            </div>
        </div>
    </div>

    <div v-if="isDeleteModalOpen" class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white max-w-sm w-full p-8 border border-slate-200 rounded-none shadow-2xl animate-in zoom-in duration-200">
            <h3 class="text-sm font-bold uppercase tracking-[0.2em] text-slate-900 mb-2 flex items-center gap-2">
                <AlertCircle class="w-5 h-5 text-red-500" /> Remove Reply
            </h3>
            <p class="text-sm text-slate-500 font-medium mb-8 leading-relaxed italic">
                Permanently delete this response from the thread? This cannot be undone.
            </p>
            <div class="flex gap-3">
                <button @click="isDeleteModalOpen = false" class="flex-1 py-3 text-[10px] font-bold uppercase tracking-widest text-slate-400 border border-slate-100 hover:bg-slate-50 transition">Cancel</button>
                <button @click="confirmDelete" class="flex-1 py-3 bg-red-600 text-white text-[10px] font-bold uppercase tracking-widest hover:bg-red-700 shadow-lg transition">Delete</button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.toast-enter-active, .toast-leave-active { transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.toast-enter-from { transform: translateX(100%); opacity: 0; }
.toast-leave-to { transform: translateY(-20px); opacity: 0; }
</style>