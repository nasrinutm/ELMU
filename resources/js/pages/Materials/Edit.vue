<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    FileText,
    ArrowLeft,
    Save,
    FileUp,
    BookOpen,
    CheckCircle2,
    X,
    AlertTriangle,
    AlertCircle,
    FileCheck
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

watch(flashSuccess, async (newVal) => {
    if (newVal) {
        showNotification.value = false;
        await nextTick();
        showNotification.value = true;
        setTimeout(() => {
            showNotification.value = false;
            (page.props as any).flash.success = null;
        }, 5000);
    }
}, { immediate: true });

const breadcrumbs = [
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

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        form.file = target.files[0];
        form.clearErrors('file');
    }
};

// Custom Validation before showing modal
const requestSubmit = () => {
    form.clearErrors();

    let hasError = false;
    if (!form.name) {
        form.setError('name', 'Please input document name');
        hasError = true;
    }
    if (!form.subject) {
        form.setError('subject', 'Please input subject/topic');
        hasError = true;
    }

    if (!hasError) {
        showConfirmModal.value = true;
    }
};

const confirmSubmit = () => {
    showConfirmModal.value = false;
    form.post(route('materials.update', props.material.id), {
        forceFormData: true,
        onError: () => {
            if (form.errors.file) form.reset('file');
        },
    });
};
</script>

<template>
    <Head title="Edit Material" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-6xl mx-auto p-6 space-y-6 font-sans antialiased text-slate-900">

            <transition name="notification">
                <div v-if="showNotification" class="fixed top-10 right-10 z-[100] flex items-center gap-4 bg-slate-900 text-white p-5 shadow-2xl border-l-4 border-emerald-500 min-w-[350px]">
                    <div class="bg-emerald-500/20 p-2"><CheckCircle2 class="w-6 h-6 text-emerald-500" /></div>
                    <div class="flex-grow">
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-emerald-500">System Notification</p>
                        <p class="text-sm font-medium">{{ flashSuccess }}</p>
                    </div>
                    <button @click="showNotification = false" class="text-slate-500 hover:text-white transition"><X class="w-4 h-4" /></button>
                </div>
            </transition>

            <div class="flex items-center justify-between border-b border-slate-100 pb-6">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-slate-100 rounded-lg text-slate-600 border border-slate-200 shadow-sm">
                        <FileText class="w-6 h-6" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight uppercase">Edit Material</h1>
                        <p class="text-sm text-slate-500 font-medium italic">ID: #{{ material.id }} — Update resource details.</p>
                    </div>
                </div>
                <Link :href="route('materials.index')" class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-teal-600 transition-all group">
                    <ArrowLeft class="w-4 h-4 transition-transform group-hover:-translate-x-1" /> Back to Library
                </Link>
            </div>

            <div class="bg-white border border-slate-200 rounded-none shadow-sm overflow-hidden">
                <form @submit.prevent="requestSubmit">
                    <div class="flex flex-col lg:flex-row">

                        <div class="lg:w-2/5 p-8 bg-slate-50/50 border-r border-slate-100 flex flex-col justify-center">
                            <Label class="block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 mb-4">
                                Update Resource File
                            </Label>

                            <div class="mb-4 flex items-center gap-3 p-3 bg-white border border-slate-200 text-slate-600 shadow-sm">
                                <BookOpen class="w-4 h-4 text-teal-500" />
                                <span class="text-[9px] font-bold uppercase tracking-tight truncate">Current: {{ material.file_name }}</span>
                            </div>

                            <div
                                class="h-full min-h-[220px] border-2 border-dashed rounded-none flex flex-col items-center justify-center text-center hover:bg-white hover:border-teal-500 transition-all cursor-pointer relative bg-white"
                                :class="[form.file ? 'border-teal-500 ring-2 ring-teal-500/10' : 'border-slate-200', form.errors.file ? 'border-red-500 bg-red-50/30' : '']"
                            >
                                <input type="file" @change="handleFileChange" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />

                                <div v-if="!form.file" class="flex flex-col items-center p-6 pointer-events-none">
                                    <div class="h-14 w-14 bg-slate-50 text-slate-400 rounded-full flex items-center justify-center mb-4 border border-slate-100">
                                        <FileUp class="w-7 h-7" />
                                    </div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Choose New File</p>
                                    <p class="text-[9px] font-bold text-slate-400 mt-2 italic">(Optional)</p>
                                </div>

                                <div v-else class="flex flex-col items-center p-6 pointer-events-none text-teal-700">
                                    <div class="h-14 w-14 bg-teal-50 rounded-full flex items-center justify-center mb-4 border border-teal-100">
                                        <FileCheck class="w-7 h-7" />
                                    </div>
                                    <p class="text-xs font-bold uppercase tracking-tight break-all">{{ form.file.name }}</p>
                                    <p class="text-[9px] font-black uppercase tracking-widest mt-2 bg-teal-600 text-white px-2 py-1 rounded-none">New File Ready</p>
                                </div>
                            </div>
                            <p v-if="form.errors.file" class="text-red-600 text-[10px] font-bold uppercase mt-3 italic flex items-center gap-1">
                                <AlertCircle class="w-3 h-3" /> {{ form.errors.file }}
                            </p>
                        </div>

                        <div class="lg:w-3/5 p-8 sm:p-10 space-y-6">

                            <div class="space-y-2">
                                <Label for="name" class="block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">
                                    Document Name <span class="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    @input="form.clearErrors('name')"
                                    placeholder="Enter title..."
                                    class="h-12 rounded-none border-slate-200 focus:ring-1 focus:ring-teal-500 focus:border-teal-500 font-medium"
                                    :class="{'border-red-500 ring-1 ring-red-500': form.errors.name}"
                                />
                                <p v-if="form.errors.name" class="text-red-600 text-[10px] font-bold uppercase mt-1 italic flex items-center gap-1">
                                    <AlertCircle class="w-3 h-3" /> {{ form.errors.name }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="subject" class="block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">
                                    Subject / Topic <span class="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="subject"
                                    v-model="form.subject"
                                    @input="form.clearErrors('subject')"
                                    placeholder="e.g. Mathematics"
                                    class="h-12 rounded-none border-slate-200 focus:ring-1 focus:ring-teal-500 focus:border-teal-500 font-medium"
                                    :class="{'border-red-500 ring-1 ring-red-500': form.errors.subject}"
                                />
                                <p v-if="form.errors.subject" class="text-red-600 text-[10px] font-bold uppercase mt-1 italic flex items-center gap-1">
                                    <AlertCircle class="w-3 h-3" /> {{ form.errors.subject }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="description" class="block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">
                                    Description (Optional)
                                </Label>
                                <Textarea
                                    id="description"
                                    v-model="form.description"
                                    class="rounded-none border-slate-200 focus:ring-1 focus:ring-teal-500 focus:border-teal-500 min-h-[120px] font-medium"
                                />
                            </div>

                            <div class="pt-6 flex justify-end gap-3 border-t border-slate-50">
                                <Link :href="route('materials.index')" class="px-8 py-4 text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-slate-600 flex items-center">
                                    Cancel
                                </Link>

                                <Button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="bg-slate-900 hover:bg-teal-600 text-white font-bold text-[10px] uppercase tracking-[0.3em] px-12 py-6 rounded-none shadow-md transition-all h-auto"
                                >
                                    <Save v-if="!form.processing" class="w-4 h-4 mr-2" />
                                    {{ form.processing ? 'Saving...' : 'Update Material' }}
                                </Button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <transition name="modal">
            <div v-if="showConfirmModal" class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
                <div class="bg-white max-w-md w-full p-10 border border-slate-200 rounded-none shadow-2xl text-center">
                    <div class="w-16 h-16 bg-amber-50 rounded-full flex items-center justify-center mb-6 mx-auto">
                        <AlertTriangle class="w-8 h-8 text-amber-500" />
                    </div>
                    <h3 class="font-bold uppercase tracking-[0.2em] text-slate-900 mb-2 text-sm">Save Changes?</h3>
                    <p class="text-sm text-slate-500 mb-8 leading-relaxed">
                        Update material <strong>"{{ material.name }}"</strong>? This change will be visible to all students immediately.
                    </p>
                    <div class="flex gap-4 w-full">
                        <button @click="showConfirmModal = false" class="flex-1 py-4 text-[10px] font-bold uppercase border border-slate-100 hover:bg-slate-50 transition">Cancel</button>
                        <button @click="confirmSubmit" class="flex-1 py-4 bg-slate-900 text-white text-[10px] font-bold uppercase hover:bg-teal-600 shadow-lg transition-all">Confirm</button>
                    </div>
                </div>
            </div>
        </transition>

    </AppLayout>
</template>

<style scoped>
/* Success Toast Animation */
.notification-enter-active, .notification-leave-active {
    transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.notification-enter-from { transform: translateX(100px); opacity: 0; }
.notification-leave-to { transform: translateY(-20px); opacity: 0; }

/* Modal Fade Animation */
.modal-enter-active, .modal-leave-active { transition: opacity 0.3s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>
