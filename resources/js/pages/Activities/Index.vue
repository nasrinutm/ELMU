<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Search, Plus, Gamepad2, Pencil, Trash2, Calendar, Clock,
    Shapes, FileText, CheckCircle, Eye, X, AlertCircle
} from 'lucide-vue-next';
import debounce from 'lodash/debounce';
import { route } from 'ziggy-js';

const props = defineProps<{
    activities: { data: Array<any>; links: Array<any>; };
    filters: { search?: string; };
    can: { manage_activities: boolean; };
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Classroom Activities', href: route('activities.index') },
];

const search = ref(props.filters?.search || '');

// Search Logic
const updateSearch = debounce(() => {
    router.get(route('activities.index'), { search: search.value }, { preserveState: true, replace: true });
}, 300);

watch(search, updateSearch);

const formatDate = (d: string) => new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });

const isDeleteModalOpen = ref(false);
const activityToDelete = ref<number | null>(null);

const openDeleteModal = (id: number) => {
    activityToDelete.value = id;
    isDeleteModalOpen.value = true;
};

const confirmDelete = () => {
    if (activityToDelete.value) {
        router.delete(route('activities.destroy', activityToDelete.value), {
            onFinish: () => {
                isDeleteModalOpen.value = false;
                activityToDelete.value = null;
            }
        });
    }
};

// Icon & Color Helper
const getActivityTypeStyle = (type: string | undefined) => {
    const t = (type || '').toLowerCase();
    if (t.includes('game') || t.includes('quiz')) return { icon: Gamepad2, class: 'bg-purple-100 text-purple-600 border-purple-200' };
    if (t.includes('lab')) return { icon: Shapes, class: 'bg-blue-100 text-blue-600 border-blue-200' };
    return { icon: FileText, class: 'bg-teal-50 text-teal-600 border-teal-100' };
};
</script>

<template>
    <Head title="Classroom Activities" />

    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-slate-50 p-6 space-y-6">

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
                        <Gamepad2 class="w-7 h-7 text-teal-600" />
                        Classroom Activities
                    </h1>
                    <p class="text-slate-500 mt-1 text-sm">Manage assignments, games, and exercises.</p>
                </div>

                <Link v-if="can.manage_activities" :href="route('activities.create')">
                    <Button class="w-full md:w-auto bg-teal-600 hover:bg-teal-700 text-white gap-2">
                        <Plus class="w-4 h-4" /> Create New
                    </Button>
                </Link>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <div class="relative w-full">
                    <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                    <Input v-model="search" placeholder="Search activities..." class="pl-9 bg-slate-50 border-slate-200 focus:bg-white w-full" />
                </div>
            </div>

            <div class="grid gap-4">
                <div v-if="activities.data.length === 0" class="text-center p-12 bg-white rounded-xl border border-slate-200 text-slate-500">
                    <div class="h-16 w-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <Shapes class="w-8 h-8 text-slate-400" />
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">No activities found</h3>
                </div>

                <div v-for="activity in activities.data" :key="activity.id" class="group bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md hover:border-teal-300 transition-all flex flex-col md:flex-row gap-5 items-start md:items-center">

                    <div class="shrink-0 hidden md:block">
                         <div class="h-12 w-12 rounded-lg flex items-center justify-center border" :class="getActivityTypeStyle(activity.type).class">
                            <component :is="getActivityTypeStyle(activity.type).icon" class="w-6 h-6" />
                        </div>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-1">
                            <h3 class="text-lg font-bold text-slate-900 truncate group-hover:text-teal-700 transition-colors">
                                {{ activity.title }}
                            </h3>
                            <Badge variant="outline" class="uppercase text-[10px] tracking-wider bg-slate-50 text-slate-600 border-slate-200">
                                {{ activity.type || 'Activity' }}
                            </Badge>
                        </div>
                        <p class="text-slate-500 text-sm line-clamp-1 mb-3">{{ activity.description || 'No description provided.' }}</p>

                        <div class="flex flex-wrap items-center gap-4 text-xs font-medium text-slate-400">
                            <span class="flex items-center gap-1.5 text-slate-600">
                                <Clock class="w-3.5 h-3.5 text-teal-500" /> Posted: {{ formatDate(activity.created_at) }}
                            </span>
                            <span v-if="activity.due_date" class="flex items-center gap-1.5 text-orange-700 bg-orange-50 px-2 py-0.5 rounded-full border border-orange-100">
                                <Calendar class="w-3.5 h-3.5" /> Due: {{ formatDate(activity.due_date) }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 border-l border-slate-100 pl-4 ml-2 md:self-center self-end w-full md:w-auto justify-end md:justify-start">

                        <Link :href="route('activities.show', activity.id)">
                            <Button size="sm" variant="outline" class="text-teal-600 border-teal-200 hover:bg-teal-50 gap-2">
                                <Eye class="w-4 h-4" /> View Details
                            </Button>
                        </Link>

                        <template v-if="can.manage_activities">
                            <Link :href="route('activities.edit', activity.id)">
                                <Button variant="ghost" size="icon" class="h-8 w-8 text-slate-400 hover:text-teal-600 hover:bg-teal-50">
                                    <Pencil class="w-4 h-4" />
                                </Button>
                            </Link>
                            <Button variant="ghost" size="icon" @click="openDeleteModal(activity.id)">
                                <Trash2 class="w-4 h-4 text-red-500" />
                            </Button>
                        </template>

                        <template v-else>
                            <div v-if="activity.type === 'Submission' && activity.my_submission">
                                <Badge class="bg-green-100 text-green-700 border-green-200 gap-1 px-3 py-1 ml-2">
                                    <CheckCircle class="w-3 h-3" /> Submitted
                                </Badge>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div v-if="activities.links && activities.links.length > 3" class="flex justify-center pt-4">
                <div class="flex flex-wrap gap-1">
                    <template v-for="(link, key) in activities.links" :key="key">
                        <div v-if="!link.url" class="px-3 py-1 text-sm text-slate-400 border border-slate-200 rounded-md bg-white" v-html="link.label" />
                        <Link v-else class="px-3 py-1 text-sm border rounded-md transition-all shadow-sm" :class="link.active ? 'bg-teal-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-50'" :href="link.url" v-html="link.label" />
                    </template>
                </div>
            </div>
         </div>
         
         <div v-if="isDeleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white rounded-none shadow-2xl p-8 max-w-sm w-full border border-slate-200 animate-in fade-in zoom-in duration-200">
                <h2 class="text-lg font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2">
                    <Trash2 class="w-5 h-5 text-red-500" /> Confirm Delete
                </h2>
                <p class="mt-4 text-slate-600 text-sm leading-relaxed font-medium">
                    Are you sure you want to delete this activity? This will permanently remove all student submissions associated with it.
                </p>
                <div class="mt-8 flex justify-end gap-3">
                    <Button variant="ghost" @click="isDeleteModalOpen = false" class="text-[10px] font-bold uppercase tracking-widest">Cancel</Button>
                    <Button @click="confirmDelete" class="bg-red-600 hover:bg-red-700 text-white text-[10px] font-bold uppercase tracking-widest px-8 shadow-md">Confirm</Button>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
