<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { route } from 'ziggy-js';
import { Edit, Trash2, UserPlus, Filter, Search, User, ArrowUpDown } from 'lucide-vue-next';
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

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'User Management', href: route('users.index') },
];

// State - Bound to props from UserController
const roleFilter = ref(props.filters.role || '');
const sortOrder = ref(props.filters.sort || 'latest');
const searchQuery = ref(props.filters.search || '');

// Delete Action
const deleteUser = (id: number) => {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        router.delete(route('users.destroy', id));
    }
};

// Debounce Function for Search
const debounce = (fn: Function, delay = 300) => {
    let timeout: ReturnType<typeof setTimeout>;
    return (...args: any[]) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), delay);
    };
};

// FIX: Combined watcher that correctly passes the 'search' key to Laravel
const updateFilters = debounce(() => {
    router.get(
        route('users.index'),
        {
            role: roleFilter.value,
            sort: sortOrder.value,
            search: searchQuery.value // This matches $request->input('search') in Controller
        },
        { preserveState: true, replace: true }
    );
}, 300);

watch([roleFilter, sortOrder, searchQuery], () => {
    updateFilters();
});

// Date Formatter
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
    switch (roleName) {
        case 'admin': return 'bg-red-600 hover:bg-red-700 text-white';
        case 'teacher': return 'bg-indigo-600 hover:bg-indigo-700 text-white';
        case 'student': return 'bg-blue-100 text-blue-800 hover:bg-blue-200';
        default: return 'bg-gray-100 text-gray-800';
    }
};
</script>

<template>
    <Head title="User Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-6">

            <div v-if="page.props.flash?.success" class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-none flex items-center gap-2 shadow-sm">
                <span class="h-2 w-2 rounded-full bg-green-600"></span>
                {{ page.props.flash.success }}
            </div>

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                        Manage Users
                    </h1>
                    <p class="text-slate-500 mt-1">
                        View, create, and manage system users and roles.
                    </p>
                </div>

                <Link :href="route('users.create')">
                    <Button class="bg-blue-600 hover:bg-blue-700 text-white font-medium shadow-sm rounded-none transition-all">
                        <UserPlus class="w-4 h-4 mr-2" />
                        Add New User
                    </Button>
                </Link>
            </div>

            <Separator class="bg-slate-200" />

            <div class="flex flex-col md:flex-row gap-4 bg-white p-4 rounded-none border border-slate-200 shadow-sm">

                <div class="relative w-full md:w-96">
                    <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search by name or email..."
                        class="w-full h-10 pl-9 pr-4 rounded-none border border-slate-300 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    />
                </div>

                <div class="flex gap-4 w-full md:w-auto ml-auto">
                    <div class="relative w-full md:w-48">
                        <Filter class="absolute left-3 top-2.5 h-4 w-4 text-slate-400 pointer-events-none" />
                        <select
                            v-model="roleFilter"
                            class="w-full h-10 pl-9 pr-8 rounded-none border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none cursor-pointer"
                        >
                            <option value="">All Roles</option>
                            <option v-for="role in roles" :key="role" :value="role">
                                {{ capitalize(role) }}
                            </option>
                        </select>
                    </div>

                    <div class="relative w-full md:w-48">
                        <ArrowUpDown class="absolute left-3 top-2.5 h-4 w-4 text-slate-400 pointer-events-none" />
                        <select
                            v-model="sortOrder"
                            class="w-full h-10 pl-9 pr-8 rounded-none border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none cursor-pointer"
                        >
                            <option value="latest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-none border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4 font-semibold text-slate-700">User Details</th>
                                <th class="px-6 py-4 font-semibold text-slate-700">Role</th>
                                <th class="px-6 py-4 font-semibold text-slate-700">Joined</th>
                                <th class="px-6 py-4 text-right font-semibold text-slate-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="user in users.data"
                                :key="user.id"
                                class="group hover:bg-slate-50/80 transition-colors"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-none bg-slate-100 text-slate-500 flex items-center justify-center border border-slate-200 shrink-0">
                                            <span class="font-bold text-sm">{{ user.name.charAt(0).toUpperCase() }}</span>
                                        </div>
                                        <div>
                                            <div class="font-medium text-slate-900">{{ user.name }}</div>
                                            <div class="text-xs text-slate-500">{{ user.email }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <Badge
                                        :class="getRoleBadgeClass(user.roles[0]?.name)"
                                        class="uppercase text-[10px] font-bold px-2.5 py-0.5 rounded-none border-none shadow-sm"
                                    >
                                        {{ user.roles[0]?.name || 'User' }}
                                    </Badge>
                                </td>

                                <td class="px-6 py-4 text-slate-500">
                                    {{ formatAccountAge(user.created_at) }}
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link :href="route('users.edit', user.id)">
                                            <Button size="icon" variant="ghost" class="h-8 w-8 rounded-none text-blue-600 hover:text-blue-700 hover:bg-blue-50">
                                                <Edit class="w-4 h-4" />
                                            </Button>
                                        </Link>

                                        <Button
                                            size="icon"
                                            variant="ghost"
                                            class="h-8 w-8 rounded-none text-red-600 hover:text-red-700 hover:bg-red-50"
                                            @click="deleteUser(user.id)"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="users.data.length === 0">
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-500">
                                        <User class="h-12 w-12 text-slate-200 mb-3" />
                                        <p class="font-medium text-slate-900">No users found</p>
                                        <p class="text-sm">Try adjusting your search or filters.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="users.links.length > 3" class="flex justify-center pt-2">
                <div class="flex flex-wrap gap-1">
                    <template v-for="(link, key) in users.links" :key="key">
                        <div
                            v-if="link.url === null"
                            class="px-3 py-1 text-sm text-slate-400 border border-slate-200 rounded-none bg-slate-50 select-none"
                            v-html="link.label"
                        />
                        <Link
                            v-else
                            class="px-3 py-1 text-sm border rounded-none transition-colors"
                            :class="{
                                'bg-blue-600 text-white border-blue-600 font-medium': link.active,
                                'bg-white text-slate-600 border-slate-200 hover:bg-slate-50 hover:border-slate-300': !link.active
                            }"
                            :href="link.url"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
