<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();

const themeClass = computed(() => {
    const user = (page.props as any).auth?.user;
    const roles = user?.roles || [];

    if (roles.includes('admin')) return 'theme-admin';
    if (roles.includes('teacher')) return 'theme-teacher';
    if (roles.includes('student')) return 'theme-student';

    return '';
});
</script>

<template>
    <AppShell variant="sidebar" :class="themeClass">
        <AppSidebar />

        <AppContent
            class="w-full h-full bg-background overflow-x-hidden p-0 m-0 border-0"
            style="border-radius: 0px !important; margin-left: 0px !important;"
        >
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />

            <div class="p-6">
                <slot />
            </div>
        </AppContent>
    </AppShell>
</template>
