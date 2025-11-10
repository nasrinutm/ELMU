<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { type BreadcrumbItem } from '@/types';

// Props from the controller
const props = defineProps<{
    users: {
        data: Array<{
            id: number;
            name: string;
            email: string;
            role: string;
            created_at: string; // We'll get this from the controller
        }>;
        // ... pagination links etc. if you use pagination
    };
    filters: {
        role: string;
        sort: string;
    };
}>();

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Manage User', href: route('users.index') },
];

// Refs for filter and sort dropdowns
const roleFilter = ref(props.filters.role || '');
const sortOrder = ref(props.filters.sort || 'latest');

// Function to format the date (Account Age)
const formatAccountAge = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now.getTime() - date.getTime();
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    
    if (days === 0) return 'Today';
    if (days === 1) return '1 day ago';
    return `${days} days ago`;
};

// Watch for changes in the refs and reload the page with new query params
watch([roleFilter, sortOrder], () => {
    router.get(
        route('users.index'),
        {
            role: roleFilter.value,
            sort: sortOrder.value,
        },
        {
            preserveState: true, // Keep the user's scroll position
            replace: true, // Don't create new history entries
        },
    );
});
</script>

<template>
    <Head title="Manage User" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-4xl mx-auto p-4 bg-transparent">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold">User Management</h1>
                <div class="flex space-x-4">
                    <div>
                        <label for="role" class="block text-sm font-medium">Role</label>
                        <select v-model="roleFilter" id="role" class="rounded border px-3 py-2">
                            <option value="">All Roles</option>
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                            </select>
                    </div>

                    <div>
                        <label for="sort" class="block text-sm font-medium">Sort By</label>
                        <select v-model="sortOrder" id="sort" class="rounded border px-3 py-2">
                            <option value="latest">Latest (Newest First)</option>
                            <option value="oldest">Oldest (Oldest First)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th class="p-3 text-left">Name</th>
                            <th class="p-3 text-left">Email</th>
                            <th class="p-3 text-left">Role</th>
                            <th class="p-3 text-left">Account Age</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="user in users.data"
                            :key="user.id"
                            class="border-t dark:border-gray-600"
                        >
                            <td class="p-3">{{ user.name }}</td>
                            <td class="p-3">{{ user.email }}</td>
                            <td class="p-3 capitalize">{{ user.role }}</td>
                            <td class="p-3">{{ formatAccountAge(user.created_at) }}</td>
                        </tr>
                        <tr v-if="users.data.length === 0">
                            <td colspan="4" class="p-3 text-center">No users found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>