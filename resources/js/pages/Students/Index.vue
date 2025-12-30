<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';
import { type BreadcrumbItem } from '@/types';
import { route } from 'ziggy-js';

defineProps<{
    students: Array<{
        id: number;
        name: string;
        email: string;
        username: string;
        created_at: string;
        submissions_count: number;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/' },
    { title: 'Students', href: route('students.index') },
];


</script>

<template>
    <Head title="Student List" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-[#ffffff] shadow-2xl sm:rounded-xl border border-[#1e293b]">
                    <div class="p-6">
                        <div class="relative overflow-x-auto text-gray-900">
                            <table class="w-full text-left text-sm text-gray-900">
                                <thead class="bg-[#ffffff] text-xs uppercase text-gray-900 border-b border-[#334155]">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 font-bold">NAME</th>
                                        <th scope="col" class="px-6 py-4 font-bold">USERNAME</th>
                                        <th scope="col" class="px-6 py-4 font-bold">EMAIL</th>
                                        <th scope="col" class="px-6 py-4 text-right font-bold">JOINED DATE</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="student in students" :key="student.id" class="border-b border-[#1e293b] bg-[#ffffff] hover:bg-[#1e293b]/50 transition-all">
                                        <td class="px-6 py-4 font-medium text-gray-900">
                                            <Link :href="route('students.show', { student: student.id })" class="text-blue-600 hover:text-blue-300 transition-colors">
                                                {{ student.name }}
                                            </Link>
                                        </td>
                                        <td class="px-6 py-4">{{ student.username }}</td>
                                        <td class="px-6 py-4">{{ student.email }}</td>
                                        <td class="px-6 py-4 text-right">{{ new Date(student.created_at).toLocaleDateString('en-GB') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>