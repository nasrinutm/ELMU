<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { route } from 'ziggy-js';
import { ref, watch, nextTick } from 'vue';
import { CheckCircle2, X, Send } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Forum', href: route('forum.index') },
    { title: 'Create Post', href: '#' },
];

const page = usePage();
const showSuccessNotification = ref(false);

const form = useForm({
    title: '',
    content: '',
});

// --- FLASH WATCHER (Consistent with Show.vue) ---
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

const submit = () => {
    form.post(route('forum.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Create New Post" />

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

            <div class="max-w-4xl mx-auto">
                <div class="pb-8 border-b border-slate-200 mb-12">
                    <h1 class="text-4xl font-black text-slate-900 tracking-tighter uppercase mb-2">
                        Start a Conversation
                    </h1>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">
                        Share your thoughts with the community
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-10">
                    <div class="group">
                        <label for="title" class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 block mb-4 group-focus-within:text-slate-900 transition-colors">
                            Discussion Title
                        </label>
                        <input
                            id="title"
                            v-model="form.title"
                            type="text"
                            class="w-full text-3xl font-black text-slate-900 tracking-tighter uppercase border-b-2 border-slate-200 bg-transparent outline-none focus:border-slate-900 transition-all py-2 placeholder:text-slate-100"
                            placeholder="What's on your mind?"
                            required
                        />
                        <p v-if="form.errors.title" class="text-red-500 text-[10px] font-bold uppercase mt-2 tracking-widest">
                            {{ form.errors.title }}
                        </p>
                    </div>

                    <div class="group">
                        <label for="content" class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 block mb-4 group-focus-within:text-slate-900 transition-colors">
                            Content
                        </label>
                        <textarea
                            id="content"
                            v-model="form.content" 
                            rows="10"
                            class="w-full text-lg font-medium text-slate-700 leading-relaxed border border-slate-200 p-6 focus:ring-1 focus:ring-slate-900 outline-none transition-all bg-slate-50 shadow-inner placeholder:text-slate-300"
                            placeholder="Provide details for your discussion..."
                            required
                        ></textarea>
                        <p v-if="form.errors.content" class="text-red-500 text-[10px] font-bold uppercase mt-2 tracking-widest">
                            {{ form.errors.content }}
                        </p>
                    </div>

                    <div class="flex justify-end items-center gap-6">
                        <Link :href="route('forum.index')" class="text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-slate-900 transition">
                            Discard
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="group flex items-center gap-3 bg-slate-900 text-white font-bold py-5 px-10 text-[10px] uppercase tracking-[0.2em] hover:bg-teal-700 disabled:opacity-50 transition-all shadow-2xl"
                        >
                            <Send class="w-4 h-4 group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform" />
                            {{ form.processing ? 'Publishing...' : 'Publish Thread' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.toast-enter-active, .toast-leave-active { transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.toast-enter-from { transform: translateX(100%); opacity: 0; }
.toast-leave-to { transform: translateY(-20px); opacity: 0; }
</style>