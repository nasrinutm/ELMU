<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Input } from '@/components/ui/input';
import {
    Search,
    Mail,
    GraduationCap,
    ArrowRight,
    Users
} from 'lucide-vue-next';
import debounce from 'lodash/debounce';
import { route } from 'ziggy-js';

const props = defineProps<{
    students: any;
    filters: { search?: string };
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Student Progress', href: route('students.index') },
];

const studentList = computed(() => {
    if (Array.isArray(props.students)) return props.students;
    return props.students.data || [];
});

const links = computed(() => {
    return !Array.isArray(props.students) ? props.students.links : [];
});

const search = ref(props.filters?.search || '');

const updateSearch = debounce(() => {
    router.get(route('students.index'), { search: search.value }, { preserveState: true, replace: true });
}, 300);

watch(search, updateSearch);

const formatDate = (dateString: string | null) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
};
</script>

<template>
    <Head title="Student Progress" />

    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-slate-50 p-6 space-y-6">

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
                        <GraduationCap class="w-7 h-7 text-teal-600" />
                        Student Progress
                    </h1>
                    <p class="text-slate-500 mt-1 text-sm">
                        Manage all enrolled students and view their progress.
                    </p>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <div class="relative w-full">
                    <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                    <Input
                        v-model="search"
                        placeholder="Search by name, username, or email..."
                        class="pl-9 bg-slate-50 border-slate-200 focus:bg-white focus:ring-teal-500 focus:border-teal-500 transition-all w-full"
                    />
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-xs font-semibold">
                            <tr>
                                <th class="px-6 py-4">Student Name</th>
                                <th class="px-6 py-4">Username</th>
                                <th class="px-6 py-4">Email Address</th>
                                <th class="px-6 py-4 text-right">Joined Date</th>
                                <th class="px-6 py-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="studentList.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="h-12 w-12 bg-slate-100 rounded-full flex items-center justify-center mb-3">
                                            <Users class="w-6 h-6 text-slate-400" />
                                        </div>
                                        <p>No students found matching your search.</p>
                                    </div>
                                </td>
                            </tr>

                            <tr
                                v-for="student in studentList"
                                :key="student.id"
                                class="group hover:bg-slate-50/80 transition-colors"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-9 w-9 rounded-full bg-teal-50 text-teal-700 flex items-center justify-center font-bold text-xs border border-teal-100 uppercase">
                                            {{ student.name.charAt(0) }}
                                        </div>
                                        <span class="font-medium text-slate-900 group-hover:text-teal-700 transition-colors">
                                            {{ student.name }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-600 font-mono text-xs">
                                    {{ student.username }}
                                </td>
                                <td class="px-6 py-4 text-slate-500 flex items-center gap-2">
                                    <Mail class="w-3.5 h-3.5 text-slate-400" />
                                    {{ student.email }}
                                </td>
                                <td class="px-6 py-4 text-right text-slate-500">
                                    {{ formatDate(student.created_at) }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Link
                                        :href="route('students.show', student.id)"
                                        class="inline-flex items-center text-xs font-bold text-teal-600 hover:text-teal-800 bg-teal-50 hover:bg-teal-100 px-3 py-1.5 rounded-full transition-colors"
                                    >
                                        View <ArrowRight class="w-3 h-3 ml-1" />
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="links.length > 3" class="flex justify-center pt-2">
                <div class="flex flex-wrap gap-1">
                    <template v-for="(link, key) in links" :key="key">
                        <div
                            v-if="!link.url"
                            class="px-3 py-1 text-sm text-slate-400 border border-slate-200 rounded-md bg-white"
                            v-html="link.label"
                        />
                        <Link
                            v-else
                            class="px-3 py-1 text-sm border rounded-md transition-all shadow-sm"
                            :class="link.active ? 'bg-teal-600 text-white border-teal-600 font-medium' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50 hover:border-slate-300'"
                            :href="link.url"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>

        </div>
    </AppSidebarLayout>
</template>
