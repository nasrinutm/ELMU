<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Users, GraduationCap, BookOpen, ShieldCheck, PlusCircle } from 'lucide-vue-next';

defineProps<{
    stats: {
        admins: number;
        teachers: number;
        students: number;
        total_users: number;
    };
    recentUsers: Array<{
        id: number;
        name: string;
        email: string;
        created_at: string;
    }>;
}>();

const breadcrumbs = [{ title: 'Dashboard', href: '/dashboard' }];
</script>

<template>
    <Head title="Admin Dashboard" />

    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-7xl mx-auto space-y-8">

            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900">System Overview</h1>
                    <p class="text-gray-500 mt-1">Monitor school performance and recent activity.</p>
                </div>
                <div class="flex gap-3">
                    <Link :href="route('users.create')" class="flex items-center gap-2 bg-blue-600 text-white px-5 py-2.5 rounded-sm hover:bg-blue-700 transition shadow-md font-medium text-sm">
                        <PlusCircle class="w-4 h-4" /> Add New User
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                <div class="relative overflow-hidden shadow-lg bg-gradient-to-br from-fuchsia-600 to-purple-700 p-6 text-white rounded-sm">
                    <div class="relative z-10 flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium opacity-90">Admins</p>
                            <h3 class="mt-2 text-4xl font-bold">{{ stats.admins }}</h3>
                            <p class="mt-2 text-xs opacity-75">System Managers</p>
                        </div>
                        <div class="p-2 bg-white/20 rounded-sm backdrop-blur-sm">
                            <ShieldCheck class="w-6 h-6 text-white" />
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden shadow-lg bg-gradient-to-br from-teal-500 to-emerald-600 p-6 text-white rounded-sm">
                    <div class="relative z-10 flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium opacity-90">Teachers</p>
                            <h3 class="mt-2 text-4xl font-bold">{{ stats.teachers }}</h3>
                            <p class="mt-2 text-xs opacity-75">Instructors</p>
                        </div>
                        <div class="p-2 bg-white/20 rounded-sm backdrop-blur-sm">
                            <BookOpen class="w-6 h-6 text-white" />
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden shadow-lg bg-gradient-to-br from-orange-500 to-amber-600 p-6 text-white rounded-sm">
                    <div class="relative z-10 flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium opacity-90">Students</p>
                            <h3 class="mt-2 text-4xl font-bold">{{ stats.students }}</h3>
                            <p class="mt-2 text-xs opacity-75">Learners</p>
                        </div>
                        <div class="p-2 bg-white/20 rounded-sm backdrop-blur-sm">
                            <GraduationCap class="w-6 h-6 text-white" />
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden shadow-lg bg-gradient-to-br from-blue-600 to-indigo-700 p-6 text-white rounded-sm">
                    <div class="relative z-10 flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium opacity-90">Total Users</p>
                            <h3 class="mt-2 text-4xl font-bold">{{ stats.total_users }}</h3>
                            <p class="mt-2 text-xs opacity-75">All Accounts</p>
                        </div>
                        <div class="p-2 bg-white/20 rounded-sm backdrop-blur-sm">
                            <Users class="w-6 h-6 text-white" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <div class="lg:col-span-2 bg-white border border-gray-200 shadow-sm rounded-sm">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="font-bold text-gray-900 flex items-center gap-2">
                            Recent Registrations
                        </h3>
                        <Link :href="route('users.index')" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View All</Link>
                    </div>
                    <div class="divide-y divide-gray-50">
                        <div v-for="user in recentUsers" :key="user.id" class="p-4 flex items-center justify-between hover:bg-gray-50 transition">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 font-bold text-sm">
                                    {{ user.name.charAt(0) }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">{{ user.name }}</p>
                                    <p class="text-xs text-gray-500">{{ user.email }}</p>
                                </div>
                            </div>
                            <span class="text-xs font-medium text-gray-400 bg-gray-50 px-2 py-1 rounded-sm">
                                {{ new Date(user.created_at).toLocaleDateString() }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 shadow-sm p-6 flex flex-col justify-between rounded-sm">
                    <div>
                        <h3 class="font-bold text-gray-900 mb-6 flex items-center gap-2">
                            <ShieldCheck class="w-5 h-5 text-green-500" /> System Status
                        </h3>
                        <div class="space-y-5">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600 font-medium">Server Status</span>
                                <span class="text-green-700 font-bold bg-green-100 px-3 py-1 rounded-sm text-xs">Operational</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600 font-medium">AI Chatbot</span>
                                <span class="text-blue-700 font-bold bg-blue-100 px-3 py-1 rounded-sm text-xs">Active</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8">
                        <p class="text-xs text-gray-400 uppercase tracking-wide font-bold mb-3">Quick Actions</p>
                        <Link :href="route('upload.create')" class="block w-full text-center bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3 hover:shadow-lg transition text-sm font-semibold rounded-sm">
                            Update Knowledge Base
                        </Link>
                    </div>
                </div>

            </div>
        </div>
    </AppSidebarLayout>
</template>
