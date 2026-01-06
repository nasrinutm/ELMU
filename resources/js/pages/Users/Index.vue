<script setup lang="ts">
import { ref, watch, computed, nextTick } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { route } from 'ziggy-js';
import {
    Edit,
    Trash2,
    UserPlus,
    Filter,
    Search,
    User,
    ArrowUpDown,
    X,
    CheckCircle2,
    AlertTriangle
} from 'lucide-vue-next';
import { type BreadcrumbItem, type AppPageProps } from '@/types';

// Props Definition
const props = defineProps<{
    users: {
        data: Array<{
            id: number;
            name: string;
            username: string;
            email: string;
            roles: Array<{ name: string }>;
            created_at: string;
        }>;
        links: Array<any>;
    };
    filters: {
        role: string;
        sort: string;
        search?: string;
    };
    roles: string[];
}>();

const page = usePage<AppPageProps>();
const authUser = computed(() => (page.props as any).auth.user);

// --- DYNAMIC THEME LOGIC ---
const themeClasses = computed(() => {
    // Detect role from Spatie array or direct property
    const role = (authUser.value?.roles?.[0]?.name || authUser.value?.roles?.[0] || authUser.value?.role || 'admin').toLowerCase();

    switch (role) {
        case 'admin':
            // Matches --action-color: #1e293b
            return 'bg-[#1e293b] hover:bg-[#0f172a] border-[#334155]';
        case 'teacher':
            // Matches --action-color: #0d9488
            return 'bg-[#0d9488] hover:bg-[#0f766e] border-[#0f766e]';
        case 'student':
            // Matches --action-color: #4338ca
            return 'bg-[#4338ca] hover:bg-[#3730a3] border-[#312e81]';
        default:
            return 'bg-slate-900 hover:bg-slate-800';
    }
});

// --- NOTIFICATION & MODAL STATE ---
const showNotification = ref(false);
const showDeleteModal = ref(false);
const selectedUser = ref<any>(null);
const flashSuccess = computed(() => (page.props as any).flash?.success);

// Watch for flash messages to trigger the toast
watch(flashSuccess, async (newVal) => {
    if (newVal) {
        showNotification.value = false;
        await nextTick();
        showNotification.value = true;
        setTimeout(() => { showNotification.value = false; }, 5000);
    }
}, { immediate: true });

// --- SEARCH & FILTER STATE ---
const roleFilter = ref(props.filters.role || '');
const sortOrder = ref(props.filters.sort || 'latest');
const searchQuery = ref(props.filters.search || '');

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'User Management', href: route('users.index') },
];

// Debounce Function for Search
const debounce = (fn: Function, delay = 300) => {
    let timeout: ReturnType<typeof setTimeout>;
    return (...args: any[]) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), delay);
    };
};

const updateFilters = debounce(() => {
    router.get(
        route('users.index'),
        {
            role: roleFilter.value,
            sort: sortOrder.value,
            search: searchQuery.value
        },
        { preserveState: true, replace: true }
    );
}, 300);

watch([roleFilter, sortOrder, searchQuery], () => {
    updateFilters();
});

// --- ACTION HANDLERS ---
const openDeleteModal = (user: any) => {
    selectedUser.value = user;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (selectedUser.value) {
        router.delete(route('users.destroy', selectedUser.value.id), {
            onFinish: () => {
                showDeleteModal.value = false;
                selectedUser.value = null;
            }
        });
    }
};

// --- UTILS ---
const formatAccountAge = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now.getTime() - date.getTime();
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    if (days === 0) return 'Joined Today';
    if (days === 1) return '1 day ago';
    return `${days} days ago`;
};

const capitalize = (s: string) => s.charAt(0).toUpperCase() + s.slice(1);

const getRoleBadgeClass = (roleName: string) => {
    switch (roleName.toLowerCase()) {
        case 'admin': return 'bg-[#1e293b] text-white';
        case 'teacher': return 'bg-[#0d9488] text-white';
        case 'student': return 'bg-[#4338ca] text-white';
        default: return 'bg-slate-100 text-slate-800';
    }
};
</script>

<template>
    <Head title="User Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-6 antialiased font-sans relative">

            <transition name="toast">
                <div v-if="showNotification" class="fixed top-10 right-10 z-[100] flex items-center gap-4 bg-slate-900 text-white p-5 shadow-2xl border-l-4 border-indigo-500 min-w-[350px]">
                    <div class="bg-indigo-500/20 p-2"><CheckCircle2 class="w-6 h-6 text-indigo-500" /></div>
                    <div class="flex-grow font-sans">
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-indigo-500">System Success</p>
                        <p class="text-sm font-medium">{{ flashSuccess }}</p>
                    </div>
                    <button @click="showNotification = false" class="text-slate-500 hover:text-white transition-colors">
                        <X class="w-4 h-4" />
                    </button>
                </div>
            </transition>

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-200 pb-6">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900 uppercase">Manage Users</h1>
                    <p class="text-slate-500 mt-1">View, create, and manage system users and roles.</p>
                </div>
                <Link :href="route('users.create')">
                    <Button
                        class="text-white font-bold shadow-md rounded-sm transition-all text-[10px] uppercase tracking-widest px-6 py-5 flex items-center gap-2"
                        :class="themeClasses"
                    >
                        <UserPlus class="w-4 h-4 mr-2" /> Add New User
                    </Button>
                </Link>
            </div>

            <div class="flex flex-col md:flex-row gap-4 bg-white p-5 border border-slate-200 shadow-sm">
                <div class="relative w-full md:w-96">
                    <Search class="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search by name or email..."
                        class="w-full h-10 pl-10 pr-4 rounded-none border border-slate-300 bg-white text-sm focus:outline-none focus:ring-1 transition-all"
                        :class="themeClasses.includes('indigo') ? 'focus:ring-indigo-600' : themeClasses.includes('teal') ? 'focus:ring-teal-600' : 'focus:ring-slate-900'"
                    />
                </div>

                <div class="flex flex-wrap md:flex-nowrap gap-4 w-full md:w-auto ml-auto">
                    <div class="relative w-full md:w-48">
                        <Filter class="absolute left-3 top-3 h-4 w-4 text-slate-400 pointer-events-none" />
                        <select
                            v-model="roleFilter"
                            class="w-full h-10 pl-10 pr-8 rounded-none border border-slate-300 bg-white text-xs font-bold uppercase tracking-widest text-slate-600 focus:outline-none focus:ring-1 appearance-none cursor-pointer"
                        >
                            <option value="">All Roles</option>
                            <option v-for="role in roles" :key="role" :value="role">{{ capitalize(role) }}</option>
                        </select>
                    </div>

                    <div class="relative w-full md:w-48">
                        <ArrowUpDown class="absolute left-3 top-3 h-4 w-4 text-slate-400 pointer-events-none" />
                        <select
                            v-model="sortOrder"
                            class="w-full h-10 pl-10 pr-8 rounded-none border border-slate-300 bg-white text-xs font-bold uppercase tracking-widest text-slate-600 focus:outline-none focus:ring-1 appearance-none cursor-pointer"
                        >
                            <option value="latest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-5 text-[10px] uppercase tracking-[0.2em] font-bold text-slate-500">User Details</th>
                                <th class="px-6 py-5 text-[10px] uppercase tracking-[0.2em] font-bold text-slate-500">Role</th>
                                <th class="px-6 py-5 text-[10px] uppercase tracking-[0.2em] font-bold text-slate-500">Joined</th>
                                <th class="px-6 py-5 text-right text-[10px] uppercase tracking-[0.2em] font-bold text-slate-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="user in users.data" :key="user.id" class="group hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="h-10 w-10 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center border border-slate-200 shrink-0 font-bold text-xs uppercase shadow-inner">
                                            {{ user.name.charAt(0) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900">{{ user.name }}</div>
                                            <div class="text-[11px] text-slate-400 uppercase tracking-tight">{{ user.email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <Badge :class="getRoleBadgeClass(user.roles[0]?.name || 'User')" class="uppercase text-[9px] font-bold px-2.5 py-1 rounded-sm border-none shadow-sm">
                                        {{ user.roles[0]?.name || 'User' }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-[11px] font-medium text-slate-500 uppercase tracking-widest">
                                    {{ formatAccountAge(user.created_at) }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1 opacity-60 group-hover:opacity-100 transition-opacity">
                                        <Link :href="route('users.edit', user.id)">
                                            <Button size="icon" variant="ghost" class="h-9 w-9 rounded-sm text-blue-600 hover:text-blue-700 hover:bg-blue-50">
                                                <Edit class="w-4 h-4" />
                                            </Button>
                                        </Link>
                                        <Button
                                            size="icon"
                                            variant="ghost"
                                            class="h-9 w-9 rounded-sm text-red-600 hover:text-red-700 hover:bg-red-50 transition-all"
                                            @click="openDeleteModal(user)"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td colspan="4" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-400">
                                        <div class="h-16 w-16 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                                            <User class="h-8 w-8 text-slate-200" />
                                        </div>
                                        <p class="font-bold text-slate-900 uppercase tracking-widest text-xs">No users found</p>
                                        <p class="text-xs mt-1">Try adjusting your search or filters.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="users.links.length > 3" class="flex justify-center pt-4">
                <div class="flex flex-wrap gap-1">
                    <template v-for="(link, key) in users.links" :key="key">
                        <div v-if="link.url === null" class="px-4 py-2 text-[10px] font-bold uppercase text-slate-400 border border-slate-200 bg-slate-50" v-html="link.label" />
                        <Link v-else class="px-4 py-2 text-[10px] font-bold uppercase border transition-all" :class="link.active ? 'bg-[#1e293b] text-white border-[#1e293b] shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'" :href="link.url" v-html="link.label" />
                    </template>
                </div>
            </div>

            <transition name="modal">
                <div v-if="showDeleteModal" class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
                    <div class="bg-white max-w-md w-full p-10 border border-slate-200 shadow-2xl rounded-none text-center">
                        <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mb-6 mx-auto">
                            <AlertTriangle class="w-8 h-8 text-red-500" />
                        </div>
                        <h3 class="font-bold uppercase tracking-[0.2em] text-slate-900 mb-2">Confirm Removal</h3>
                        <p class="text-sm text-slate-500 mb-8 leading-relaxed font-sans font-medium">
                            Are you sure you want to delete <strong>{{ selectedUser?.name }}</strong>? This action will permanently remove all associated data and cannot be undone.
                        </p>
                        <div class="flex gap-4 w-full">
                            <button @click="showDeleteModal = false" class="flex-1 py-4 text-[10px] font-bold uppercase border border-slate-100 hover:bg-slate-50 transition-colors">Cancel</button>
                            <button @click="confirmDelete" class="flex-1 py-4 bg-red-600 text-white text-[10px] font-bold uppercase hover:bg-red-700 shadow-lg transition-all">Delete User</button>
                        </div>
                    </div>
                </div>
            </transition>

        </div>
    </AppLayout>
</template>

<style scoped>
/* Toast Animation */
.toast-enter-active, .toast-leave-active { transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.toast-enter-from { transform: translateX(100%); opacity: 0; }
.toast-leave-to { transform: translateY(-20px); opacity: 0; }

/* Modal Animation */
.modal-enter-active, .modal-leave-active { transition: opacity 0.3s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>
