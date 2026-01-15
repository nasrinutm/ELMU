<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { route } from 'ziggy-js';
import { ref, watch, nextTick, computed } from 'vue';
import { CheckCircle2, X, Send, AlertCircle } from 'lucide-vue-next';

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

const limits = {
    title: 255,
    minContent: 10,
    maxContent: 5000
};

/**
 * STRICT VALIDATION LOGIC
 * The form is only valid if:
 * 1. Title is not empty and <= 255
 * 2. Content is >= 10 AND <= 5000
 */
const isValid = computed(() => {
    const titleValid = form.title.trim().length > 0 && form.title.length <= limits.title;
    const contentValid = form.content.trim().length >= limits.minContent && form.content.length <= limits.maxContent;
    return titleValid && contentValid;
});

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
    if (!isValid.value) return;

    form.post(route('forum.store'), {
        onSuccess: () => form.reset(),
        onError: () => {
            nextTick(() => {
                const firstError = document.querySelector('.text-red-500');
                firstError?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        }
    });
};
</script>

<template>
    <Head title="Create New Post" />

    <AppSidebarLayout :breadcrumbs="breadcrumbs">
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

            <div class="mx-auto">
                <div class="pb-8 border-b border-slate-200 mb-12 flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <h1 class="text-4xl font-black text-slate-900 tracking-tighter uppercase mb-2">Start a Conversation</h1>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Share your thoughts with the community</p>
                    </div>

                    <div class="inline-flex items-center bg-slate-50 px-3 py-2 border border-slate-100">
                        <span class="text-red-500 font-bold mr-2">*</span>
                        <span class="text-[9px] font-bold uppercase tracking-widest text-slate-500 italic">Indicates required field</span>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-10">
                    
                    <div class="group">
                        <div class="flex justify-between items-end mb-4">
                            <label for="title" class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 group-focus-within:text-slate-900 transition-colors">
                                Discussion Title <span class="text-red-500">*</span>
                            </label>
                            <span :class="[form.title.length > limits.title ? 'text-red-500' : 'text-slate-300']" class="text-[9px] font-mono font-bold">
                                {{ form.title.length }} / {{ limits.title }}
                            </span>
                        </div>
                        <textarea
                            id="title"
                            v-model="form.title"
                            rows="2"
                            :maxlength="limits.title"
                            :class="[
                                form.errors.title ? 'border-red-500 bg-red-50/30' : 'border-slate-200 bg-slate-50 focus:ring-slate-900',
                                'w-full text-lg font-medium text-slate-700 leading-relaxed border p-6 outline-none transition-all shadow-inner placeholder:text-slate-300'
                            ]"
                            placeholder="What's on your mind?"
                        ></textarea>
                        <p v-if="form.errors.title" class="text-red-500 text-[10px] font-bold uppercase mt-2 tracking-widest">{{ form.errors.title }}</p>
                    </div>

                    <div class="group">
                        <div class="flex justify-between items-end mb-4">
                            <label for="content" class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 group-focus-within:text-slate-900 transition-colors">
                                Content <span class="text-red-500">*</span>
                                <span v-if="form.content.length > 0 && form.content.length < limits.minContent" class="text-amber-600 font-medium italic lowercase tracking-normal ml-2">
                                    (Minimum {{ limits.minContent }} characters required)
                                </span>
                            </label>
                            <span :class="[
                                form.content.length === 0 ? 'text-slate-300' :
                                form.content.length < limits.minContent ? 'text-amber-500' : 
                                form.content.length > limits.maxContent ? 'text-red-500' : 'text-emerald-500'
                            ]" class="text-[9px] font-mono font-bold transition-colors">
                                {{ form.content.length }} / {{ limits.maxContent }}
                            </span>
                        </div>
                        <textarea
                            id="content"
                            v-model="form.content" 
                            rows="10"
                            :maxlength="limits.maxContent"
                            :class="[
                                form.errors.content ? 'border-red-500 bg-red-50/30' : 'border-slate-200 bg-slate-50 focus:ring-slate-900',
                                'w-full text-lg font-medium text-slate-700 leading-relaxed border p-6 outline-none transition-all shadow-inner placeholder:text-slate-300'
                            ]"
                            placeholder="Provide details for your discussion..."
                        ></textarea>
                        <p v-if="form.errors.content" class="text-red-500 text-[10px] font-bold uppercase mt-2 tracking-widest">{{ form.errors.content }}</p>
                    </div>

                    <div class="flex justify-end items-center gap-8 pt-4">
                        <Link :href="route('forum.index')" class="text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-red-500 transition-colors">
                            Discard Draft
                        </Link>
                        
                        <button
                            type="submit"
                            :disabled="form.processing || !isValid"
                            class="group flex items-center gap-3 bg-slate-900 text-white font-bold py-5 px-10 text-[10px] uppercase tracking-[0.2em] hover:bg-teal-700 disabled:opacity-30 disabled:cursor-not-allowed transition-all shadow-2xl"
                        >
                            <Send class="w-4 h-4 group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform" />
                            {{ form.processing ? 'Publishing...' : 'Publish Thread' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
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