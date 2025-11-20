<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { route } from 'ziggy-js';
import { Download, Edit, Trash2, Upload, FileText, Calendar, Filter } from 'lucide-vue-next';
import { type BreadcrumbItem, type AppPageProps } from '@/types';

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
        date: string | null;
        sort: string;
    };
    can: {
        manage_materials: boolean;
    };
}>();

const page = usePage<AppPageProps>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Materials', href: route('materials.index') },
];

const dateFilter = ref(props.filters.date || '');
const sortFilter = ref(props.filters.sort || 'latest');

const deleteMaterial = (id: number) => {
    if (confirm('Are you sure you want to delete this material?')) {
        router.delete(route('materials.destroy', id));
    }
};

const debounce = (fn: (...args: any[]) => void, delay = 300) => {
    let timeout: ReturnType<typeof setTimeout>;
    return (...args: any[]) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), delay);
    };
};

watch([dateFilter, sortFilter], debounce(([newDate, newSort]) => {
    router.get(
        route('materials.index'),
        { date: newDate, sort: newSort },
        { preserveState: true, replace: true },
    );
}, 300));

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getFileBadgeVariant = (type: string) => {
    return 'default';
};
</script>

<template>
    <Head title="Learning Materials" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-8">

            <!-- Header Section (Fixed Layout) -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="space-y-2">
                    <h1 class="text-3xl font-bold tracking-tight text-[#FFD900]">
                        Learning Materials
                    </h1>
                    <p class="text-[#FFD900]">
                        Access and manage your course documents.
                    </p>
                </div>

                <!-- Restored Upload Button -->
                <Link v-if="can.manage_materials" :href="route('materials.create')">
                    <Button class="bg-[#FFD900] text-[#003366] hover:bg-[#FFD900]/90 font-bold shadow-lg">
                        <Upload class="w-4 h-4 mr-2" />
                        Upload Material
                    </Button>
                </Link>
            </div>

            <Separator class="bg-slate-600/50" />

            <!-- Filters Card -->
            <div class="rounded-xl border-none bg-[#FFD900] p-6 shadow-sm">
                <div class="flex flex-col sm:flex-row gap-6">
                    <div class="w-full sm:w-1/3 space-y-2">
                        <Label for="filterDate" class="text-xs font-bold uppercase tracking-wider text-[#003366]">
                            Filter by Date
                        </Label>
                        <div class="relative bg-[#003366]">
                            <Calendar class="absolute left-3 top-2.5 h-4 w-4 text-white z-10" />
                            <Input
                                id="filterDate"
                                type="date"
                                v-model="dateFilter"
                                class="pl-9  border-none shadow-none text-white placeholder:text-white/70 focus:ring-white rounded-md h-10"
                                style="color-scheme: dark;"
                            />
                        </div>
                    </div>

                    <div class="w-full sm:w-1/3 space-y-2">
                        <Label for="filterSort" class="text-xs font-bold uppercase tracking-wider text-[#003366]">
                            Sort Order
                        </Label>
                        <div class="relative">
                            <Filter class="absolute left-3 top-2.5 h-4 w-4 text-white z-10" />
                            <select
                                id="filterSort"
                                v-model="sortFilter"
                                class="h-10 w-full rounded-md border-none bg-[#003366] pl-9 px-3 text-sm text-white shadow-none focus:outline-none focus:ring-2 focus:ring-white"
                            >
                                <option value="latest">Newest First</option>
                                <option value="oldest">Oldest First</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Materials Table Card -->
            <div class="rounded-xl border-none bg-[#FFD900] shadow-sm overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-[#eecb00] border-b border-[#003366]/20 text-[#003366] uppercase text-xs font-bold tracking-wider">
                        <tr>
                            <th class="p-4 pl-6">Document Name</th>
                            <th class="p-4">Subject</th>
                            <th class="p-4">Type</th>
                            <th class="p-4">Uploaded By</th>
                            <th class="p-4">Date</th>
                            <th class="p-4 text-right pr-6">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#003366]/10">
                        <tr
                            v-for="material in materials.data"
                            :key="material.id"
                            class="group hover:bg-white/30 transition-colors"
                        >
                            <td class="p-4 pl-6">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-lg bg-[#003366] text-white flex items-center justify-center shrink-0 border border-[#003366]/10">
                                        <FileText class="h-5 w-5" />
                                    </div>
                                    <span class="font-semibold text-[#003366]">{{ material.name }}</span>
                                </div>
                            </td>
                            <td class="p-4 text-[#003366]">{{ material.subject }}</td>
                            <td class="p-4">
                                <Badge class="uppercase px-2 bg-[#003366] text-white hover:bg-[#002244] border-none">
                                    {{ material.file_type }}
                                </Badge>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-2">
                                    <div class="h-6 w-6 rounded-full bg-white flex items-center justify-center text-xs font-bold text-[#003366]">
                                        {{ material.user.name.charAt(0) }}
                                    </div>
                                    <span class="text-[#003366]">{{ material.user.name }}</span>
                                </div>
                            </td>
                            <td class="p-4 text-[#003366]/80">{{ formatDate(material.created_at) }}</td>
                            <td class="p-4 text-right pr-6">
                                <div class="flex items-center justify-end gap-2">
                                    <a :href="route('materials.download', material.id)" target="_blank">
                                        <Button
                                            variant="outline"
                                            size="icon"
                                            class="h-8 w-8 bg-[#003366] text-[#FFD900] border-[#003366] hover:bg-[#002244] hover:text-white"
                                        >
                                            <Download class="w-4 h-4" />
                                        </Button>
                                    </a>

                                    <template v-if="can.manage_materials">
                                        <Button variant="ghost" size="icon" class="h-8 w-8 text-[#003366] hover:bg-white/50" as-child>
                                            <Link :href="route('materials.edit', material.id)">
                                                <Edit class="w-4 h-4" />
                                            </Link>
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8 text-[#003366] hover:text-red-600 hover:bg-red-100"
                                            @click="deleteMaterial(material.id)"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </Button>
                                    </template>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="materials.data.length === 0">
                            <td colspan="6" class="p-12 text-center text-[#003366]">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <div class="h-12 w-12 rounded-full bg-white/50 flex items-center justify-center mb-2">
                                        <FileText class="h-6 w-6 text-[#003366]" />
                                    </div>
                                    <p class="font-medium">No materials found.</p>
                                    <p class="text-sm opacity-80">Try adjusting your filters or check back later.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="materials.links.length > 3" class="mt-4 flex justify-center">
                <div class="flex flex-wrap gap-1">
                    <template v-for="(link, key) in materials.links" :key="key">
                        <div v-if="link.url === null" class="px-3 py-1 text-sm text-[#003366]/50 border border-[#003366]/20 rounded bg-[#FFD900]" v-html="link.label" />
                        <Link v-else
                              class="px-3 py-1 text-sm border rounded transition-colors"
                              :class="{
                                  'bg-[#003366] text-[#FFD900] border-[#003366] font-bold': link.active,
                                  'bg-white text-[#003366] border-white hover:bg-white/90': !link.active
                              }"
                              :href="link.url"
                              v-html="link.label" />
                    </template>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
