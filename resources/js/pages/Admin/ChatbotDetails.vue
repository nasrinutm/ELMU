<script setup lang="ts">
import { ref, computed, watch, nextTick } from 'vue';
import { useForm, Head, Link, router, usePage } from '@inertiajs/vue3';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Server, Database, Cpu, Trash2, FileText, Plus,
    Search, ChevronUp, ChevronDown, X, Bot, Edit2, Save, UploadCloud, 
    AlertCircle, FilePlus, CheckCircle2, Pencil
} from 'lucide-vue-next';
import { route } from 'ziggy-js';

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
    { title: 'AI Details', href: '#' },
];

// --- NOTIFICATION STATE ---
const page = usePage();
const showSuccessNotification = ref(false);
const showErrorNotification = ref(false);

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
        if (flash?.error) {
            showErrorNotification.value = false;
            nextTick(() => {
                showErrorNotification.value = true;
                setTimeout(() => { showErrorNotification.value = false; }, 5000);
            });
        }
    },
    { deep: true, immediate: true }
);

// --- MODAL & FORM STATES ---
const isPromptModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const isEditNameModalOpen = ref(false);

const promptForm = useForm({
    instruction: props.systemInfo.current_prompt,
});

const editNameForm = useForm({
    display_name: '',
    gemini_document_name: '',
});

// --- ACTIONS ---
const openEditNameModal = (file: any) => {
    editNameForm.display_name = file.display_name;
    editNameForm.gemini_document_name = file.name; // 'name' corresponds to 'gemini_document_name'
    isEditNameModalOpen.value = true;
};

const submitNameUpdate = () => {
    editNameForm.put(route('upload.update', { geminiDocumentName: editNameForm.gemini_document_name }), {
        preserveScroll: true,
        onSuccess: () => { isEditNameModalOpen.value = false; },
    });
};

const savePrompt = () => {
    promptForm.put(route('chatbot.prompt.update'), {
        preserveScroll: true,
        onSuccess: () => { isPromptModalOpen.value = false; },
    });
};

// --- SEARCH, SORT & DELETE LOGIC ---
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
    if (sortKey.value === key) sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    else { sortKey.value = key; sortOrder.value = 'asc'; }
};

const deleteFile = (file: any) => {
    fileToDelete.value = file.name;
    isDeleteModalOpen.value = true;
};

const confirmDelete = () => {
    if (fileToDelete.value) {
        router.delete(route('upload.delete', fileToDelete.value), {
            preserveScroll: true,
            onFinish: () => { isDeleteModalOpen.value = false; }
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

    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-4 md:p-8 w-full relative">

            <transition name="toast">
                <div v-if="showSuccessNotification" class="fixed top-10 right-10 z-[100] flex items-center gap-6 bg-slate-900 text-white p-6 shadow-2xl border-l-4 border-emerald-500 min-w-[400px]">
                    <div class="bg-emerald-500/20 p-3"><CheckCircle2 class="w-8 h-8 text-emerald-500" /></div>
                    <div class="flex-grow">
                        <p class="text-[11px] font-black uppercase tracking-[0.3em] text-emerald-500 mb-1">AI System Update</p>
                        <p class="text-sm font-bold">{{ (page.props as any).flash.success }}</p>
                    </div>
                    <button @click="showSuccessNotification = false" class="text-slate-500 hover:text-white p-2 transition"><X class="w-5 h-5" /></button>
                </div>
            </transition>

            <transition name="toast">
                <div v-if="showErrorNotification" class="fixed top-10 right-10 z-[100] flex items-center gap-6 bg-slate-900 text-white p-6 shadow-2xl border-l-4 border-red-500 min-w-[400px]">
                    <div class="bg-red-500/20 p-3"><AlertCircle class="w-8 h-8 text-red-500" /></div>
                    <div class="flex-grow">
                        <p class="text-[11px] font-black uppercase tracking-[0.3em] text-red-500 mb-1">System Alert</p>
                        <p class="text-sm font-bold">{{ (page.props as any).flash.error }}</p>
                    </div>
                    <button @click="showErrorNotification = false" class="text-slate-500 hover:text-white p-2 transition"><X class="w-5 h-5" /></button>
                </div>
            </transition>

            <div class="border-b border-slate-200 pb-6">
                <h1 class="text-3xl font-bold tracking-tight text-slate-900 uppercase">AI Knowledge Center</h1>
                <p class="text-sm text-slate-500 mt-1">Manage system model, behavioral prompt, and training data.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div v-for="(stat, index) in [
                    { label: 'Current Model', val: systemInfo.model, icon: Cpu },
                    { label: 'Knowledge Store', val: systemInfo.store_id, icon: Database },
                    { label: 'Indexed Documents', val: files.length + ' Files', icon: FileText }
                ]" :key="index" class="p-6 bg-white border border-slate-200 shadow-sm flex items-center gap-4 hover:border-slate-900 transition-all rounded-sm">
                    <div class="p-3 bg-slate-50 rounded-sm text-action border border-slate-100 shadow-inner"><component :is="stat.icon" class="w-6 h-6" /></div>
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
                    <Button variant="outline" size="sm" @click="isPromptModalOpen = true" class="text-[10px] font-bold uppercase tracking-widest px-4">
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
                        class="block w-full pl-10 pr-10 py-3 border border-slate-300 rounded-none bg-white text-sm focus:ring-1 focus:ring-action outline-none transition shadow-sm" />
                    <button v-if="searchQuery" @click="searchQuery = ''" class="absolute right-3 top-3.5 text-slate-400 hover:text-gray-600"><X class="h-4 w-4" /></button>
                </div>
                <Link :href="route('upload.create')">
                    <Button class="bg-slate-900 hover:bg-teal-700 text-white font-bold shadow-md rounded-none text-[10px] uppercase tracking-widest px-8 py-4 h-auto">
                        <Plus class="w-4 h-4 mr-2" /> Add New Material
                    </Button>
                </Link>
            </div>

            <div class="bg-white border border-slate-200 shadow-sm rounded-none overflow-hidden w-full">
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-widest">Active Knowledge Base</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-100/50 border-b border-slate-200">
                            <tr>
                                <th @click="sortBy('display_name')" class="px-6 py-4 cursor-pointer text-[10px] font-bold uppercase tracking-widest text-slate-500">File Name</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest text-slate-400 italic">Gemini ID</th>
                                <th @click="sortBy('size_bytes')" class="px-6 py-4 cursor-pointer text-[10px] font-bold uppercase tracking-widest text-slate-500">Size</th>
                                <th @click="sortBy('state')" class="px-6 py-4 cursor-pointer text-[10px] font-bold uppercase tracking-widest text-slate-500">Status</th>
                                <th class="px-6 py-4 text-right text-[10px] font-bold uppercase tracking-widest text-slate-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="file in filteredFiles" :key="file.name" class="group hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 font-bold text-slate-900 capitalize max-w-[200px] truncate" :title="file.display_name">{{ file.display_name }}</td>
                                <td class="px-6 py-4 font-mono text-[10px] text-slate-400">{{ file.name.split('/').pop() }}</td>
                                <td class="px-6 py-4 text-xs font-medium text-slate-500">{{ formatBytes(file.size_bytes) }}</td>
                                <td class="px-6 py-4">
                                    <Badge :class="file.state.includes('ACTIVE') ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'" class="uppercase text-[9px] font-bold px-2.5 py-1 rounded-sm shadow-sm border-none">
                                        {{ file.state }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-1">
                                        <Button variant="ghost" size="icon" @click="openEditNameModal(file)" class="text-slate-400 hover:text-blue-600 transition-colors">
                                            <Pencil class="w-4 h-4" />
                                        </Button>
                                        <Button variant="ghost" size="icon" @click="deleteFile(file)" class="text-red-500 hover:text-red-700 hover:bg-red-50 transition-colors">
                                            <Trash2 class="w-4 h-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div v-if="isEditNameModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-none shadow-2xl w-full max-w-2xl border border-slate-200 animate-in fade-in zoom-in duration-200">
                
                <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <h3 class="font-bold text-slate-800 uppercase tracking-[0.2em] flex items-center gap-3 text-sm">
                        <Pencil class="w-5 h-5 text-action" /> Rename Document
                    </h3>
                    <button @click="isEditNameModalOpen = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <X class="w-6 h-6" />
                    </button>
                </div>

                <form @submit.prevent="submitNameUpdate" class="p-10 space-y-10">
                    
                    <div class="group">
                        <div class="flex justify-between items-end mb-4">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] group-focus-within:text-slate-900 transition-colors">
                                Display Name 
                                <span class="text-slate-300 font-normal lowercase italic ml-1">— required</span>
                            </label>
                            
                            <span :class="[
                                editNameForm.display_name.length === 0 ? 'text-slate-200' :
                                editNameForm.display_name.length > 100 ? 'text-red-500' : 'text-emerald-500'
                            ]" class="text-[10px] font-mono font-bold transition-colors">
                                {{ editNameForm.display_name.length }} / 100
                            </span>
                        </div>
                        
                        <input 
                            v-model="editNameForm.display_name" 
                            type="text" 
                            maxlength="100"
                            class="w-full text-3xl font-black text-slate-900 tracking-tight uppercase border-b-2 bg-transparent outline-none transition-all py-3 placeholder:text-slate-100" 
                            :class="editNameForm.errors.display_name ? 'border-red-500' : 'border-slate-200 focus:border-slate-900'"
                            placeholder="ENTER NEW NAME..."
                            required 
                        />
                        
                        <div v-if="editNameForm.errors.display_name" class="text-red-500 text-[10px] font-bold uppercase mt-3 tracking-widest flex items-center gap-2">
                            <AlertCircle class="w-4 h-4" /> {{ editNameForm.errors.display_name }}
                        </div>
                    </div>

                    <div class="flex justify-end gap-6 pt-6 border-t border-slate-50">
                        <Button 
                            variant="ghost" 
                            type="button" 
                            @click="isEditNameModalOpen = false" 
                            class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 hover:text-red-500 transition-colors"
                        >
                            Cancel
                        </Button>
                        <Button 
                            type="submit" 
                            :disabled="editNameForm.processing || editNameForm.display_name.trim().length === 0" 
                            class="bg-slate-900 text-white font-bold text-[10px] uppercase tracking-[0.2em] px-12 py-5 h-auto shadow-2xl rounded-none hover:bg-teal-700 disabled:opacity-30 transition-all"
                        >
                            {{ editNameForm.processing ? 'Updating...' : 'Save Name' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="isDeleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-none shadow-2xl p-8 max-w-sm w-full border border-slate-200">
                <h2 class="text-lg font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2"><Trash2 class="w-5 h-5 text-red-500" /> Confirm</h2>
                <p class="mt-4 text-slate-600 text-sm leading-relaxed font-medium">Are you sure you want the AI to forget this document?</p>
                <div class="mt-8 flex justify-end gap-3">
                    <Button variant="ghost" @click="isDeleteModalOpen = false" class="text-[10px] font-bold uppercase tracking-widest">Cancel</Button>
                    <Button @click="confirmDelete" class="bg-red-600 hover:bg-red-700 text-white text-[10px] font-bold uppercase tracking-widest px-8 shadow-md">Confirm</Button>
                </div>
            </div>
        </div>

        <div v-if="isPromptModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-none shadow-2xl w-full max-w-2xl border border-slate-200 overflow-hidden animate-in fade-in zoom-in duration-200">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <h3 class="font-bold text-slate-800 uppercase tracking-widest flex items-center gap-2 text-sm"><Bot class="w-5 h-5 text-action" /> Edit AI Instructions</h3>
                    <button @click="isPromptModalOpen = false" class="text-slate-400 hover:text-slate-600 transition-colors"><X class="w-5 h-5" /></button>
                </div>
                <div class="p-8">
                    <textarea v-model="promptForm.instruction" rows="8" class="w-full p-4 text-sm text-gray-700 bg-slate-50 border border-slate-200 rounded-none focus:ring-1 focus:ring-action outline-none transition"></textarea>
                    <div class="mt-8 flex justify-end gap-3">
                        <Button variant="ghost" @click="isPromptModalOpen = false" class="text-[10px] font-bold uppercase tracking-widest">Cancel</Button>
                        <Button @click="savePrompt" :disabled="promptForm.processing" class="bg-action hover:opacity-90 text-white px-8 py-3 text-[10px] font-bold uppercase tracking-widest shadow-md">
                            <Save class="w-4 h-4 mr-2" /> Save Changes
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>

<style scoped>
.toast-enter-active, .toast-leave-active { transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.toast-enter-from { transform: translateX(100%); opacity: 0; }
.toast-leave-to { transform: translateY(-20px); opacity: 0; }
</style>