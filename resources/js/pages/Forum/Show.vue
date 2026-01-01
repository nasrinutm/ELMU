<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, Link, router, usePage } from '@inertiajs/vue3';
import { type BreadcrumbItem, type Post, type Reply as ReplyType } from '@/types';
import { route } from 'ziggy-js';
import Reply from '@/components/Reply.vue';
import { ref, computed, watch, nextTick } from 'vue';
import { Trash2, Pencil, CheckCircle2, AlertCircle, X } from 'lucide-vue-next';

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
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Forum', href: route('forum.index') },
    { title: 'View Post', href: '#' },
];

// --- NOTIFICATION & MODAL STATE ---
const page = usePage();
const flashSuccess = computed(() => (page.props as any).flash?.success);
const showSuccessNotification = ref(false);
const isDeleteModalOpen = ref(false);

// --- FLASH WATCHER ---
watch(flashSuccess, async (newVal) => {
    if (newVal) {
        showSuccessNotification.value = false;
        await nextTick();
        showSuccessNotification.value = true;
        setTimeout(() => { showSuccessNotification.value = false; }, 5000);
    }
}, { immediate: true });

const replyForm = useForm({
    body: '',
    post_id: props.post.id,
    parent_id: null as number | null,
});

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
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-emerald-500">Forum Update</p>
                        <p class="text-sm font-medium">{{ flashSuccess }}</p>
                    </div>
                    <button @click="showSuccessNotification = false" class="text-slate-500 hover:text-white transition"><X class="w-4 h-4" /></button>
                </div>
            </transition>

            <div class="pb-8 mx-4 border-b border-slate-200 mb-8">
                <div class="flex justify-between items-start gap-6">
                    <h1 class="text-4xl font-black text-slate-900 tracking-tighter break-words whitespace-pre-wrap min-w-0 flex-1 uppercase">
                        {{ post.title }}
                    </h1>
                    
                    <div v-if="props.post.can_update || props.post.can_delete" class="flex items-center gap-1">
                        <Link v-if="props.post.can_update" :href="route('forum.edit', props.post.id)" class="p-2 text-slate-400 hover:text-blue-600 transition-colors">
                            <Pencil class="w-6 h-6" />
                        </Link>
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
            </div>

            <div class="bg-slate-50 rounded-none p-8 border border-slate-200 mx-4 shadow-sm">
                <h2 class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 mb-6">Join the Discussion</h2>
                <form @submit.prevent="submitReply">
                    <textarea
                        v-model="replyForm.body"
                        rows="4"
                        class="w-full p-4 rounded-none border border-slate-300 bg-white text-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-all placeholder:text-slate-300 shadow-inner"
                        placeholder="Type your reply here..."
                    ></textarea>            
                    <div class="flex justify-end mt-6">
                        <button type="submit" :disabled="replyForm.processing" class="px-10 py-4 text-[10px] font-bold uppercase tracking-widest text-white bg-slate-900 hover:bg-teal-700 disabled:opacity-50 transition-all shadow-xl rounded-none">
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
            <div class="bg-white max-w-sm w-full p-8 border border-slate-200 rounded-none shadow-2xl animate-in zoom-in duration-200">
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
</style>