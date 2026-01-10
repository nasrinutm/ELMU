<script setup lang="ts">
import { ref, watch, nextTick, computed } from 'vue';
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

// --- VALIDATION LIMITS ---
const limits = {
    title: 100 // Typical limit for document titles
};

const form = useForm({
    title: '',
    file: null as File | null,
});

// --- VALIDATION LOGIC ---
const isValid = computed(() => {
    return form.title.trim().length > 0 && 
           form.title.length <= limits.title && 
           form.file !== null;
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
    { deep: true, immediate: true }
);

const submit = () => {
    if (!isValid.value) return;

    form.post(route('upload.store'), {
        onSuccess: () => { 
            form.reset(); 
        },
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

            <div class="mx-auto">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-200 pb-8 mb-12">
                    <div>
                        <h1 class="text-4xl font-black text-slate-900 tracking-normal uppercase mb-2">Upload Knowledge</h1>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Train your custom AI assistant with PDF, TXT, or CSV files.</p>
                    </div>
                    
                    <div class="flex flex-col items-end gap-4">
                        <Link :href="route('chatbot.details')" class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-slate-900 transition-colors">
                            <ArrowLeft class="w-4 h-4" /> Back to Base
                        </Link>
                        <div class="inline-flex items-center bg-slate-50 px-3 py-1 border border-slate-100">
                            <span class="text-red-500 font-bold mr-2">*</span>
                            <span class="text-[9px] font-bold uppercase tracking-widest text-slate-500 italic">Indicates required field</span>
                        </div>
                    </div>
                </div>

                <div class="w-full bg-white border border-slate-200 rounded-none shadow-sm overflow-hidden">
                    <div class="p-8 sm:p-12">
                        <form @submit.prevent="submit" class="space-y-12">
                            
                            <div class="group">
                                <div class="flex justify-between items-end mb-4">
                                    <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 block group-focus-within:text-slate-900 transition-colors">
                                        Document Title <span class="text-red-500">*</span>
                                    </label>
                                    <span :class="[
                                        form.title.length === 0 ? 'text-slate-200' :
                                        form.title.length > limits.title ? 'text-red-500' : 'text-emerald-500'
                                    ]" class="text-[9px] font-mono font-bold transition-colors">
                                        {{ form.title.length }} / {{ limits.title }}
                                    </span>
                                </div>
                                
                                <input 
                                    v-model="form.title" 
                                    type="text"
                                    :maxlength="limits.title"
                                    class="w-full text-2xl font-black text-slate-900 tracking-normal border-b-2 bg-transparent outline-none transition-all py-2 placeholder:text-slate-100"
                                    :class="form.errors.title ? 'border-red-500' : 'border-slate-200 focus:border-slate-900'"
                                    placeholder="e.g., Physics Chapter 1"
                                />
                                
                                <div v-if="form.errors.title" class="text-red-500 text-[10px] font-bold uppercase mt-2 tracking-widest flex items-center gap-1">
                                    <AlertCircle class="w-3 h-3" /> {{ form.errors.title }}
                                </div>
                            </div>

                            <div class="group">
                                <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 block mb-4 group-focus-within:text-slate-900 transition-colors">
                                    File Attachment (PDF, TXT, CSV) <span class="text-red-500">*</span>
                                </label>
                                
                                <input 
                                    type="file" 
                                    @input="form.file = ($event.target as HTMLInputElement).files?.[0] || null"
                                    class="block w-full text-xs text-slate-500 border border-dashed p-6 bg-slate-50 shadow-inner hover:bg-white transition-all cursor-pointer" 
                                    :class="form.errors.file ? 'border-red-500' : 'border-slate-300 hover:border-slate-900'"
                                />

                                <div v-if="form.progress" class="w-full bg-slate-100 h-1.5 mt-4 overflow-hidden">
                                    <div class="bg-slate-900 h-full transition-all duration-300" :style="{ width: form.progress.percentage + '%' }"></div>
                                </div>

                                <div v-if="form.file && !form.errors.file" class="mt-3 flex items-center gap-2 text-emerald-600">
                                    <CheckCircle2 class="w-3 h-3" />
                                    <p class="text-[9px] font-bold uppercase tracking-widest">Ready for indexing: {{ form.file.name }}</p>
                                </div>

                                <div v-if="form.errors.file" class="text-red-500 text-[10px] font-bold uppercase mt-2 tracking-widest flex items-center gap-1">
                                    <AlertCircle class="w-3 h-3" /> {{ form.errors.file }}
                                </div>
                            </div>

                           <div class="flex justify-end items-center pt-10 border-t border-slate-100 gap-8">
                                <Link 
                                    :href="route('chatbot.details')" 
                                    class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 hover:text-red-500 transition-colors"
                                >
                                    Discard
                                </Link>

                                <Button 
                                    type="submit" 
                                    :disabled="form.processing || !isValid"
                                    class="bg-[#0f172a] hover:bg-teal-700 text-white font-bold text-[10px] uppercase tracking-[0.2em] px-12 py-5 h-auto rounded-none transition-all shadow-2xl disabled:opacity-30 disabled:cursor-not-allowed"
                                >
                                    {{ form.processing ? 'Indexing...' : 'Publish to Base' }}
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