<script setup lang="ts">
import { ref, watch, computed, nextTick } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    FileText,
    Upload,
    Search,
    Filter,
    Download,
    Pencil,
    Trash2,
    File,
    FileJson,
    FileType,
    ArrowUpDown,
    X,
    CheckCircle2,
    AlertTriangle,
    AlertCircle,
    Lock
} from 'lucide-vue-next';
import debounce from 'lodash/debounce';
import { route } from 'ziggy-js';

const props = defineProps<{
    materials: {
        data: Array<{
            id: number;
            name: string;
            subject: string;
            file_type: string;
            created_at: string;
            user_id: number; // Required for ownership check
            user: { name: string };
        }>;
        links: Array<any>;
    };
    filters: {
        search?: string;
        type?: string;
        sort?: string;
    };
    can: {
        manage_materials: boolean;
    };
}>();

// --- NOTIFICATION & MODAL STATE ---
const page = usePage();
const authUser = computed(() => (page.props as any).auth.user);

// FLASH HANDLERS
const flashSuccess = computed(() => (page.props as any).flash?.success);
const flashError = computed(() => (page.props as any).flash?.error);

const showSuccessNotification = ref(false);
const showErrorNotification = ref(false);
const showDeleteModal = ref(false);
const materialToDelete = ref<number | null>(null);

// Ownership Logic
const canModify = (materialUserId: number) => {
    return authUser.value.id === materialUserId || authUser.value.role === 'admin';
};

// RESET HANDSHAKE: Watch Success Flash
watch(flashSuccess, async (newVal) => {
    if (newVal) {
        showSuccessNotification.value = false;
        await nextTick();
        showSuccessNotification.value = true;
        setTimeout(() => {
            showSuccessNotification.value = false;
            (page.props as any).flash.success = null;
        }, 5000);
    }
}, { immediate: true });

// RESET HANDSHAKE: Watch Error Flash
watch(flashError, async (newVal) => {
    if (newVal) {
        showErrorNotification.value = false;
        await nextTick();
        showErrorNotification.value = true;
        setTimeout(() => {
            showErrorNotification.value = false;
            (page.props as any).flash.error = null;
        }, 5000);
    }
}, { immediate: true });

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Materials', href: route('materials.index') },
];

const search = ref(props.filters.search || '');
const typeFilter = ref(props.filters.type || '');
const sortOrder = ref(props.filters.sort || 'latest');

const updateFilters = debounce(() => {
    router.get(route('materials.index'), {
        search: search.value,
        type: typeFilter.value,
        sort: sortOrder.value
    }, {
        preserveState: true,
        replace: true
    });
}, 300);

watch([search, typeFilter, sortOrder], () => {
    updateFilters();
});

const openDeleteModal = (id: number) => {
    materialToDelete.value = id;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (materialToDelete.value) {
        router.delete(route('materials.destroy', materialToDelete.value), {
            onFinish: () => {
                showDeleteModal.value = false;
                materialToDelete.value = null;
            }
        });
    }
};

const getFileIcon = (type: string) => {
    const t = (type || '').toLowerCase();
    if (t.includes('pdf')) return { icon: FileText, class: 'text-red-600 bg-red-50 border-red-100' };
    if (t.includes('doc') || t.includes('word')) return { icon: FileType, class: 'text-blue-600 bg-blue-50 border-blue-100' };
    if (t.includes('xls') || t.includes('sheet') || t.includes('csv')) return { icon: FileJson, class: 'text-green-600 bg-green-50 border-green-100' };
    if (t.includes('ppt')) return { icon: File, class: 'text-orange-600 bg-orange-50 border-orange-100' };
    return { icon: File, class: 'text-slate-500 bg-slate-50 border-slate-100' };
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric'
    });
};
</script>

<template>
    <Head title="Learning Materials" />

    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-slate-50 p-4 sm:p-6 space-y-6 relative">

            <transition name="toast">
                <div v-if="showSuccessNotification" class="fixed top-10 right-10 z-[100] flex items-center gap-4 bg-slate-900 text-white p-5 shadow-2xl border-l-4 border-emerald-500 min-w-[350px]">
                    <div class="bg-emerald-500/20 p-2"><CheckCircle2 class="w-6 h-6 text-emerald-500" /></div>
                    <div class="flex-grow">
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-emerald-500">System Success</p>
                        <p class="text-sm font-medium">{{ flashSuccess }}</p>
                    </div>
                    <button @click="showSuccessNotification = false" class="text-slate-500 hover:text-white transition"><X class="w-4 h-4" /></button>
                </div>
            </transition>

            <transition name="toast">
                <div v-if="showErrorNotification" class="fixed top-10 right-10 z-[100] flex items-center gap-4 bg-slate-900 text-white p-5 shadow-2xl border-l-4 border-red-500 min-w-[350px]">
                    <div class="bg-red-500/20 p-2"><AlertCircle class="w-6 h-6 text-red-500" /></div>
                    <div class="flex-grow">
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-red-500">Access Restricted</p>
                        <p class="text-sm font-medium">{{ flashError }}</p>
                    </div>
                    <button @click="showErrorNotification = false" class="text-slate-500 hover:text-white transition"><X class="w-4 h-4" /></button>
                </div>
            </transition>

            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 uppercase">Learning Materials</h1>
                    <p class="text-sm text-slate-500 mt-1 italic font-medium">Manage course documents and notes.</p>
                </div>

                <Link v-if="can.manage_materials" :href="route('materials.create')">
                    <Button class="bg-slate-900 text-white shadow-lg font-bold uppercase text-[10px] tracking-widest px-6 py-5 rounded-none hover:bg-teal-700 transition">
                        <Upload class="w-4 h-4 mr-2" /> Upload Material
                    </Button>
                </Link>
            </div>

            <div class="bg-white p-4 rounded-none border border-slate-200 shadow-sm flex flex-col md:flex-row gap-4 font-sans">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                    <Input v-model="search" placeholder="Search materials..." class="pl-9 bg-slate-50 border-slate-200 rounded-none h-10 transition-all focus:ring-slate-900" />
                </div>

                <div class="flex gap-4">
                    <select v-model="typeFilter" class="h-10 px-4 rounded-none border border-slate-200 bg-slate-50 text-sm text-slate-700 cursor-pointer outline-none focus:ring-1 focus:ring-slate-900">
                        <option value="">All Types</option>
                        <option value="pdf">PDF</option>
                        <option value="docx">Word</option>
                    </select>
                    <select v-model="sortOrder" class="h-10 px-4 rounded-none border border-slate-200 bg-slate-50 text-sm text-slate-700 cursor-pointer outline-none focus:ring-1 focus:ring-slate-900">
                        <option value="latest">Newest First</option>
                        <option value="a-z">A-Z</option>
                    </select>
                </div>
            </div>

            <div class="bg-white rounded-none border border-slate-200 shadow-sm overflow-hidden font-sans">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-widest font-bold">
                        <tr>
                            <th class="px-6 py-4">Document Details</th>
                            <th class="px-6 py-4">Subject</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-sans">
                        <tr v-if="materials.data.length === 0">
                            <td colspan="3" class="px-6 py-12 text-center text-slate-400 italic">No materials found.</td>
                        </tr>
                        <tr v-for="material in materials.data" :key="material.id" class="group hover:bg-slate-50 transition-colors border-l-2 border-transparent hover:border-teal-500">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="h-10 w-10 rounded-none flex items-center justify-center shrink-0 border" :class="getFileIcon(material.file_type).class">
                                        <component :is="getFileIcon(material.file_type).icon" class="w-5 h-5" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 uppercase tracking-tight truncate max-w-xs">{{ material.name }}</div>
                                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ material.file_type }} • {{ formatDate(material.created_at) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 text-[10px] font-bold uppercase tracking-widest bg-slate-100 text-slate-600 border border-slate-200">
                                    {{ material.subject }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-sans">
                                <div class="flex items-center justify-end gap-1">
                                    <a :href="route('materials.download', material.id)" class="p-2 text-slate-400 hover:text-teal-600 transition" title="Download">
                                        <Download class="w-4 h-4" />
                                    </a>

                                    <template v-if="can.manage_materials && canModify(material.user_id)">
                                        <Link :href="route('materials.edit', material.id)" class="p-2 text-slate-400 hover:text-blue-600 transition" title="Edit">
                                            <Pencil class="w-4 h-4" />
                                        </Link>
                                        <button @click="openDeleteModal(material.id)" class="p-2 text-slate-400 hover:text-red-600 transition" title="Delete">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </template>

                                    <template v-else-if="can.manage_materials">
                                        <div class="p-2 text-slate-200 cursor-not-allowed" title="Unauthorized: Another Teacher's Material">
                                            <Lock class="w-4 h-4" />
                                        </div>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <transition name="modal">
            <div v-if="showDeleteModal" class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm font-sans">
                <div class="bg-white max-w-md w-full p-10 shadow-2xl border border-slate-200 rounded-none">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mb-6">
                            <AlertTriangle class="w-8 h-8 text-red-500" />
                        </div>
                        <h3 class="text-sm font-bold uppercase tracking-[0.2em] text-slate-900 mb-2">Remove Material</h3>
                        <p class="text-sm text-slate-500 font-medium mb-8 leading-relaxed">
                            Are you sure you want to delete this resource? This will remove the file permanently for all students.
                        </p>
                        <div class="flex gap-4 w-full">
                            <button @click="showDeleteModal = false" class="flex-1 py-4 text-[10px] font-bold uppercase tracking-widest text-slate-400 border border-slate-100 hover:bg-slate-50 transition">
                                Cancel
                            </button>
                            <button @click="confirmDelete" class="flex-1 py-4 bg-red-600 text-white text-[10px] font-bold uppercase tracking-widest hover:bg-red-700 shadow-lg transition font-sans">
                                Delete Now
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

    </AppSidebarLayout>
</template>

<style scoped>
/* Standardizing transition for the dash border */
.border-dashed { transition: all 0.2s ease-in-out; }

/* Success Toast Animation */
.toast-enter-active, .toast-leave-active { transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.toast-enter-from { transform: translateX(100%); opacity: 0; }
.toast-leave-to { transform: translateY(-20px); opacity: 0; }

/* Modal Fade Animation */
.modal-enter-active, .modal-leave-active { transition: opacity 0.3s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>
