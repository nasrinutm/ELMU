<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { route } from 'ziggy-js';
import {
    Server, Database, Cpu, Trash2, FileText, Plus,
    Search, ChevronUp, ChevronDown, X, Bot, Edit2, Save, UploadCloud, AlertCircle, FilePlus
} from 'lucide-vue-next';

// Define Props with TypeScript types
const props = defineProps<{
    files: Array<{
        name: string;
        display_name: string;
        size_bytes: number;
        mime_type: string;
        state: string;
        created_at: string;
    }>;
    systemInfo: {
        model: string;
        store_id: string;
        current_prompt: string;
    };
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'AI Details', href: route('chatbot.details') },
];

// --- MODAL STATES ---
const isPromptModalOpen = ref(false);
const isUploadModalOpen = ref(false);
const isDeleteModalOpen = ref(false);

// --- UPLOAD FORM LOGIC ---
const uploadForm = useForm({
    title: '',
    file: null as File | null,
});

const uploadStatus = ref('');

const openUploadModal = () => {
    uploadForm.reset();
    uploadStatus.value = '';
    isUploadModalOpen.value = true;
};

const submitUpload = () => {
    uploadStatus.value = 'Indexing to Google AI...';
    uploadForm.post(route('upload.store'), {
        preserveScroll: true,
        onSuccess: () => {
            isUploadModalOpen.value = false;
            uploadForm.reset();
            uploadStatus.value = '';
        },
        onError: () => {
            uploadStatus.value = 'Upload failed. Check file size/format.';
        }
    });
};

// --- SYSTEM PROMPT LOGIC ---
const promptForm = useForm({
    instruction: props.systemInfo.current_prompt,
});

const openEditModal = () => {
    promptForm.instruction = props.systemInfo.current_prompt;
    isPromptModalOpen.value = true;
};

const closeEditModal = () => {
    isPromptModalOpen.value = false;
    promptForm.reset();
};

const savePrompt = () => {
    promptForm.put(route('chatbot.prompt.update'), {
        preserveScroll: true,
        onSuccess: () => {
            closeEditModal();
        },
    });
};

// --- SEARCH & SORT LOGIC ---
const searchQuery = ref('');
const sortKey = ref('created_at');
const sortOrder = ref('desc');
const fileToDelete = ref<string | null>(null);

const filteredFiles = computed(() => {
    let result = [...props.files];
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(file =>
            file.display_name?.toLowerCase().includes(query) ||
            file.mime_type?.toLowerCase().includes(query)
        );
    }
    result.sort((a: any, b: any) => {
        let valA = a[sortKey.value];
        let valB = b[sortKey.value];
        let modifier = sortOrder.value === 'desc' ? -1 : 1;
        if (valA < valB) return -1 * modifier;
        if (valA > valB) return 1 * modifier;
        return 0;
    });
    return result;
});

const sortBy = (key: string) => {
    if (sortKey.value === key) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = key;
        sortOrder.value = 'asc';
    }
};

const deleteFile = (file: any) => {
    fileToDelete.value = file.name;
    isDeleteModalOpen.value = true;
};

const confirmDelete = () => {
    if (fileToDelete.value) {
        router.delete(route('upload.delete', fileToDelete.value), {
            preserveScroll: true,
            onFinish: () => {
                isDeleteModalOpen.value = false;
                fileToDelete.value = null;
            }
        });
    }
};

const formatBytes = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};
</script>

<template>
    <Head title="AI Knowledge Center" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-8 antialiased font-sans font-normal text-gray-900">

            <div class="border-b border-slate-200 pb-6">
                <h1 class="text-3xl font-bold tracking-tight text-slate-900 uppercase">AI Knowledge Center</h1>
                <p class="text-sm text-slate-500 mt-1">Manage system model, behavioral prompt, and training data.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div v-for="(stat, index) in [
                    { label: 'Current Model', val: systemInfo.model, icon: Cpu },
                    { label: 'Knowledge Store', val: systemInfo.store_id, icon: Database },
                    { label: 'Indexed Documents', val: files.length + ' Files', icon: FileText }
                ]" :key="index" class="p-6 bg-white border border-slate-200 shadow-sm flex items-center gap-4 transition-all hover:border-action/50 rounded-sm">
                    <div class="p-3 bg-slate-50 rounded-sm text-action border border-slate-100 shadow-inner">
                        <component :is="stat.icon" class="w-6 h-6" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ stat.label }}</p>
                        <h3 class="text-lg font-bold text-slate-900 truncate break-all mt-1 leading-tight">{{ stat.val }}</h3>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-slate-200 shadow-sm rounded-none overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <Bot class="w-5 h-5 text-action" />
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-widest">System Behavior Prompt</h3>
                    </div>
                    <Button variant="outline" size="sm" @click="openEditModal" class="text-[10px] font-bold uppercase tracking-widest px-4">
                        <Edit2 class="w-3 h-3 mr-2" /> Update Instructions
                    </Button>
                </div>
                <div class="p-8 italic text-sm text-slate-700 leading-relaxed bg-slate-50/30 whitespace-pre-wrap font-medium border-l-4 border-action">
                    "{{ systemInfo.current_prompt }}"
                </div>
            </div>

            <div class="flex flex-col md:flex-row justify-between items-center gap-4 w-full">
                <div class="relative max-w-md w-full">
                    <Search class="absolute left-3 top-3.5 h-4 w-4 text-slate-400" />
                    <input v-model="searchQuery" type="text" placeholder="Search knowledge base..."
                        class="block w-full pl-10 pr-10 py-3 border border-slate-300 rounded-none bg-white text-sm focus:outline-none focus:ring-1 focus:ring-action focus:border-action transition shadow-sm" />
                    <button v-if="searchQuery" @click="searchQuery = ''" class="absolute right-3 top-3.5 text-slate-400 hover:text-gray-600">
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <div class="shrink-0">
                    <Button
                        @click="openUploadModal"
                        class="bg-action hover:bg-[#0f172a] text-white font-bold shadow-md rounded-none text-[10px] uppercase tracking-widest px-8 py-4 h-auto transition-all duration-200">
                        <Plus class="w-4 h-4 mr-2" />
                        Add New Material
                    </Button>
                </div>
            </div>

            <div class="bg-white border border-slate-200 shadow-sm rounded-none overflow-hidden w-full">
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-widest">Active Knowledge Base</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-100/50 border-b border-slate-200">
                            <tr>
                                <th @click="sortBy('display_name')" class="px-6 py-4 cursor-pointer text-[10px] font-bold uppercase tracking-widest text-slate-500">
                                    <div class="flex items-center gap-1">
                                        File Name
                                        <ChevronUp v-if="sortKey === 'display_name' && sortOrder === 'asc'" class="w-3 h-3 text-action" />
                                        <ChevronDown v-else-if="sortKey === 'display_name' && sortOrder === 'desc'" class="w-3 h-3 text-action" />
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest text-slate-400 italic">Gemini ID</th>
                                <th @click="sortBy('size_bytes')" class="px-6 py-4 cursor-pointer text-[10px] font-bold uppercase tracking-widest text-slate-500">Size</th>
                                <th @click="sortBy('state')" class="px-6 py-4 cursor-pointer text-[10px] font-bold uppercase tracking-widest text-slate-500">Status</th>
                                <th class="px-6 py-4 text-right text-[10px] font-bold uppercase tracking-widest text-slate-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="file in filteredFiles" :key="file.name" class="group hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 font-bold text-slate-900">{{ file.display_name }}</td>
                                <td class="px-6 py-4 font-mono text-[10px] text-slate-400">{{ file.name.split('/').pop() }}</td>
                                <td class="px-6 py-4 text-xs font-medium text-slate-500">{{ formatBytes(file.size_bytes) }}</td>
                                <td class="px-6 py-4">
                                    <Badge :class="file.state.includes('ACTIVE') ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'"
                                        class="uppercase text-[9px] font-bold px-2.5 py-1 rounded-sm shadow-sm border-none">
                                        {{ file.state }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Button variant="ghost" size="icon" @click="deleteFile(file)" class="text-red-500 hover:text-red-700 hover:bg-red-50 transition-colors">
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </td>
                            </tr>
                            <tr v-if="filteredFiles.length === 0">
                                <td colspan="5" class="px-6 py-20 text-center text-slate-400 italic text-sm">
                                    <div class="flex flex-col items-center gap-2">
                                        <Search class="w-8 h-8 text-slate-200" />
                                        <span>No documents found in knowledge base.</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div v-if="isUploadModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-none shadow-2xl w-full max-w-xl overflow-hidden border border-slate-200 animate-in fade-in zoom-in duration-200">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <h3 class="font-bold text-slate-800 uppercase tracking-widest flex items-center gap-2 text-sm">
                        <UploadCloud class="w-5 h-5 text-action" /> Upload Material
                    </h3>
                    <button @click="isUploadModalOpen = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                <div class="p-8">
                    <form @submit.prevent="submitUpload" class="space-y-8">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Document Title</label>
                            <input v-model="uploadForm.title" type="text" class="block w-full border border-slate-300 h-12 px-4 text-sm focus:ring-1 focus:ring-action focus:border-action transition outline-none" placeholder="e.g., Physics Chapter 1" required />
                            <div v-if="uploadForm.errors.title" class="text-red-500 text-[10px] font-bold uppercase mt-1">{{ uploadForm.errors.title }}</div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">File Attachment (PDF, TXT, CSV)</label>
                            <input type="file" @input="uploadForm.file = ($event.target as HTMLInputElement).files?.[0] || null"
                                class="block w-full text-xs text-slate-500 border border-dashed border-slate-300 p-4 bg-slate-50/30 hover:border-action transition-colors cursor-pointer" required />
                            <div v-if="uploadForm.progress" class="w-full bg-slate-100 h-1.5 mt-4 overflow-hidden">
                                <div class="bg-action h-full transition-all duration-300" :style="{ width: uploadForm.progress.percentage + '%' }"></div>
                            </div>
                            <div v-if="uploadForm.errors.file" class="text-red-500 text-[10px] font-bold uppercase mt-1">{{ uploadForm.errors.file }}</div>
                        </div>

                        <div v-if="uploadStatus" class="p-4 bg-slate-50 text-action text-[10px] font-bold uppercase tracking-widest border border-slate-200 animate-pulse">
                            {{ uploadStatus }}
                        </div>

                        <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                            <Button variant="ghost" type="button" @click="isUploadModalOpen = false" class="text-[10px] font-bold uppercase tracking-widest">Cancel</Button>
                            <Button type="submit" :disabled="uploadForm.processing" class="bg-action hover:bg-[#0f172a] text-white font-bold text-[10px] uppercase tracking-widest px-8 py-3 shadow-md h-auto transition-all">
                                <FilePlus class="w-4 h-4 mr-2" /> {{ uploadForm.processing ? 'Indexing...' : 'Upload to AI' }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div v-if="isDeleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white rounded-none shadow-2xl p-8 max-w-sm w-full border border-slate-200">
                <h2 class="text-lg font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2"><Trash2 class="w-5 h-5 text-red-500" /> Confirm</h2>
                <p class="mt-4 text-slate-600 text-sm leading-relaxed font-medium">Are you sure you want the AI to forget this document? This cannot be undone.</p>
                <div class="mt-8 flex justify-end gap-3">
                    <Button variant="ghost" @click="isDeleteModalOpen = false" class="text-[10px] font-bold uppercase tracking-widest">Cancel</Button>
                    <Button @click="confirmDelete" class="bg-red-600 hover:bg-red-700 text-white text-[10px] font-bold uppercase tracking-widest px-8 shadow-md">Confirm</Button>
                </div>
            </div>
        </div>

        <div v-if="isPromptModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-none shadow-2xl w-full max-w-2xl border border-slate-200 animate-in fade-in zoom-in duration-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <h3 class="font-bold text-slate-800 uppercase tracking-widest flex items-center gap-2 text-sm">
                        <Bot class="w-5 h-5 text-action" /> Edit AI Instructions
                    </h3>
                    <button @click="isPromptModalOpen = false" class="text-slate-400 hover:text-slate-600 transition-colors"><X class="w-5 h-5" /></button>
                </div>
                <div class="p-8">
                    <textarea v-model="promptForm.instruction" rows="8" class="w-full p-4 text-sm text-gray-700 bg-slate-50 border border-slate-200 rounded-none focus:ring-1 focus:ring-action focus:border-action transition outline-none"></textarea>
                    <div class="mt-8 flex justify-end gap-3">
                        <Button variant="ghost" @click="isPromptModalOpen = false" class="text-[10px] font-bold uppercase tracking-widest">Cancel</Button>
                        <Button @click="savePrompt" :disabled="promptForm.processing" class="bg-action hover:opacity-90 text-white px-8 py-3 rounded-none text-[10px] font-bold uppercase tracking-widest shadow-md transition-all">
                            <Save class="w-4 h-4 mr-2" /> Save Changes
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
