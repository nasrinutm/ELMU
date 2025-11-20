<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Users, FileText, Download, ArrowRight } from 'lucide-vue-next';
import { route } from 'ziggy-js';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
];

// Define props received from web.php
const props = defineProps<{
    stats: {
        users: number;
        materials: number;
        my_materials: number;
    };
    recentMaterials: Array<{
        id: number;
        name: string;
        subject: string;
        created_at: string;
        user: { name: string };
    }>;
}>();

const page = usePage();
const user = page.props.auth.user;
// Check if user is admin or teacher for conditional display
const isAdmin = user.roles.includes('admin');
const isTeacher = user.roles.includes('teacher');

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">

            <!-- Welcome Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Welcome back, {{ user.name }}!</h1>
                    <p class="text-muted-foreground1">Here is an overview of the learning platform.</p>
                </div>
                <Link :href="route('materials.index')">
                    <Button>View All Materials</Button>
                </Link>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-4 md:grid-cols-3">

                <!-- Card 1: Total Materials (Everyone sees this) -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Materials</CardTitle>
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.materials }}</div>
                        <p class="text-xs text-muted-foreground">Available for download</p>
                    </CardContent>
                </Card>

                <!-- Card 2: Total Users (Only Admin sees this) -->
                <Card v-if="isAdmin">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Users</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.users }}</div>
                        <p class="text-xs text-muted-foreground">Registered in system</p>
                    </CardContent>
                </Card>

                <!-- Card 3: My Uploads (Only Teacher sees this) -->
                <Card v-if="isTeacher">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">My Uploads</CardTitle>
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.my_materials }}</div>
                        <p class="text-xs text-muted-foreground">Files you uploaded</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Recent Materials Section -->
            <div class="grid gap-4 md:grid-cols-1">
                <Card>
                    <CardHeader>
                        <CardTitle>Recently Added Materials</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="recentMaterials.length > 0" class="space-y-4">
                            <div
                                v-for="item in recentMaterials"
                                :key="item.id"
                                class="flex items-center justify-between border-b pb-4 last:border-0 last:pb-0"
                            >
                                <div>
                                    <p class="font-medium">{{ item.name }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ item.subject }} • Uploaded by {{ item.user.name }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="text-sm text-muted-foreground hidden sm:block">
                                        {{ formatDate(item.created_at) }}
                                    </div>
                                    <a :href="route('materials.download', item.id)" target="_blank">
                                    <Button

                                            size="icon"
                                            class="bg-[#003366] text-[#ffffff] border-[#000000] hover:bg-[#002244] hover:text-white"
                                        >
                                            <Download class="w-4 h-4" />
                                        </Button>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center text-muted-foreground py-6">
                            No materials uploaded yet.
                        </div>

                        <div class="mt-4 flex justify-center" v-if="recentMaterials.length > 0">
                             <Link :href="route('materials.index')" class="text-sm text-blue-900 hover:underline flex items-center">
                                View all materials <ArrowRight class="ml-1 h-3 w-3" />
                             </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
