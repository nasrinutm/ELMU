<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { type BreadcrumbItem } from '@/types';
import {
    FileText,
    ArrowLeft,
    Save,
    FileUp,
    BookOpen,
    CheckCircle2,
    X,
    AlertTriangle
} from 'lucide-vue-next';
import { route } from 'ziggy-js';
import { computed, ref, watch, nextTick } from 'vue';

const props = defineProps<{
    material: {
        id: number;
        name: string;
        subject: string;
        description: string | null;
        file_name: string;
    };
}>();

// --- NOTIFICATION & MODAL STATE ---
const page = usePage();
const flashSuccess = computed(() => (page.props as any).flash?.success);
const showNotification = ref(false);
const showConfirmModal = ref(false);

// Watch for flash messages with RESET HANDSHAKE logic
watch(flashSuccess, async (newVal) => {
    if (newVal) {
        // 1. Force hide existing notification to reset transition state
        showNotification.value = false;

        // 2. Wait for DOM update, then show again to trigger animation
        await nextTick();
        showNotification.value = true;

        // 3. Auto-dismiss after 5 seconds
        setTimeout(() => {
            showNotification.value = false;
            // 4. Manually clear the flash prop locally so next update is seen as fresh
            (page.props as any).flash.success = null;
        }, 5000);
    }
}, { immediate: true });

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Materials', href: route('materials.index') },
    { title: 'Edit Material', href: route('materials.edit', props.material.id) },
];

const form = useForm({
    _method: 'put',
    name: props.material.name,
    subject: props.material.subject,
    description: props.material.description || '',
    file: null as File | null,
});

// Trigger Custom Modal instead of browser confirm
const requestSubmit = () => {
    showConfirmModal.value = true;
};

const confirmSubmit = () => {
    showConfirmModal.value = false;
    form.post(route('materials.update', props.material.id), {
        forceFormData: true,
        onError: () => {
            if (form.errors.file) {
                form.reset('file');
            }
        },
    });
};
</script>

<template>
    <Head title="Edit Material" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-4xl mx-auto pb-20 px-4 sm:px-6 lg:px-8 font-sans antialiased text-slate-900 mt-10 relative">

            <transition name="notification">
                <div v-if="showNotification" class="fixed top-10 right-10 z-[100] flex items-center gap-4 bg-slate-900 text-white p-5 shadow-2xl border-l-4 border-emerald-500 min-w-[350px]">
                    <div class="bg-emerald-500/20 p-2">
                        <CheckCircle2 class="w-6 h-6 text-emerald-500" />
                    </div>
                    <div class="flex-grow">
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-emerald-500">System Notification</p>
                        <p class="text-sm font-medium">{{ flashSuccess }}</p>
                    </div>
                    <button @click="showNotification = false" class="text-slate-500 hover:text-white transition">
                        <X class="w-4 h-4" />
                    </button>
                </div>
            </transition>

            <div class="flex items-center justify-between border-b border-slate-200 pb-6 mb-10">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900 uppercase">Edit Material</h1>
                    <p class="text-sm text-slate-500 mt-1 font-medium italic">Update unit details and resource files for your students.</p>
                </div>
                <Link :href="route('materials.index')" class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-action transition">
                    <ArrowLeft class="w-4 h-4" /> Back to Library
                </Link>
            </div>

            <div class="bg-white border border-slate-200 shadow-sm rounded-none overflow-hidden">
                <div class="p-8 border-b border-slate-50 bg-slate-50/30 flex items-center gap-3">
                    <FileText class="w-5 h-5 text-action" />
                    <h3 class="font-bold text-slate-900 uppercase text-[10px] tracking-[0.2em]">Module Information</h3>
                </div>

                <form @submit.prevent="requestSubmit" class="p-8 space-y-8">
                    <div class="space-y-2">
                        <Label for="name" class="text-[10px] font-bold uppercase text-slate-400 tracking-widest">Name / Title</Label>
                        <Input id="name" v-model="form.name" type="text" class="w-full bg-slate-50 border border-slate-200 px-4 py-6 text-sm focus:ring-1 focus:ring-action focus:border-action outline-none transition font-medium rounded-none" placeholder="Enter material title..." required />
                        <InputError :message="form.errors.name" class="text-[10px] font-bold uppercase" />
                    </div>

                    <div class="space-y-2">
                        <Label for="subject" class="text-[10px] font-bold uppercase text-slate-400 tracking-widest">Subject</Label>
                        <Input id="subject" v-model="form.subject" type="text" class="w-full bg-slate-50 border border-slate-200 px-4 py-6 text-sm focus:ring-1 focus:ring-action focus:border-action outline-none transition font-medium rounded-none" placeholder="e.g. Physics, Mathematics..." required />
                        <InputError :message="form.errors.subject" class="text-[10px] font-bold uppercase" />
                    </div>

                    <div class="space-y-2">
                        <Label for="description" class="text-[10px] font-bold uppercase text-slate-400 tracking-widest">Description (Optional)</Label>
                        <textarea id="description" v-model="form.description" rows="4" class="w-full bg-slate-50 border border-slate-200 px-4 py-4 text-sm focus:ring-1 focus:ring-action focus:border-action outline-none transition font-medium rounded-none resize-none border-input" placeholder="Provide brief details about this material..."></textarea>
                        <InputError :message="form.errors.description" class="text-[10px] font-bold uppercase" />
                    </div>

                    <div class="space-y-4">
                        <Label class="text-[10px] font-bold uppercase text-slate-400 tracking-widest">Replace Resource File (Optional)</Label>
                        <div class="flex items-center gap-3 p-4 bg-indigo-50 border border-indigo-100 rounded-none text-indigo-700">
                            <BookOpen class="w-4 h-4 opacity-70" />
                            <span class="text-[10px] font-bold uppercase tracking-tight truncate">Current File: {{ material.file_name }}</span>
                        </div>
                        <div class="relative border-2 border-dashed border-slate-200 p-12 text-center hover:border-action transition-colors bg-slate-50/50 group">
                            <input type="file" @input="form.file = ($event.target as HTMLInputElement).files?.[0] || null" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
                            <div class="flex flex-col items-center gap-2">
                                <FileUp class="w-8 h-8 text-slate-300 group-hover:text-action transition-colors" />
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest group-hover:text-action transition-colors">
                                    {{ form.file ? form.file.name : 'Drag or click to choose a new file' }}
                                </span>
                            </div>
                        </div>
                        <InputError :message="form.errors.file" class="text-[10px] font-bold uppercase" />
                    </div>

                    <div class="flex items-center justify-end gap-6 pt-6 border-t border-slate-100">
                        <Link :href="route('materials.index')" class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 hover:text-slate-600 transition">Cancel</Link>
                        <Button type="submit" :disabled="form.processing" class="bg-slate-900 text-white px-8 py-4 text-[10px] font-bold uppercase tracking-[0.3em] shadow-xl hover:bg-action transition flex items-center gap-3 rounded-none h-auto">
                            <Save class="w-4 h-4" v-if="!form.processing" />
                            {{ form.processing ? 'Saving...' : 'Save Changes' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>

        <transition name="modal">
            <div v-if="showConfirmModal" class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm font-sans">
                <div class="bg-white max-w-md w-full p-10 shadow-2xl border border-slate-200 rounded-none">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-16 h-16 bg-amber-50 rounded-full flex items-center justify-center mb-6">
                            <AlertTriangle class="w-8 h-8 text-amber-500" />
                        </div>
                        <h3 class="text-sm font-bold uppercase tracking-[0.2em] text-slate-900 mb-2">Confirm Update</h3>
                        <p class="text-sm text-slate-500 font-medium mb-8 leading-relaxed">
                            Proceed with saving these changes? This will update the material details for all students.
                        </p>
                        <div class="flex gap-4 w-full">
                            <button @click="showConfirmModal = false" class="flex-1 py-4 text-[10px] font-bold uppercase tracking-widest text-slate-400 border border-slate-100 hover:bg-slate-50 transition">
                                No, Cancel
                            </button>
                            <button @click="confirmSubmit" class="flex-1 py-4 bg-slate-900 text-white text-[10px] font-bold uppercase tracking-widest hover:bg-action shadow-lg transition">
                                Yes, Proceed
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </AppLayout>
</template>

<style scoped>
.border-dashed {
    transition: all 0.2s ease-in-out;
}

/* Success Toast Animation */
.notification-enter-active, .notification-leave-active {
    transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.notification-enter-from {
    transform: translateX(100px);
    opacity: 0;
}
.notification-leave-to {
    transform: translateY(-20px);
    opacity: 0;
}

/* Modal Fade Animation */
.modal-enter-active, .modal-leave-active {
    transition: opacity 0.3s ease;
}
.modal-enter-from, .modal-leave-to {
    opacity: 0;
}
</style>
