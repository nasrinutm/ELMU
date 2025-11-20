<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input'; // Ensure you have these components
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge'; // Ensure you have Badge component
import { Separator } from '@/components/ui/separator';
import { route } from 'ziggy-js';
import { Edit, Trash2, UserPlus, Filter, Search, User } from 'lucide-vue-next';
import { type BreadcrumbItem, type AppPageProps } from '@/types';

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
    };
    roles: string[];
}>();

const page = usePage<AppPageProps>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Users', href: route('users.index') },
];

const roleFilter = ref(props.filters.role || '');
const sortOrder = ref(props.filters.sort || 'latest');

const deleteUser = (id: number) => {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(route('users.destroy', id));
    }
};

const debounce = (fn: (...args: any[]) => void, delay = 300) => {
    let timeout: ReturnType<typeof setTimeout>;
    return (...args: any[]) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), delay);
    };
};

watch([roleFilter, sortOrder], debounce(([newRole, newSort]) => {
    router.get(
        route('users.index'),
        { role: newRole, sort: newSort },
        { preserveState: true, replace: true },
    );
}, 300));

const formatAccountAge = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now.getTime() - date.getTime();
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));

    if (days === 0) return 'Today';
    if (days === 1) return '1 day ago';
    return `${days} days ago`;
};

const capitalize = (s: string) => s.charAt(0).toUpperCase() + s.slice(1);

// Helper for role badge colors
const getRoleBadgeVariant = (roleName: string) => {
    if (roleName === 'admin') return 'destructive'; // Red
    if (roleName === 'teacher') return 'default'; // Blue (default theme)
    return 'secondary'; // Yellow
};
</script>

<template>
    <Head title="Manage Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-8">

            <!-- Flash Messages -->
            <div v-if="page.props.flash?.success" class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-green-600"></span>
                {{ page.props.flash.success }}
            </div>
            <div v-if="page.props.flash?.error" class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-red-600"></span>
                {{ page.props.flash.error }}
            </div>

            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="space-y-2">
                    <h1 class="text-3xl font-bold tracking-tight text-[#FFD900]">
                        Manage Users
                    </h1>
                    <p class="text-[#FFD900]">
                        View, create, and manage system users.
                    </p>
                </div>

                <Link :href="route('users.create')">
                    <Button class="bg-[#FFD900] text-[#003366] hover:bg-[#FFD900]/90 font-bold shadow-lg">
                        <UserPlus class="w-4 h-4 mr-2" />
                        Add New User
                    </Button>
                </Link>
            </div>

            <Separator class="bg-slate-600/50" />

            <!-- Filters Card (Yellow Background) -->
            <div class="rounded-xl border-none bg-[#FFD900] p-6 shadow-sm">
                <div class="flex flex-col sm:flex-row gap-6">
                    <!-- Role Filter -->
                    <div class="w-full sm:w-1/3 space-y-2">
                        <Label for="roleFilter" class="text-xs font-bold uppercase tracking-wider text-[#003366]">
                            Filter by Role
                        </Label>
                        <div class="relative">
                            <Filter class="absolute left-3 top-2.5 h-4 w-4 text-white z-10" />
                            <select
                                id="roleFilter"
                                v-model="roleFilter"
                                class="h-10 w-full rounded-md border-transparent bg-[#003366] pl-9 px-3 text-sm text-white shadow-none focus:outline-none focus:ring-2 focus:ring-white appearance-none"
                            >
                                <option value="">All Roles</option>
                                <option v-for="role in roles" :key="role" :value="role">
                                    {{ capitalize(role) }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Sort Filter -->
                    <div class="w-full sm:w-1/3 space-y-2">
                        <Label for="sortOrder" class="text-xs font-bold uppercase tracking-wider text-[#003366]">
                            Sort By
                        </Label>
                        <div class="relative">
                            <Search class="absolute left-3 top-2.5 h-4 w-4 text-white z-10" />
                            <select
                                id="sortOrder"
                                v-model="sortOrder"
                                class="h-10 w-full rounded-md border-transparent bg-[#003366] pl-9 px-3 text-sm text-white shadow-none focus:outline-none focus:ring-2 focus:ring-white appearance-none"
                            >
                                <option value="latest">Latest Joined</option>
                                <option value="oldest">Oldest Joined</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Table Card -->
            <div class="rounded-xl border-none bg-white shadow-sm overflow-hidden">
                <table class="w-full text-sm text-left">
                    <!--
                         Header: Yellow Background (#FFD900)
                         Text: Dark Blue (#003366)
                    -->
                    <thead class="bg-[#FFD900] border-b border-[#003366]/20 text-[#003366] uppercase text-xs font-bold tracking-wider">
                        <tr>
                            <th class="p-4 pl-6">Name</th>
                            <th class="p-4">Email</th>
                            <th class="p-4">Role</th>
                            <th class="p-4">Account Age</th>
                            <th class="p-4 text-right pr-6">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#003366]/10">
                        <tr
                            v-for="user in users.data"
                            :key="user.id"
                            class="group hover:bg-slate-50 transition-colors"
                        >
                            <td class="p-4 pl-6">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-full bg-[#003366] text-white flex items-center justify-center shrink-0 border border-[#003366]/10">
                                        <User class="h-5 w-5" />
                                    </div>
                                    <div>
                                        <div class="font-semibold text-[#003366]">{{ user.name }}</div>
                                        <div class="text-xs text-[#003366]/60">@{{ user.username }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 text-[#003366]">{{ user.email }}</td>
                            <td class="p-4">
                                <Badge
                                    :variant="getRoleBadgeVariant(user.roles[0]?.name)"
                                    class="uppercase px-2 border-none"
                                    :class="{
                                        'bg-[#003366] text-white': user.roles[0]?.name === 'teacher',
                                        'bg-[#FFD900] text-[#003366]': user.roles[0]?.name === 'student',
                                        'bg-red-600 text-white': user.roles[0]?.name === 'admin'
                                    }"
                                >
                                    {{ user.roles[0]?.name || 'N/A' }}
                                </Badge>
                            </td>
                            <td class="p-4 text-[#003366]/80">{{ formatAccountAge(user.created_at) }}</td>

                            <td class="p-4 text-right pr-6">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Edit Button (Solid Blue) -->
                                    <Button
                                        size="sm"
                                        class="h-8 bg-[#003366] text-white hover:bg-[#002244] border-none shadow-sm"
                                        as-child
                                    >
                                        <Link :href="route('users.edit', user.id)">
                                            <Edit class="w-3 h-3 mr-1" /> Edit
                                        </Link>
                                    </Button>

                                    <!-- Delete Button (Solid Red) -->
                                    <Button
                                        size="sm"
                                        variant="destructive"
                                        class="h-8 shadow-sm bg-red-600 hover:bg-red-700 text-white"
                                        @click="deleteUser(user.id)"
                                    >
                                        <Trash2 class="w-3 h-3 mr-1" /> Delete
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="users.data.length === 0">
                            <td colspan="5" class="p-12 text-center text-[#003366]">
                                <p class="font-medium">No users found.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="users.links.length > 3" class="mt-4 flex justify-center">
                <div class="flex flex-wrap gap-1">
                    <template v-for="(link, key) in users.links" :key="key">
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
