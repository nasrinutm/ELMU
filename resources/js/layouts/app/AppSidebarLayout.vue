<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import ChatbotWidget from '@/components/ChatbotWidget.vue'; // Corrected import
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

    // Safe check for roles whether they are strings or objects
    const hasRole = (role: string) => roles.some((r: any) =>
        typeof r === 'string' ? r === role : r.name === role
    );

    if (hasRole('admin')) return 'theme-admin';
    if (hasRole('teacher')) return 'theme-teacher';
    if (hasRole('student')) return 'theme-student';

    return '';
});
</script>

<template>
    <AppShell variant="sidebar" :class="themeClass">
        <AppSidebar />
        <AppContent class="w-full h-full bg-background overflow-x-hidden p-0 m-0 border-0 relative">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <div class="p-6">
                <slot />
            </div>
            <div class="fixed bottom-6 right-8 z-50 no-print">
                <ChatbotWidget />
            </div>
        </AppContent>
    </AppShell>
</template>
