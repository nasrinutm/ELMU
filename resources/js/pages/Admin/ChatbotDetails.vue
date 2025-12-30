<script setup>
import { ref, computed } from 'vue';
import { useForm, Head, Link, router } from '@inertiajs/vue3'; 
import AppLayout from '@/layouts/AppLayout.vue';
import { 
    Server, Database, Cpu, Trash2, FileText, Plus, 
    Search, ChevronUp, ChevronDown, X, Bot, Info, Edit2, Save, XCircle 
} from 'lucide-vue-next';

const props = defineProps({
    files: Array,
    systemInfo: Object,
});

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'AI Details', href: route('chatbot.details') },
];

// --- MODAL STATE ---
const isPromptModalOpen = ref(false);

// 2. INITIALIZE FORM
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

// --- SEARCH & SORT STATE ---
const searchQuery = ref('');
const sortKey = ref('created_at'); // Default sort field
const sortOrder = ref('desc');     // Default sort direction
const isDeleteModalOpen = ref(false);
const fileToDelete = ref(null);

// --- SEARCH & SORT LOGIC ---
const filteredFiles = computed(() => {
    let result = [...props.files];

    // 1. Search filter (Checks Display Name and Mime Type)
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(file => 
            file.display_name?.toLowerCase().includes(query) ||
            file.mime_type?.toLowerCase().includes(query)
        );
    }

    // 2. Sort logic
    result.sort((a, b) => {
        let valA = a[sortKey.value];
        let valB = b[sortKey.value];

        if (valA === null) return 1;
        if (valB === null) return -1;

        let modifier = sortOrder.value === 'desc' ? -1 : 1;
        if (valA < valB) return -1 * modifier;
        if (valA > valB) return 1 * modifier;
        return 0;
    });

    return result;
});

const sortBy = (key) => {
    if (sortKey.value === key) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = key;
        sortOrder.value = 'asc';
    }
};

// --- ACTIONS ---
const deleteFile = (file) => {
    // Uses the Gemini document name for deletion
    fileToDelete.value = file.name; 
    isDeleteModalOpen.value = true;
};

const confirmDelete = () => {
    isDeleteModalOpen.value = false;
    if (fileToDelete.value) {
        router.delete(route('upload.delete', fileToDelete.value), {
            preserveScroll: true,
        });
    }
    fileToDelete.value = null;
};

const formatBytes = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};
</script>

<template>
    <Head title="AI Chatbot Details" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-4 md:p-8 w-full">
            
            <div class="border-b border-gray-100 pb-4">
                <h1 class="text-2xl font-bold text-gray-900">AI Knowledge Center</h1>
                <p class="text-sm text-gray-500">Manage your system model, behavioral prompt, and training data.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div v-for="(stat, index) in [
                    { label: 'Current Model', val: systemInfo.model, icon: Cpu, color: 'text-purple-600', bg: 'bg-purple-100' },
                    { label: 'Knowledge Store', val: systemInfo.store_id, icon: Database, color: 'text-green-600', bg: 'bg-green-100' },
                    { label: 'Indexed Documents', val: files.length + ' Files', icon: FileText, color: 'text-blue-600', bg: 'bg-blue-100' }
                ]" :key="index" class="p-6 bg-white rounded-xl shadow-sm border border-gray-100 flex items-center gap-4 transition-all hover:shadow-md">
                    <div :class="['p-3 rounded-lg', stat.bg, stat.color]">
                        <component :is="stat.icon" class="w-6 h-6" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">{{ stat.label }}</p>
                        <h3 class="text-lg font-bold text-gray-900 truncate break-all">{{ stat.val }}</h3>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <Bot class="w-5 h-5 text-purple-600" />
                        <h3 class="font-bold text-gray-900">System Behavior Prompt</h3>
                    </div>
                    
                    <button 
                        @click="openEditModal" 
                        class="text-xs flex items-center gap-1.5 font-bold bg-white text-blue-600 border border-gray-200 hover:bg-gray-50 px-3 py-1.5 rounded-lg shadow-sm transition"
                    >
                        <Edit2 class="w-3.5 h-3.5" />
                        Update Instructions
                    </button>
                </div>

                <div class="p-6">
                    <div class="bg-indigo-50/30 p-5 rounded-xl border border-indigo-100 border-dashed">
                        <p class="text-sm text-gray-700 leading-relaxed italic whitespace-pre-wrap">
                            "{{ systemInfo.current_prompt }}"
                        </p>
                    </div>
                </div>
            </div>

             <div class="flex flex-col md:flex-row justify-between items-center gap-4 w-full">
                <div class="relative max-w-md w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <Search class="h-4 w-4 text-gray-400" />
                    </div>
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Search by filename or type..." 
                        class="block w-full pl-10 pr-10 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition shadow-sm"
                    />
                    <button 
                        v-if="searchQuery" 
                        @click="searchQuery = ''" 
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="flex-shrink-0">
                    <Link 
                        :href="route('upload.create')" 
                        class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition shadow-sm whitespace-nowrap"
                    >
                        <Plus class="w-4 h-4" />
                        Add New Material
                    </Link>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden w-full">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50">
                    <h3 class="font-bold text-gray-900">Active Knowledge Base</h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-100 text-gray-700 uppercase text-xs font-semibold">
                            <tr>
                                <th @click="sortBy('display_name')" class="px-6 py-3 cursor-pointer hover:bg-gray-200 transition group">
                                    <div class="flex items-center gap-1">
                                        File Name
                                        <ChevronUp v-if="sortKey === 'display_name' && sortOrder === 'asc'" class="w-3 h-3 text-blue-600" />
                                        <ChevronDown v-else-if="sortKey === 'display_name' && sortOrder === 'desc'" class="w-3 h-3 text-blue-600" />
                                        <ChevronUp v-else class="w-3 h-3 text-gray-300 opacity-0 group-hover:opacity-100" />
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-xs text-gray-400 font-normal italic">Gemini ID</th>
                                <th @click="sortBy('size_bytes')" class="px-6 py-3 cursor-pointer hover:bg-gray-200 transition group">
                                    <div class="flex items-center gap-1">
                                        Size
                                        <ChevronUp v-if="sortKey === 'size_bytes' && sortOrder === 'asc'" class="w-3 h-3 text-blue-600" />
                                        <ChevronDown v-else-if="sortKey === 'size_bytes' && sortOrder === 'desc'" class="w-3 h-3 text-blue-600" />
                                        <ChevronUp v-else class="w-3 h-3 text-gray-300 opacity-0 group-hover:opacity-100" />
                                    </div>
                                </th>
                                <th class="px-6 py-3">Type</th>
                                <th @click="sortBy('state')" class="px-6 py-3 cursor-pointer hover:bg-gray-200 transition group">
                                    <div class="flex items-center gap-1">
                                        Status
                                        <ChevronUp v-if="sortKey === 'state' && sortOrder === 'asc'" class="w-3 h-3 text-blue-600" />
                                        <ChevronDown v-else-if="sortKey === 'state' && sortOrder === 'desc'" class="w-3 h-3 text-blue-600" />
                                        <ChevronUp v-else class="w-3 h-3 text-gray-300 opacity-0 group-hover:opacity-100" />
                                    </div>
                                </th>
                                <th @click="sortBy('created_at')" class="px-6 py-3 cursor-pointer hover:bg-gray-200 transition group">
                                    <div class="flex items-center gap-1">
                                        Uploaded
                                        <ChevronUp v-if="sortKey === 'created_at' && sortOrder === 'asc'" class="w-3 h-3 text-blue-600" />
                                        <ChevronDown v-else-if="sortKey === 'created_at' && sortOrder === 'desc'" class="w-3 h-3 text-blue-600" />
                                        <ChevronUp v-else class="w-3 h-3 text-gray-300 opacity-0 group-hover:opacity-100" />
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="file in filteredFiles" :key="file.name" class="hover:bg-blue-50/30 transition">
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ file.display_name }} 
                                </td>
                                <td class="px-6 py-4">
                                    <code class="text-xs bg-gray-50 px-2 py-1 rounded text-gray-400 font-mono">
                                        {{ file.name.split('/').pop() }}...
                                    </code>
                                </td>
                                <td class="px-6 py-4 text-gray-500">
                                    {{ formatBytes(file.size_bytes) }}
                                </td>
                                <td class="px-6 py-4 text-gray-500">
                                    <span class="px-2 py-1 bg-gray-100 rounded text-xs">
                                        {{ file.mime_type?.split('/')[1] || 'File' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span 
                                        class="px-2 py-1 rounded-full text-xs font-bold"
                                        :class="file.state === 'ACTIVE' || file.state === 'STATE_ACTIVE' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'"
                                    >
                                        {{ file.state }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-500">
                                    {{ file.created_at }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button 
                                        @click="deleteFile(file)" 
                                        class="text-red-500 hover:text-red-700 p-2 hover:bg-red-50 rounded-lg transition"
                                        title="Delete from AI">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="filteredFiles.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <Search class="w-8 h-8 text-gray-300" />
                                        <p class="text-gray-500 font-medium">
                                            {{ searchQuery ? `No results found for "${searchQuery}"` : 'The AI has no custom knowledge yet.' }}
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div v-if="isDeleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
            <div class="bg-white rounded-xl shadow-2xl p-6 max-w-sm w-full mx-4 border border-gray-100">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <Trash2 class="w-6 h-6 text-red-500" />
                    Confirm Deletion
                </h2>
                <p class="mt-4 text-gray-600 text-sm leading-relaxed">
                    Are you sure you want the AI to forget this document? This action cannot be undone and the record will be removed from the database.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button 
                        @click="isDeleteModalOpen = false" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button 
                        @click="confirmDelete" 
                        class="px-4 py-2 text-sm font-medium bg-red-600 text-white rounded-lg hover:bg-red-700 shadow-sm"
                    >
                        OK, Delete
                    </button>
                </div>
            </div>
        </div>

        <div v-if="isPromptModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden border border-gray-100 animate-in fade-in zoom-in duration-200">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2">
                        <Bot class="w-5 h-5 text-blue-600" />
                        Edit AI Instructions
                    </h3>
                    <button @click="closeEditModal" class="text-gray-400 hover:text-gray-600">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                
                <div class="p-6">
                    <p class="text-xs text-gray-500 mb-4 italic">
                        The instructions below define how the AI behaves. (e.g., "Answer in Malay", "Be concise").
                    </p>
                    
                    <textarea
                        v-model="promptForm.instruction"
                        rows="8"
                        class="w-full p-4 text-sm text-gray-700 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-inner"
                        placeholder="Enter system instructions..."
                    ></textarea>

                    <div class="mt-6 flex justify-end gap-3">
                        <button 
                            @click="closeEditModal" 
                            class="px-4 py-2 text-sm font-bold text-gray-600 hover:bg-gray-100 rounded-lg transition"
                        >
                            Cancel
                        </button>
                        <button 
                            @click="savePrompt"
                            :disabled="promptForm.processing"
                            class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-bold shadow-lg transition disabled:opacity-50"
                        >
                            <Save class="w-4 h-4" />
                            {{ promptForm.processing ? 'Saving...' : 'Update Instruction' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>