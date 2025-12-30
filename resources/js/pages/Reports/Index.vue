<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { User as UserIcon } from 'lucide-vue-next';

// The Index page receives a LIST of students, not a single student
defineProps<{
    students: Array<{ id: number; name: string; email: string; }>;
}>();
</script>

<template>
    <Head title="Classroom Student Directory" />
    <AppLayout>
        <div class="min-h-screen bg-[#001f3f] text-white p-6">
            <div class="max-w-7xl mx-auto">
                <h1 class="text-3xl font-bold text-[#ffcc00] mb-8">Classroom Student Directory</h1>
                
                <div class="bg-[#0a192f] rounded-xl border border-white/10 overflow-hidden shadow-xl">
                    <div class="p-6 border-b border-white/10">
                        <h2 class="text-[#ffcc00] font-bold text-lg">Enrollment List</h2>
                        <p class="text-sm text-gray-400 mt-1">Click on a student to view their full report.</p>
                    </div>

                    <div v-if="students.length > 0">
                        <table class="w-full text-left">
                            <thead class="bg-[#0d223f] text-[10px] uppercase font-bold text-gray-500">
                                <tr>
                                    <th class="px-6 py-4">Student Name</th>
                                    <th class="px-6 py-4">Email Address</th>
                                    <th class="px-6 py-4 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <tr v-for="student in students" :key="student.id" class="hover:bg-white/5 transition-colors group">
                                    <td class="px-6 py-4 flex items-center gap-3">
                                        <div class="bg-blue-500/20 p-2 rounded-full group-hover:bg-blue-600 transition-colors">
                                            <UserIcon class="w-4 h-4 text-blue-400 group-hover:text-white" />
                                        </div>
                                        <Link :href="route('reports.student.detail', student.id)" class="font-medium text-gray-200 group-hover:text-[#ffcc00]">
                                            {{ student.name }}
                                        </Link>
                                    </td>
                                    <td class="px-6 py-4 text-gray-400 text-sm">{{ student.email }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <Link :href="route('reports.student.detail', student.id)" class="text-xs font-bold text-blue-400 hover:text-[#ffcc00] hover:underline">
                                            View Report &rarr;
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="p-12 text-center text-gray-500 italic">
                        No students found. Please run: php artisan db:seed --class=StudentPerformanceSeeder
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>