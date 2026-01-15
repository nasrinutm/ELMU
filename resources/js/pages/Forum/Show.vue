<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, Link, router, usePage } from '@inertiajs/vue3';
import { type BreadcrumbItem, type Post, type Reply as ReplyType } from '@/types';
import { route } from 'ziggy-js';
import Reply from '@/components/Reply.vue';
import { ref, computed, watch, nextTick } from 'vue';
import { Trash2, Pencil, CheckCircle2, AlertCircle, X, Send } from 'lucide-vue-next';

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

// --- NOTIFICATION & MODAL STATE ---
const page = usePage();
const showSuccessNotification = ref(false);
const isDeleteModalOpen = ref(false);
const isEditing = ref(false);

// --- VALIDATION LIMITS ---
const limits = {
    title: 255,
    minContent: 10,
    maxContent: 5000
};

// --- FORMS ---
const replyForm = useForm({
    body: '',
    post_id: props.post.id,
    parent_id: null as number | null,
});

const editForm = useForm({
    title: props.post.title,
    content: props.post.content,
});

// --- VALIDATION LOGIC ---
const isEditValid = computed(() => {
    const titleValid = editForm.title.trim().length > 0 && editForm.title.length <= limits.title;
    const contentValid = editForm.content.trim().length >= limits.minContent && editForm.content.length <= limits.maxContent;
    return titleValid && contentValid;
});

// --- FLASH WATCHER ---
watch(
    () => (page.props as any).flash,
    (flash) => {
        if (flash?.success) {
            showSuccessNotification.value = false;
            nextTick(() => {
                showSuccessNotification.value = true;
                setTimeout(() => { 
                    showSuccessNotification.value = false; 
                }, 5000);
            });
        }
    },
    { deep: true, immediate: true }
);

// --- ACTIONS ---
const startEditing = () => {
    isEditing.value = true;
};

const cancelEditing = () => {
    isEditing.value = false;
    editForm.reset();
};

const submitUpdate = () => {
    if (!isEditValid.value) return;

    editForm.put(route('forum.update', props.post.id), {
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false;
        },
        onError: () => {
            nextTick(() => {
                const firstError = document.querySelector('.text-red-500');
                firstError?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        }
    });
};

const confirmDelete = () => {
    isDeleteModalOpen.value = false;
    router.delete(route('forum.destroy', props.post.id));
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
        <div class="w-full mx-auto py-8 px-6 relative min-h-screen bg-white">

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

            <div class="pb-8 mx-4 border-b border-slate-200 mb-8">
                
                <template v-if="!isEditing">
                    <div class="flex justify-between items-start gap-6">
                        <h1 class="text-4xl font-black text-slate-900 tracking-tighter break-words whitespace-pre-wrap min-w-0 flex-1 uppercase">
                            {{ post.title }}
                        </h1>
                        
                        <div v-if="props.post.can_update || props.post.can_delete" class="flex items-center gap-1">
                            <button v-if="props.post.can_update" @click="startEditing" class="p-2 text-slate-400 hover:text-blue-600 transition-colors">
                                <Pencil class="w-6 h-6" />
                            </button>
                            <button v-if="props.post.can_delete" @click="isDeleteModalOpen = true" class="p-2 text-slate-400 hover:text-red-600 transition-colors">
                                <Trash2 class="w-6 h-6" />
                            </button>
                        </div>
                    </div>
                    
                    <div class="flex items-center text-[10px] font-bold uppercase tracking-widest text-slate-400 mt-4">
                        Authored by <span class="ml-2 text-slate-900">@{{ post.user.username }}</span>
                    </div>

                    <div class="mt-8 text-slate-700 leading-relaxed text-lg font-medium max-w-none">
                        <p class="break-words whitespace-pre-wrap">{{ post.content }}</p>
                    </div>
                </template>

                <template v-else>
                    <form @submit.prevent="submitUpdate" class="space-y-10">
                        <div class="flex justify-end">
                            <div class="inline-flex items-center bg-slate-50 px-3 py-1 border border-slate-100">
                                <span class="text-red-500 font-bold mr-2">*</span>
                                <span class="text-[9px] font-bold uppercase tracking-widest text-slate-500 italic">Indicates required field</span>
                            </div>
                        </div>

                        <div class="group">
                            <div class="flex justify-between items-end mb-2">
                                <label for="edit-title" class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 group-focus-within:text-slate-900 transition-colors">
                                    Discussion Title <span class="text-red-500">*</span>
                                </label>
                                <span :class="[editForm.title.length > limits.title ? 'text-red-500' : 'text-slate-300']" class="text-[9px] font-mono font-bold">
                                    {{ editForm.title.length }} / {{ limits.title }}
                                </span>
                            </div>
                            <input 
                                id="edit-title"
                                v-model="editForm.title"
                                type="text"
                                :maxlength="limits.title"
                                class="w-full text-4xl font-black text-slate-900 tracking-tighter uppercase border-b-2 bg-transparent outline-none transition-colors py-2"
                                :class="editForm.errors.title ? 'border-red-500' : 'border-slate-900 focus:border-teal-500'"
                                placeholder="Edit Title..."
                            />
                            <div v-if="editForm.errors.title" class="flex items-center gap-2 mt-2 text-red-500">
                                <AlertCircle class="w-3 h-3" />
                                <p class="text-[10px] font-bold uppercase tracking-widest">{{ editForm.errors.title }}</p>
                            </div>
                        </div>

                        <div class="group">
                            <div class="flex justify-between items-end mb-4">
                                <label for="edit-content" class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 group-focus-within:text-slate-900 transition-colors">
                                    Content <span class="text-red-500">*</span>
                                    <span v-if="editForm.content.length > 0 && editForm.content.length < limits.minContent" class="text-amber-600 font-medium italic lowercase tracking-normal ml-2">
                                        (Minimum {{ limits.minContent }} characters required)
                                    </span>
                                </label>
                                <span :class="[
                                    editForm.content.length === 0 ? 'text-slate-300' :
                                    editForm.content.length < limits.minContent ? 'text-amber-500' : 
                                    editForm.content.length > limits.maxContent ? 'text-red-500' : 'text-emerald-500'
                                ]" class="text-[9px] font-mono font-bold transition-colors">
                                    {{ editForm.content.length }} / {{ limits.maxContent }}
                                </span>
                            </div>
                            <textarea 
                                id="edit-content"
                                v-model="editForm.content"
                                rows="8"
                                :maxlength="limits.maxContent"
                                class="w-full text-lg font-medium text-slate-700 leading-relaxed border p-6 focus:ring-1 focus:ring-slate-900 outline-none transition-all shadow-inner placeholder:text-slate-300"
                                :class="editForm.errors.content ? 'border-red-500 bg-red-50/30' : 'border-slate-200 bg-slate-50'"
                                placeholder="Edit content..."
                            ></textarea>
                            <div v-if="editForm.errors.content" class="flex items-center gap-2 mt-2 text-red-500">
                                <AlertCircle class="w-3 h-3" />
                                <p class="text-[10px] font-bold uppercase tracking-widest">{{ editForm.errors.content }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <button 
                                type="submit" 
                                :disabled="editForm.processing || !isEditValid"
                                class="px-8 py-4 text-[10px] font-bold uppercase tracking-widest text-white bg-slate-900 hover:bg-teal-700 transition shadow-lg disabled:opacity-30 disabled:cursor-not-allowed"
                            >
                                {{ editForm.processing ? 'Saving...' : 'Update Post' }}
                            </button>
                            <button 
                                type="button" 
                                @click="cancelEditing"
                                class="px-8 py-4 text-[10px] font-bold uppercase tracking-widest text-slate-500 border border-slate-200 hover:bg-slate-50 transition"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                </template>
            </div>

            <div v-if="!isEditing" class="bg-slate-50 rounded-none p-8 border border-slate-200 mx-4 shadow-sm">
                <h2 class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 mb-6">Join the Discussion</h2>
                <form @submit.prevent="submitReply">
                    <textarea
                        v-model="replyForm.body"
                        rows="4"
                        class="w-full p-4 rounded-none border border-slate-300 bg-white text-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-all placeholder:text-slate-300 shadow-inner"
                        placeholder="Type your reply here..."
                    ></textarea>            
                    <div class="flex justify-end mt-6">
                        <button type="submit" :disabled="replyForm.processing || !replyForm.body.trim()" class="px-10 py-4 text-[10px] font-bold uppercase tracking-widest text-white bg-slate-900 hover:bg-teal-700 disabled:opacity-50 transition-all shadow-xl rounded-none">
                            {{ replyForm.processing ? 'Sending...' : 'Post Reply' }}
                        </button>
                    </div>
                </form>
            </div>

            <div class="px-4 mt-12">
                <h2 class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 mb-8 flex items-center gap-3">
                    Active Thread <span class="h-[1px] flex-1 bg-slate-100"></span> <span class="text-slate-900">{{ post.replies.length }} Replies</span>
                </h2>
                <div class="space-y-2">
                    <Reply v-for="reply in post.replies" :key="reply.id" :reply="reply" :post_id="post.id" />
                </div>
            </div>
        </div>
        
        <div v-if="isDeleteModalOpen" class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white max-w-sm w-full p-8 border border-slate-200 rounded-none shadow-2xl">
                <h3 class="text-sm font-bold uppercase tracking-[0.2em] text-slate-900 mb-2 flex items-center gap-2">
                    <AlertCircle class="w-5 h-5 text-red-500" /> Remove Thread
                </h3>
                <p class="text-sm text-slate-500 font-medium mb-8 leading-relaxed italic">
                    Are you sure? Deleting this post will permanently remove all associated replies and discussion data.
                </p>
                <div class="flex gap-3">
                    <button @click="isDeleteModalOpen = false" class="flex-1 py-3 text-[10px] font-bold uppercase tracking-widest text-slate-400 border border-slate-100 hover:bg-slate-50 transition">Cancel</button>
                    <button @click="confirmDelete" class="flex-1 py-3 bg-red-600 text-white text-[10px] font-bold uppercase tracking-widest hover:bg-red-700 shadow-lg transition">Delete Post</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.toast-enter-active, .toast-leave-active { transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.toast-enter-from { transform: translateX(100%); opacity: 0; }
.toast-leave-to { transform: translateY(-20px); opacity: 0; }

textarea::-webkit-scrollbar { width: 4px; }
textarea::-webkit-scrollbar-track { background: transparent; }
textarea::-webkit-scrollbar-thumb { background: #e2e8f0; }
textarea:focus::-webkit-scrollbar-thumb { background: #0f172a; }
</style>