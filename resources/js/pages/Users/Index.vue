<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3'; // 👈 1. Import Link
import { ref, watch, defineProps } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { route } from 'ziggy-js';

// Props from the controller
const props = defineProps<{
    users: {
        data: Array<{
            id: number;
            name: string;
            email: string;
            roles: Array<{ name: string }>; // It's an array of role objects
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

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Users', href: route('users.index') },
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
const capitalize = (s: string) => s.charAt(0).toUpperCase() + s.slice(1);
</script>

<template>
    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-4xl mx-auto p-4 bg-transparent">
            <div class="flex justify-between items-center mb-4">
                <div class="flex space-x-4">
                    <div>
                        <label for="role" class="block text-sm font-medium">Role</label>
                        <select v-model="roleFilter" id="role" class="rounded border px-3 py-2">
                            <option value="">All Roles</option>
                            <option v-for="role in roles" :key="role" :value="role">
                            {{ capitalize(role) }}
                        </option>
                            </select>
                    </div>

                    <div>
                        <label for="sort" class="block text-sm font-medium">Sort By</label>
                        <select v-model="sortOrder" id="sort" class="rounded border px-3 py-2">
                            <option value="latest">Latest</option>
                            <option value="oldest">Oldest</option>
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
                            <th class="p-3 text-left">Actions</th>
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
                            <td class="p-3 capitalize">
                                {{ user.roles[0]?.name || 'N/A' }}
                            </td>
                            <td class="p-3">{{ formatAccountAge(user.created_at) }}</td>
                        </tr>
                        <tr v-if="users.data.length === 0">
                            <td colspan="4" class="p-3 text-center">No users found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="users.links.length > 3" class="mt-4">
                <div class="flex flex-wrap -mb-1">
                    <template v-for="(link, key) in users.links" :key="key">
                        <div
                            v-if="link.url === null"
                            class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded"
                            v-html="link.label"
                        />
                        <Link
                            v-else
                            class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-white focus:border-indigo-500 focus:text-indigo-500"
                            :class="{ 'bg-blue-600 text-white': link.active }"
                            :href="link.url"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>
    </AppLayout>
</template>