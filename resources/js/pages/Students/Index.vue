<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';
import { route } from 'ziggy-js';

// Define Props
// "students" is now an Object because it contains pagination data (data, links, current_page, etc.)
defineProps<{
    students: {
        data: Array<{
            id: number;
            name: string;
            email: string;
            username: string;
            created_at: string;
        }>;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
}>();
</script>

<template>
    <Head title="Student List" />

    <AppLayout>
        <Teleport to="body">
            <div class="fixed top-2 left-[305px] z-[9999] flex items-center h-16 pointer-events-none">
                <nav class="flex items-center text-[16px] font-normal pointer-events-auto">
                    <Link 
                        :href="route('dashboard')" 
                        class="text-black transition-colors duration-200 hover:text-[#FFD700] tracking-tight"
                    >
                        Dashboard
                    </Link>
                    
                    <ChevronRight class="mx-3 h-3 w-3 text-black stroke-[2px]" />
                    
                    <span class="text-[#FFD700] font-medium tracking-tight">
                        Students
                    </span>
                </nav>
            </div>
        </Teleport>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-[#111827] shadow-2xl sm:rounded-xl border border-[#1e293b]">
                    <div class="p-6">
                        <div class="relative overflow-x-auto text-gray-100">
                            <table class="w-full text-left text-sm text-gray-400">
                                <thead class="bg-[#1e293b] text-xs uppercase text-gray-300 border-b border-[#334155]">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 font-bold">NAME</th>
                                        <th scope="col" class="px-6 py-4 font-bold">USERNAME</th>
                                        <th scope="col" class="px-6 py-4 font-bold">EMAIL</th>
                                        <th scope="col" class="px-6 py-4 text-right font-bold">JOINED DATE</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="student in students.data" :key="student.id" class="border-b border-[#1e293b] bg-[#111827] hover:bg-[#1e293b]/50 transition-all">
                                        <td class="px-6 py-4 font-medium text-white">
                                            <Link :href="route('students.show', student.id)" class="text-blue-400 hover:text-blue-300 transition-colors">
                                                {{ student.name }}
                                            </Link>
                                        </td>
                                        <td class="px-6 py-4">{{ student.username }}</td>
                                        <td class="px-6 py-4">{{ student.email }}</td>
                                        <td class="px-6 py-4 text-right">{{ new Date(student.created_at).toLocaleDateString('en-GB') }}</td>
                                    </tr>
                                    
                                    <tr v-if="students.data.length === 0">
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                            No students found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="students.links.length > 3" class="mt-6 flex justify-center">
                            <div class="flex flex-wrap gap-1">
                                <template v-for="(link, key) in students.links" :key="key">
                                    <div v-if="link.url === null" class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-600 border rounded" v-html="link.label" />
                                    <Link v-else 
                                        class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-white focus:border-indigo-500 focus:text-indigo-500" 
                                        :class="{ 'bg-blue-600 text-white': link.active, 'bg-white text-gray-700': !link.active }" 
                                        :href="link.url" 
                                        v-html="link.label" 
                                    />
                                </template>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>