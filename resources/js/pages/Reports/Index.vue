<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Search, ArrowRight, TrendingUp, Users } from 'lucide-vue-next';
import { route } from 'ziggy-js';

// Define the updated student interface to include progress data
const props = defineProps<{
    students: Array<{ 
        id: number; 
        name: string; 
        email: string; 
        completed_count: number; // Count of activities finished by student
        total_activities: number; // Total activities available in system
    }>;
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Performance Reports', href: route('reports.index') },
];

const searchQuery = ref('');

const filteredStudents = computed(() => {
    if (!searchQuery.value) return props.students;
    const query = searchQuery.value.toLowerCase();
    return props.students.filter(student =>
        student.name.toLowerCase().includes(query) ||
        student.email.toLowerCase().includes(query)
    );
});

/**
 * Calculates the percentage of completion.
 */
const getProgress = (completed: number, total: number) => {
    if (!total || total === 0) return 0;
    return Math.round((completed / total) * 100);
};
</script>

<template>
    <Head title="Performance Reports" />

    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-slate-50 p-6 space-y-6">

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
                        <TrendingUp class="w-7 h-7 text-teal-600" />
                        Performance Reports
                    </h1>
                    <p class="text-slate-500 mt-1 text-sm">Select a student to view analytics.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white border border-slate-200 p-5 rounded-xl flex items-center gap-4 shadow-sm">
                    <div class="p-3 bg-teal-50 text-teal-600 rounded-lg">
                        <Users class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Total Enrollment</p>
                        <p class="text-2xl font-bold text-slate-900">{{ students.length }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <div class="relative w-full">
                    <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                    <Input v-model="searchQuery" placeholder="Search student..." class="pl-9 bg-slate-50 border-slate-200 focus:bg-white w-full" />
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-6 py-4">Student Name</th>
                            <th class="px-6 py-4">Email Address</th>
                            <th class="px-6 py-4">Activity Progress</th>
                            <th class="px-6 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="student in filteredStudents" :key="student.id" class="group hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 font-medium text-slate-900 flex items-center gap-3">
                                <div class="h-9 w-9 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center font-bold text-xs uppercase">
                                    {{ student.name.charAt(0) }}
                                </div>
                                {{ student.name }}
                            </td>
                            <td class="px-6 py-4 text-slate-500">{{ student.email }}</td>
                            
                            <td class="px-6 py-4 min-w-[200px]">
                                <div class="flex flex-col gap-1.5">
                                    <div class="flex justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                        <span>{{ student.completed_count }} / {{ student.total_activities }} Total Tasks</span>
                                        <span>{{ getProgress(student.completed_count, student.total_activities) }}%</span>
                                    </div>
                                    <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                                        <div 
                                            class="h-full bg-teal-500 transition-all duration-500 ease-out"
                                            :style="{ width: getProgress(student.completed_count, student.total_activities) + '%' }"
                                        ></div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <Link :href="route('reports.student.detail', student.id)">
                                    <Button size="sm" variant="outline" class="text-teal-600 border-teal-200 hover:bg-teal-50 gap-2">
                                        View Report <ArrowRight class="w-4 h-4" />
                                    </Button>
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="filteredStudents.length === 0">
                            <td colspan="4" class="px-6 py-12 text-center text-slate-400 italic">
                                No students found matching your search.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppSidebarLayout>
</template>