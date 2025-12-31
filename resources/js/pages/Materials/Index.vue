<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
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
    ArrowUpDown
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

const deleteMaterial = (id: number) => {
    if (confirm('Are you sure you want to delete this material? This action cannot be undone.')) {
        router.delete(route('materials.destroy', id));
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
        <div class="min-h-screen bg-slate-50 p-4 sm:p-6 space-y-6">

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                        Learning Materials
                    </h1>
                    <p class="text-slate-500 mt-1">
                        Access and manage course documents, assignments, and notes.
                    </p>
                </div>

                <Link v-if="can.manage_materials" :href="route('materials.create')">
                    <Button class="w-full md:w-auto bg-teal-600 hover:bg-teal-700 text-white shadow-sm gap-2 font-medium">
                        <Upload class="w-4 h-4" /> Upload Material
                    </Button>
                </Link>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row gap-4">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                    <Input
                        v-model="search"
                        placeholder="Search by filename or subject..."
                        class="pl-9 bg-slate-50 border-slate-200 focus:bg-white focus:ring-teal-500 focus:border-teal-500 transition-all h-10"
                    />
                </div>

                <div class="relative w-full md:w-48">
                    <Filter class="absolute left-3 top-2.5 h-4 w-4 text-slate-400 pointer-events-none" />
                    <select
                        v-model="typeFilter"
                        class="w-full h-10 pl-9 pr-8 rounded-md border border-slate-200 bg-slate-50 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500 cursor-pointer appearance-none"
                    >
                        <option value="">All Types</option>
                        <option value="pdf">PDF Documents</option>
                        <option value="docx">Word Documents</option>
                        <option value="pptx">Presentations</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="relative w-full md:w-48">
                    <ArrowUpDown class="absolute left-3 top-2.5 h-4 w-4 text-slate-400 pointer-events-none" />
                    <select
                        v-model="sortOrder"
                        class="w-full h-10 pl-9 pr-4 rounded-md border border-slate-200 bg-slate-50 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500 cursor-pointer appearance-none"
                    >
                        <option value="latest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="a-z">Name (A-Z)</option>
                    </select>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-xs font-semibold">
                            <tr>
                                <th class="px-6 py-4">Document Details</th>
                                <th class="px-6 py-4">Subject</th>
                                <th class="px-6 py-4">Uploaded By</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="materials.data.length === 0">
                                <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                    <div class="flex flex-col items-center">
                                        <div class="h-12 w-12 rounded-full bg-slate-100 flex items-center justify-center mb-3">
                                            <FileText class="w-6 h-6 text-slate-400" />
                                        </div>
                                        <p class="font-medium text-slate-900">No materials found.</p>
                                        <p class="text-sm mt-1">Try adjusting your filters or search.</p>
                                    </div>
                                </td>
                            </tr>
                            <tr
                                v-for="material in materials.data"
                                :key="material.id"
                                class="group hover:bg-slate-50/80 transition-colors"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="h-10 w-10 rounded-lg flex items-center justify-center shrink-0 border"
                                            :class="getFileIcon(material.file_type).class"
                                        >
                                            <component :is="getFileIcon(material.file_type).icon" class="w-5 h-5" />
                                        </div>

                                        <div class="min-w-0">
                                            <div class="font-bold text-slate-900 truncate max-w-xs group-hover:text-teal-700 transition-colors">
                                                {{ material.name }}
                                            </div>
                                            <div class="text-xs text-slate-500 flex items-center gap-2 mt-0.5">
                                                <Badge variant="outline" class="rounded px-1.5 py-0 border-slate-200 text-slate-500 text-[10px] uppercase bg-white">
                                                    {{ material.file_type }}
                                                </Badge>
                                                <span class="text-slate-300">•</span>
                                                <span>{{ formatDate(material.created_at) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ material.subject }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="h-6 w-6 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center text-xs font-bold">
                                            {{ material.user.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <span class="text-slate-600 truncate text-sm font-medium">{{ material.user.name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a
                                            :href="route('materials.download', material.id)"
                                            class="p-2 text-slate-400 hover:text-teal-600 hover:bg-teal-50 rounded-md transition border border-transparent hover:border-teal-100"
                                            title="Download"
                                        >
                                            <Download class="w-4 h-4" />
                                        </a>
                                        <template v-if="can.manage_materials">
                                            <Link
                                                :href="route('materials.edit', material.id)"
                                                class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-md transition border border-transparent hover:border-blue-100"
                                                title="Edit"
                                            >
                                                <Pencil class="w-4 h-4" />
                                            </Link>
                                            <button
                                                @click="deleteMaterial(material.id)"
                                                class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-md transition border border-transparent hover:border-red-100"
                                                title="Delete"
                                            >
                                                <Trash2 class="w-4 h-4" />
                                            </button>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="materials.links.length > 3" class="flex justify-center pt-2">
                <div class="flex flex-wrap gap-1">
                    <template v-for="(link, key) in materials.links" :key="key">
                        <div
                            v-if="!link.url"
                            class="px-3 py-1 text-sm text-slate-400 border border-slate-200 rounded-md bg-white shadow-sm"
                            v-html="link.label"
                        />
                        <Link
                            v-else
                            class="px-3 py-1 text-sm border rounded-md transition-all shadow-sm"
                            :class="{
                                'bg-teal-600 text-white border-teal-600 font-medium': link.active,
                                'bg-white text-slate-600 border-slate-200 hover:bg-slate-50 hover:border-slate-300': !link.active
                            }"
                            :href="link.url"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
