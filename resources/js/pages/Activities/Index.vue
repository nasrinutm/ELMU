<script setup lang="ts">
import { ref, watch, computed, nextTick } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Search, Plus, Gamepad2, Pencil, Trash2, Calendar, Clock,
    Shapes, FileText, CheckCircle, Eye, X, CheckCircle2, AlertCircle
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

// --- NOTIFICATION & MODAL STATE ---
const page = usePage();
const flashSuccess = computed(() => (page.props as any).flash?.success);
const flashError = computed(() => (page.props as any).flash?.error);

const showSuccessNotification = ref(false);
const showErrorNotification = ref(false);
const showDeleteModal = ref(false);
const activityToDelete = ref<number | null>(null);

// --- SEARCH LOGIC ---
const search = ref(props.filters?.search || '');
const updateSearch = debounce(() => {
    router.get(route('activities.index'), { search: search.value }, { preserveState: true, replace: true });
}, 300);
watch(search, updateSearch);

// --- FLASH WATCHERS ---
watch(flashSuccess, async (newVal) => {
    if (newVal) {
        showSuccessNotification.value = false;
        await nextTick();
        showSuccessNotification.value = true;
        setTimeout(() => { showSuccessNotification.value = false; }, 5000);
    }
}, { immediate: true });

watch(flashError, async (newVal) => {
    if (newVal) {
        showErrorNotification.value = false;
        await nextTick();
        showErrorNotification.value = true;
        setTimeout(() => { showErrorNotification.value = false; }, 5000);
    }
}, { immediate: true });

// --- ACTIONS ---
const openDeleteModal = (id: number) => {
    activityToDelete.value = id;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (activityToDelete.value) {
        router.delete(route('activities.destroy', activityToDelete.value), {
            onFinish: () => {
                showDeleteModal.value = false;
                activityToDelete.value = null;
            }
        });
    }
};

const formatDate = (d: string) => new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });

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
        <div class="min-h-screen bg-slate-50 p-6 space-y-6 relative">

            <transition name="toast">
                <div v-if="showSuccessNotification" class="fixed top-10 right-10 z-[100] flex items-center gap-4 bg-slate-900 text-white p-5 shadow-2xl border-l-4 border-emerald-500 min-w-[350px]">
                    <div class="bg-emerald-500/20 p-2"><CheckCircle2 class="w-6 h-6 text-emerald-500" /></div>
                    <div class="flex-grow">
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-emerald-500">Success</p>
                        <p class="text-sm font-medium">{{ flashSuccess }}</p>
                    </div>
                    <button @click="showSuccessNotification = false" class="text-slate-500 hover:text-white transition"><X class="w-4 h-4" /></button>
                </div>
            </transition>

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900 uppercase">Classroom Activities</h1>
                    <p class="text-slate-500 mt-1 text-sm italic font-medium">Manage assignments, games, and exercises.</p>
                </div>

                <Link v-if="can.manage_activities" :href="route('activities.create')">
                    <Button class="bg-slate-900 hover:bg-teal-700 text-white font-bold uppercase text-[10px] tracking-widest px-8 py-5 rounded-none shadow-lg transition-all">
                        <Plus class="w-4 h-4 mr-2" /> Create New
                    </Button>
                </Link>
            </div>

            <div class="bg-white p-4 rounded-none border border-slate-200 shadow-sm flex gap-4">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                    <Input v-model="search" placeholder="Search activities..." class="pl-9 bg-slate-50 border-slate-200 rounded-none h-10 transition-all focus:ring-slate-900" />
                </div>
            </div>

            <div class="grid gap-4">
                <div v-if="activities.data.length === 0" class="text-center p-20 bg-white border border-slate-200">
                    <Shapes class="w-12 h-12 text-slate-200 mx-auto mb-4" />
                    <h3 class="text-xs font-bold uppercase tracking-widest text-slate-400">No activities found</h3>
                </div>

                <div v-for="activity in activities.data" :key="activity.id" class="group bg-white rounded-none border border-slate-200 p-5 shadow-sm hover:border-teal-500 transition-all flex flex-col md:flex-row gap-5 items-start md:items-center border-l-4">

                    <div class="shrink-0 hidden md:block">
                         <div class="h-12 w-12 rounded-none flex items-center justify-center border" :class="getActivityTypeStyle(activity.type).class">
                            <component :is="getActivityTypeStyle(activity.type).icon" class="w-6 h-6" />
                        </div>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-1">
                            <Link :href="route('activities.show', activity.id)" class="hover:underline">
                                <h3 class="text-lg font-bold text-slate-900 truncate group-hover:text-teal-700 transition-colors uppercase">
                                    {{ activity.title }}
                                </h3>
                            </Link>
                            <Badge variant="outline" class="uppercase text-[9px] font-bold tracking-widest bg-slate-50 text-slate-500 border-slate-200 rounded-none">
                                {{ activity.type || 'Activity' }}
                            </Badge>
                        </div>
                        <p class="text-slate-500 text-xs line-clamp-1 mb-3 font-medium">{{ activity.description || 'No instructions provided.' }}</p>

                        <div class="flex flex-wrap items-center gap-4 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                            <span class="flex items-center gap-1.5 text-slate-600">
                                <Clock class="w-3.5 h-3.5 text-teal-500" /> Posted: {{ formatDate(activity.created_at) }}
                            </span>
                            <span v-if="activity.due_date" class="flex items-center gap-1.5 text-orange-600 bg-orange-50 px-2 py-0.5 border border-orange-100">
                                <Calendar class="w-3.5 h-3.5" /> Due: {{ formatDate(activity.due_date) }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 border-l border-slate-100 pl-4 ml-2 md:self-center self-end w-full md:w-auto justify-end">
                        <Link :href="route('activities.show', activity.id)">
                            <Button size="sm" variant="outline" class="text-teal-600 border-teal-200 hover:bg-teal-50 gap-2 rounded-none text-[10px] uppercase font-bold px-4">
                                <Eye class="w-4 h-4" /> View
                            </Button>
                        </Link>

                        <template v-if="can.manage_activities">
                            <Link :href="route('activities.edit', activity.id)">
                                <Button variant="ghost" size="icon" class="h-8 w-8 text-slate-400 hover:text-teal-600"><Pencil class="w-4 h-4" /></Button>
                            </Link>
                            <Button variant="ghost" size="icon" class="h-8 w-8 text-slate-400 hover:text-red-600" @click="openDeleteModal(activity.id)">
                                <Trash2 class="w-4 h-4" />
                            </Button>
                        </template>

                        <template v-else>
                            <div v-if="activity.my_submission">
                                <Badge class="bg-green-100 text-green-700 border-none gap-1 px-3 py-1 rounded-none text-[9px] font-bold uppercase shadow-sm">
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
                        <div v-if="!link.url" class="px-4 py-2 text-[10px] font-bold uppercase text-slate-400 border border-slate-200 bg-slate-50 select-none" v-html="link.label" />
                        <Link v-else class="px-4 py-2 text-[10px] font-bold uppercase border transition-all" :class="link.active ? 'bg-slate-900 text-white border-slate-900 shadow-md' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'" :href="link.url" v-html="link.label" />
                    </template>
                </div>
            </div>
         </div>

         <div v-if="showDeleteModal" class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white max-w-sm w-full p-8 border border-slate-200 rounded-none shadow-2xl animate-in zoom-in duration-200">
                <h3 class="text-sm font-bold uppercase tracking-[0.2em] text-slate-900 mb-2 flex items-center gap-2">
                    <AlertCircle class="w-5 h-5 text-red-500" /> Confirm Delete
                </h3>
                <p class="text-sm text-slate-500 font-medium mb-8 leading-relaxed">
                    Delete this activity and all student work permanently? This cannot be undone.
                </p>
                <div class="flex gap-3">
                    <Button variant="ghost" @click="showDeleteModal = false" class="flex-1 text-[10px] font-bold uppercase tracking-widest">Cancel</Button>
                    <Button @click="confirmDelete" class="flex-1 bg-red-600 text-white text-[10px] font-bold uppercase tracking-widest hover:bg-red-700 shadow-lg px-6">Confirm</Button>
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