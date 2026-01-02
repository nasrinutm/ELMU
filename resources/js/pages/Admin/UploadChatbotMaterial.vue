<script setup lang="ts">
import { ref, watch, nextTick } from 'vue';
import { useForm, Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { route } from 'ziggy-js';
import { FilePlus, UploadCloud, AlertCircle, ArrowLeft, CheckCircle2, X } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'AI Knowledge Base', href: route('chatbot.details') },
    { title: 'Upload Material', href: '#' },
];

const page = usePage();
const showSuccessNotification = ref(false);

const form = useForm({
    title: '',
    file: null as File | null,
});

// --- NOTIFICATION WATCHER (Catch potential success messages before redirect) ---
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
    { deep: true, immediate: true }
);

const submit = () => {
    form.post(route('upload.store'), {
        onSuccess: () => { form.reset(); },
    });
};
</script>

<template>
    <Head title="Upload Knowledge Base" />

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
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-200 pb-8 mb-12">
                    <div>
                        <h1 class="text-4xl font-black text-slate-900 tracking-tighter uppercase mb-2">Upload Knowledge</h1>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Train your custom AI assistant with PDF, TXT, or CSV files.</p>
                    </div>
                    <Link :href="route('chatbot.details')" class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-slate-900 transition-colors">
                        <ArrowLeft class="w-4 h-4" /> Back to Base
                    </Link>
                </div>

                <div class="w-full max-w-2xl bg-white border border-slate-200 rounded-none shadow-sm overflow-hidden">
                    <div class="p-8 sm:p-12">
                        <form @submit.prevent="submit" class="space-y-10">
                            <div class="group">
                                <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 block mb-4 group-focus-within:text-slate-900 transition-colors">Document Title</label>
                                <input v-model="form.title" type="text"
                                    class="w-full text-2xl font-black text-slate-900 tracking-tighter uppercase border-b-2 border-slate-200 bg-transparent outline-none focus:border-slate-900 transition-all py-2 placeholder:text-slate-100"
                                    placeholder="e.g., Physics Chapter 1" required />
                                <div v-if="form.errors.title" class="text-red-500 text-[10px] font-bold uppercase mt-2 tracking-widest flex items-center gap-1">
                                    <AlertCircle class="w-3 h-3" /> {{ form.errors.title }}
                                </div>
                            </div>

                            <div class="group">
                                <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 block mb-4 group-focus-within:text-slate-900 transition-colors">File Attachment (PDF, TXT, CSV)</label>
                                <input type="file" @input="form.file = ($event.target as HTMLInputElement).files?.[0] || null"
                                    class="block w-full text-xs text-slate-500 border border-dashed border-slate-300 p-6 bg-slate-50 shadow-inner hover:border-slate-900 transition-all cursor-pointer" required />
                                <div v-if="form.progress" class="w-full bg-slate-100 h-1.5 mt-4 overflow-hidden">
                                    <div class="bg-action h-full transition-all duration-300" :style="{ width: form.progress.percentage + '%' }"></div>
                                </div>
                                <div v-if="form.errors.file" class="text-red-500 text-[10px] font-bold uppercase mt-2 tracking-widest flex items-center gap-1">
                                    <AlertCircle class="w-3 h-3" /> {{ form.errors.file }}
                                </div>
                            </div>

                            <div class="flex justify-end pt-6 border-t border-slate-100 gap-4">
                                <Link :href="route('chatbot.details')" class="text-[10px] font-bold uppercase tracking-widest text-slate-400 py-3 hover:text-slate-900 transition">Discard</Link>
                                <Button type="submit" :disabled="form.processing"
                                    class="bg-slate-900 hover:bg-teal-700 text-white font-bold text-[10px] uppercase tracking-widest px-10 py-6 h-auto shadow-2xl transition-all">
                                    {{ form.processing ? 'Indexing to AI...' : 'Publish to Base' }}
                                </Button>
                            </div>
                        </form>
                    </div>
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